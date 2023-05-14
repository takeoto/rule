<?php

declare(strict_types=1);

namespace Takeoto\Rule\Builder;

use Takeoto\Message\Contract\ErrorMessageInterface;
use Takeoto\Message\Contract\MessageInterface;
use Takeoto\Message\ErrorMessage;
use Takeoto\Message\Utility\MessageUtility;
use Takeoto\Rule\Contract\ClaimInterface;
use Takeoto\Rule\Contract\RuleBuilderInterface;
use Takeoto\Rule\Contract\RuleInterface;
use Takeoto\Rule\Dictionary\ClaimDict;
use Takeoto\Rule\Dictionary\ErrorDict;
use Takeoto\Rule\RAWRule;
use Takeoto\Rule\Utility\Claim;
use Takeoto\Type\Type;

class RuleBuilder implements RuleBuilderInterface
{
    /**
     * @var array<\Closure(ClaimInterface $claim): RuleInterface>
     */
    protected array $additionBuilders = [];

    public function build(ClaimInterface $claim): RuleInterface
    {
        return match ($claim->getType()) {
            ClaimDict::INT => $this->makeIntRule($claim),
            ClaimDict::STRING => $this->makeStringRule($claim),
            ClaimDict::OBJECT => $this->makeObjectRule($claim),
            ClaimDict::ARRAY => $this->makeArrayRule($claim),
            ClaimDict::CALLBACK => $this->makeCallbackRule($claim),
            ClaimDict::ONE_OF => $this->makeOneOfRule($claim),
            ClaimDict::COMPARE => $this->makeCompareRule($claim),
            ClaimDict::FLOAT,
            ClaimDict::BOOL,
            ClaimDict::CALLABLE,
            ClaimDict::NULL,
            ClaimDict::NUMERIC,
            ClaimDict::SCALAR,
            ClaimDict::ITERABLE,
            ClaimDict::RESOURCE,
            ClaimDict::COUNTABLE => $this->makeTypeRule($claim),
            default => $this->buildFromAdditional($claim),
        };
    }

    /**
     * @param string $type
     * @param \Closure(ClaimInterface $claim): RuleInterface|RuleInterface $builder
     * @return $this
     */
    public function register(string $type, \Closure|RuleInterface $builder): static
    {
        $this->additionBuilders[$type] = $builder;

        return $this;
    }

    protected function buildFromAdditional(ClaimInterface $claim): RuleInterface
    {
        $builder = $this->additionBuilders[$claim->getType()] ?? throw new \InvalidArgumentException(sprintf(
            '"%s" is not allowed type of the claim.',
            $claim->getType(),
        ));

        return $builder instanceof RuleInterface ? $builder : $builder($claim);
    }

    protected function makeIntRule(ClaimInterface $claim): RuleInterface
    {
        $max = $claim->getAttr(ClaimDict::INT_MAX);
        $min = $claim->getAttr(ClaimDict::INT_MIN);
        $soft = $claim->getAttr(ClaimDict::INT_SOFT);
        $errorsMassages = $claim->getAttr(ClaimDict::CLAIM_ERROR_MESSAGE);

        return RAWRule::new(
            static function (mixed $v) use ($max, $min, $soft, $errorsMassages): bool|ErrorMessageInterface {
                $errorCode = match (false) {
                    is_int($v) || ($soft && filter_var($v, FILTER_VALIDATE_INT)) => ErrorDict::NOT_INT,
                    $min === null || $v >= $min => ErrorDict::NOT_INT_MORE_OR_EQ,
                    $max === null || $v <= $max => ErrorDict::NOT_INT_LESS_OR_EQ,
                    default => null,
                };

                return null === $errorCode ?: new ErrorMessage(
                    $errorCode,
                    $errorsMassages[$errorCode] ?? 'Value is not valid.',
                    [
                        '{{ type }}' => gettype($v),
                        '{{ min }}' => $min,
                        '{{ max }}' => $max,
                    ],
                );
            },
        );
    }

    protected function makeStringRule(ClaimInterface $claim): RuleInterface
    {
        $max = $claim->getAttr(ClaimDict::STRING_LENGTH_MAX);
        $min = $claim->getAttr(ClaimDict::STRING_LENGTH_MIN);
        $pattern = $claim->getAttr(ClaimDict::STRING_PATTERN);
        $errorsMassages = $claim->getAttr(ClaimDict::CLAIM_ERROR_MESSAGE);

        return RAWRule::new(
            static function (mixed $v) use ($max, $min, $pattern, $errorsMassages): bool|ErrorMessageInterface {
                $errorCode = match (false) {
                    is_string($v) => ErrorDict::NOT_STRING,
                    null === $pattern || preg_match($pattern, $v) => ErrorDict::NOT_STRING_REGEX,
                    $min === null || mb_strlen($v) >= $min => ErrorDict::NOT_STRING_LENGTH_MORE_OR_EQ,
                    $max === null || mb_strlen($v) <= $max => ErrorDict::NOT_STRING_LENGTH_LESS_OR_EQ,
                    default => null,
                };

                return null === $errorCode ?: new ErrorMessage(
                    $errorCode,
                    $errorsMassages[$errorCode] ?? 'The value is not valid.',
                    [
                        '{{ type }}' => gettype($v),
                        '{{ min }}' => $min,
                        '{{ max }}' => $max,
                    ],
                );
            },
        );
    }

    protected function makeCallbackRule(ClaimInterface $claim): RuleInterface
    {
        return RAWRule::new($claim->getAttr(ClaimDict::CALLBACK_CLOSURE));
    }

    protected function makeArrayRule(ClaimInterface $claim): RuleInterface
    {
        $allowMissing = $claim->getAttr(ClaimDict::ARRAY_ALLOWED_MISSING_FIELDS);
        $allowExtra = $claim->getAttr(ClaimDict::ARRAY_ALLOWED_EXTRA_FIELDS);
        $reqFields = array_flip($claim->getAttr(ClaimDict::ARRAY_REQUIRED_FIELD));
        $optFields = array_flip($claim->getAttr(ClaimDict::ARRAY_OPTIONAL_FIELD));
        $structure = $claim->getAttr(ClaimDict::ARRAY_STRUCTURE);
        $eachRule = $claim->getAttr(ClaimDict::ARRAY_EACH);
        $errorsMessages = $claim->getAttr(ClaimDict::CLAIM_ERROR_MESSAGE);

        return RAWRule::new(function (mixed $array) use (
            $allowExtra,
            $allowMissing,
            $reqFields,
            $optFields,
            $structure,
            $eachRule,
            $errorsMessages,
        ) {
            if (!is_array($array)) {
                return new ErrorMessage(
                    ErrorDict::NOT_ARRAY,
                    $errorsMessages[ErrorDict::NOT_ARRAY],
                    ['{{ type }}' => gettype($array)],
                );
            }

            $messages = [];

            if ($eachRule !== null) {
                $rule = $this->makeRule($eachRule, 'This value of the key {{ key }} should be equal to {{ value }}.');
                array_walk($array, fn(mixed $v, string|int $k) => array_push($messages,
                    ...$this->verifyArrayValue($rule, $v, $k)
                ));

                return $messages;
            }

            foreach ($structure as $key => $rule) {
                $keyExist = array_key_exists($key, $array);

                if (!$keyExist) {
                    if (isset($reqFields[$key]) || (!$allowMissing && !isset($optFields[$key]))) {
                        $messages[] = new ErrorMessage(
                            ErrorDict::ARRAY_KEY_MISSING,
                            $errorsMessages[ErrorDict::ARRAY_KEY_MISSING],
                            ['{{ key }}' => $key],
                        );
                    }
                    continue;
                }

                array_push($messages, ...$this->verifyArrayValue(
                    $this->makeRule($rule, 'This value of the key {{ key }} should be equal to {{ value }}.'),
                    $array[$key],
                    $key,
                ));
            }

            if ($allowExtra) {
                return $messages;
            }

            foreach ($array as $key => $v) {
                if (!array_key_exists($key, $structure)) {
                    $messages[] = new ErrorMessage(
                        ErrorDict::ARRAY_KEY_EXTRA,
                        $errorsMessages[ErrorDict::ARRAY_KEY_EXTRA],
                        ['{{ key }}' => $key]
                    );
                }
            }

            return $messages;
        });
    }

    protected function makeObjectRule(ClaimInterface $claim): RuleInterface
    {
        $instanceOf = $claim->getAttr(ClaimDict::OBJECT_INSTANCE);
        $errorsMassages = $claim->getAttr(ClaimDict::CLAIM_ERROR_MESSAGE);

        return RAWRule::new(
            static function (mixed $value) use ($instanceOf, $errorsMassages): bool|ErrorMessageInterface {
                $errorCode = match (false) {
                    is_object($value) => ErrorDict::NOT_OBJECT,
                    null === $instanceOf || $value instanceof $instanceOf => ErrorDict::NOT_OBJECT_INSTANCE_OF,
                    default => null,
                };

                return null === $errorCode ?: new ErrorMessage($errorCode, $errorsMassages[$errorCode], [
                    '{{ type }}' => gettype($value),
                ]);
            },
        );
    }

    protected function makeOneOfRule(ClaimInterface $claim): RuleInterface
    {
        $items = $claim->getAttr(ClaimDict::ONE_OF_ITEMS);
        $errorsMessages = $claim->getAttr(ClaimDict::CLAIM_ERROR_MESSAGE);

        return RAWRule::new(function(mixed $value) use ($items, $errorsMessages): bool|MessageInterface {
            $errors = [];

            foreach ($items as $item) {
                $state = $this->makeRule($item)->verify($value);

                if ($state->isOk()) {
                    return true;
                }

                array_push($errors, ...$state->getMessages());
            }

            return new ErrorMessage(ErrorDict::NOT_ONE_OF, $errorsMessages[ErrorDict::NOT_ONE_OF], [
                '{{ errors }}' => implode(', ', array_map('strval', $errors)),
            ]);
        });
    }

    protected function makeTypeRule(ClaimInterface $claim): RuleInterface
    {
        [$name, $errorCode, $verifier] = match ($claim->getType()) {
            ClaimDict::INT => ['int', ErrorDict::NOT_INT, \Closure::fromCallable('is_int')],
            ClaimDict::BOOL => ['bool', ErrorDict::NOT_BOOL, \Closure::fromCallable('is_bool')],
            ClaimDict::FLOAT => ['float', ErrorDict::NOT_FLOAT, \Closure::fromCallable('is_float')],
            ClaimDict::STRING => ['string', ErrorDict::NOT_STRING, \Closure::fromCallable('is_string')],
            ClaimDict::ARRAY => ['array', ErrorDict::NOT_ARRAY, \Closure::fromCallable('is_array')],
            ClaimDict::OBJECT => ['object', ErrorDict::NOT_OBJECT, \Closure::fromCallable('is_object')],
            ClaimDict::CALLABLE => ['callable', ErrorDict::NOT_CALLABLE, \Closure::fromCallable('is_callable')],
            ClaimDict::NULL => ['null', ErrorDict::NOT_NULL, \Closure::fromCallable('is_null')],
            ClaimDict::NUMERIC => ['numeric', ErrorDict::NOT_NUMERIC, \Closure::fromCallable('is_numeric')],
            ClaimDict::SCALAR => ['scalar', ErrorDict::NOT_SCALAR, \Closure::fromCallable('is_scalar')],
            ClaimDict::ITERABLE => ['iterable', ErrorDict::NOT_ITERABLE, \Closure::fromCallable('is_iterable')],
            ClaimDict::RESOURCE => ['resource', ErrorDict::NOT_RESOURCE, \Closure::fromCallable('is_resource')],
            ClaimDict::COUNTABLE => ['countable', ErrorDict::NOT_COUNTABLE, \Closure::fromCallable('is_countable')],
            default => throw new \InvalidArgumentException(sprintf(
                '"%s" is an undefined claim type.',
                $claim->getType(),
            )),
        };
        $errorsMassages = $claim->hasAttr(ClaimDict::CLAIM_ERROR_MESSAGE)
            ? $claim->getAttr(ClaimDict::CLAIM_ERROR_MESSAGE)
            : [];
        $errorsMassages += [$errorCode => sprintf('The value should be %s, {{ type }} given.', $name)];

        return RAWRule::new(
            static fn(mixed $v): bool|ErrorMessageInterface => $verifier($v) ?: new ErrorMessage(
                $errorCode,
                $errorsMassages[$errorCode],
                ['{{ type }}' => gettype($v)],
            ),
        );
    }

    private function makeCompareRule(ClaimInterface $claim): RuleInterface
    {
        $value = $claim->getAttr(ClaimDict::COMPARE_VALUE);
        $strict = $claim->getAttr(ClaimDict::COMPARE_STRICT);
        $messages = $claim->getAttr(ClaimDict::CLAIM_ERROR_MESSAGE);
        $verifier = static fn(mixed $v): bool => $strict ? $value === $v : $value == $v;

        return RAWRule::new(static fn(mixed $v) => $verifier($v) ?: new ErrorMessage(
            ErrorDict::NOT_SAME,
            $messages[ErrorDict::NOT_SAME],
            [
                '{{ value }}' => $value,
            ],
        ));
    }

    /**
     * @param array<string|int, mixed> $variables
     * @param string $placeholder
     * @param mixed $value
     * @return array<string|int, mixed>
     */
    private function addVariableToMessage(array $variables, string $placeholder, mixed $value): array
    {
        if (array_key_exists($placeholder, $variables)) {
            $value = [
                $value,
                ...is_array($variables[$placeholder])
                    ? $variables[$placeholder]
                    : [$variables[$placeholder]]
            ];
        }

        $variables[$placeholder] = $value;

        return $variables;
    }

    /**
     * @param RuleInterface $rule
     * @param mixed $value
     * @param string|int $key
     * @return MessageInterface[]
     */
    private function verifyArrayValue(RuleInterface $rule, mixed $value, string|int $key): array
    {
        $state = $rule->verify($value);
        $messages = [];

        if ($state->isOk()) {
            return $messages;
        }


        foreach ($state->getMessages() as $message) {
            $messages[] = $message instanceof ErrorMessageInterface
                ? new ErrorMessage(
                    $message->getCode(),
                    $message->getTemplate(),
                    $this->addVariableToMessage($message->getVariables(), '{{ key }}', $key),
                    static fn(mixed $v, string $k): string => $k === '{{ key }}' && is_array($v)
                        ? array_reduce($v, static fn(string $c, mixed $v): string => $c . "[$v]", '')
                        : MessageUtility::formatVar($v)
                ) : $message;
        }

        return $messages;
    }

    /**
     * @param mixed $rule
     * @param string|null $asErrorMessage
     * @return RuleInterface
     */
    private function makeRule(mixed $rule, string $asErrorMessage = null): RuleInterface
    {
        if ($rule instanceof ClaimInterface) {
            $rule = $this->build($rule);
        } elseif (!$rule instanceof RuleInterface) {
            $compareClaim = Claim::as($rule)->strict();

            if ($asErrorMessage !== null) {
                $compareClaim->setErrorMessage(ErrorDict::NOT_SAME, $asErrorMessage);
            }

            $rule = $this->build($compareClaim);
        }

        return $rule;
    }
}