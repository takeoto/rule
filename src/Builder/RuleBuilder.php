<?php

declare(strict_types=1);

namespace Takeoto\Rule\Builder;

use Takeoto\Message\Contract\ErrorMessageInterface;
use Takeoto\Message\ErrorMessage;
use Takeoto\Rule\Contract\ClaimInterface;
use Takeoto\Rule\Contract\RuleBuilderInterface;
use Takeoto\Rule\Contract\RuleInterface;
use Takeoto\Rule\Dictionary\ClaimDict;
use Takeoto\Rule\Dictionary\ErrorDict;
use Takeoto\Rule\RAWRule;
use Takeoto\Rule\Utility\Claim;

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
        $attrs = $claim->getAttrs();
        $max = $attrs[ClaimDict::INT_MAX] ?? null;
        $min = $attrs[ClaimDict::INT_MIN] ?? null;
        $soft = $attrs[ClaimDict::INT_SOFT] ?? false;
        $errorsMassages = $attrs[ClaimDict::CLAIM_ERROR_MESSAGE] ?? [];
        $errorsMassages += [
            ErrorDict::NOT_INT => 'The value should be an int, {{ type }} given.',
            ErrorDict::NOT_INT_MORE_OR_EQ => 'The value should be more or equal then {{ min }}.',
            ErrorDict::NOT_INT_LESS_OR_EQ => 'The value should be less or equal then {{ max }}.',
        ];

        return RAWRule::new(
            static function (mixed $v) use ($max, $min, $soft, $errorsMassages): bool|ErrorMessageInterface {
                $errorCode = match (false) {
                    is_int($v) || ($soft && filter_var($v, FILTER_VALIDATE_INT)) => ErrorDict::NOT_INT,
                    $v >= $min => ErrorDict::NOT_INT_MORE_OR_EQ,
                    $v <= $max => ErrorDict::NOT_INT_LESS_OR_EQ,
                    default => null,
                };

                return null === $errorCode ?: new ErrorMessage($errorCode, $errorsMassages[$errorCode], [
                    '{{ type }}' => gettype($v),
                    '{{ min }}' => $min,
                    '{{ max }}' => $max,
                ]);
            },
        );
    }

    protected function makeStringRule(ClaimInterface $claim): RuleInterface
    {
        $attrs = $claim->getAttrs();
        $max = $attrs[ClaimDict::STRING_LENGTH_MAX] ?? null;
        $min = $attrs[ClaimDict::STRING_LENGTH_MIN] ?? null;
        $pattern = $attrs[ClaimDict::STRING_PATTERN] ?? null;
        $errorsMassages = $attrs[ClaimDict::CLAIM_ERROR_MESSAGE] ?? [];
        $errorsMassages += [
            ErrorDict::NOT_STRING => 'The value should be a string, {{ type }} given.',
            ErrorDict::NOT_STRING_LENGTH_MORE_OR_EQ => 'The length of the string should be ' .
                'more or equal then {{ min }}.',
            ErrorDict::NOT_STRING_LENGTH_LESS_OR_EQ => 'The length of the string should be ' .
                'less or equal then {{ max }}.',
        ];

        return RAWRule::new(
            static function (mixed $v) use ($max, $min, $pattern, $errorsMassages): bool|ErrorMessageInterface {
                $errorCode = match (false) {
                    is_string($v) => ErrorDict::NOT_STRING,
                    null === $pattern || preg_match($pattern, $v) => ErrorDict::NOT_STRING_REGEX,
                    mb_strlen($v) >= $min => ErrorDict::NOT_STRING_LENGTH_MORE_OR_EQ,
                    mb_strlen($v) <= $max => ErrorDict::NOT_STRING_LENGTH_LESS_OR_EQ,
                    default => null,
                };

                return null === $errorCode ?: new ErrorMessage($errorCode, $errorsMassages[$errorCode], [
                    '{{ type }}' => gettype($v),
                    '{{ min }}' => $min,
                    '{{ max }}' => $max,
                ]);
            },
        );
    }

    protected function makeCallbackRule(ClaimInterface $claim): RuleInterface
    {
        return RAWRule::new($claim->getAttr(ClaimDict::CALLBACK_CLOSURE));
    }

    protected function makeArrayRule(ClaimInterface $claim): RuleInterface
    {
        $attrs = $claim->getAttrs();
        $allowMissing = $attrs[ClaimDict::ARRAY_ALLOWED_EXTRA_FIELDS] ?? false;
        $allowExtra = $attrs[ClaimDict::ARRAY_ALLOWED_MISSING_FIELDS] ?? false;
        $reqFields = array_combine($reqFields = $attrs[ClaimDict::ARRAY_REQUIRED_FIELD] ?? [], $reqFields);
        $optFields = array_combine($optFields = $attrs[ClaimDict::ARRAY_OPTIONAL_FIELD] ?? [], $optFields);
        $structure = $attrs[ClaimDict::ARRAY_STRUCTURE] ?? [];
        $eachRule = $attrs[ClaimDict::ARRAY_EACH] ?? null;

        return RAWRule::new(function (mixed $value) use (
            $allowExtra,
            $allowMissing,
            $reqFields,
            $optFields,
            $structure,
            $eachRule,
        ) {
            if (!is_array($value)) {
                return new ErrorMessage(
                    ErrorDict::NOT_ARRAY,
                    'The value should be an string, {{ type }} given.',
                    ['{{ type }}' => gettype($value)],
                );
            }

            $errors = [];

            foreach ($structure as $key => $rule) {
                $keyExist = array_key_exists($key, $value);

                if (!$keyExist) {
                    if (isset($reqFields[$key]) || (!$allowMissing && !isset($optFields[$key]))) {
                        $errors[] = new ErrorMessage(
                            ErrorDict::ARRAY_KEY_MISSING,
                            'The key "{{ key }}" in the array is missing.',
                            ['{{ key }}' => $key],
                        );
                    }
                    continue;
                }

                if ($rule instanceof ClaimInterface) {
                    $rule = $this->build($rule);
                }

                $rule = $rule instanceof RuleInterface ? $rule : $this->build(Claim::as($value[$key])->strict());
                $state = $rule->verify($value[$key]);

                if ($state->isOk()) {
                    continue;
                }

                foreach ($state->getMessages() as $message) {

                }
            }
        });
    }

    protected function makeObjectRule(ClaimInterface $claim): RuleInterface
    {
        $attrs = $claim->getAttrs();
        $instanceOf = $attrs[ClaimDict::OBJECT_INSTANCE] ?? null;
        $errorsMassages = $attrs[ClaimDict::CLAIM_ERROR_MESSAGE] ?? [];
        $errorsMassages += [
            ErrorDict::NOT_OBJECT => 'The value should be an string, {{ type }} given.',
            ErrorDict::NOT_OBJECT_INSTANCE_OF => 'The value should be an string, {{ type }} given.',
        ];

        return RAWRule::new(
            static function (mixed $v) use ($instanceOf, $errorsMassages): bool|ErrorMessageInterface {
                $errorCode = match (false) {
                    is_object($v) => ErrorDict::NOT_OBJECT,
                    null === $instanceOf || $v instanceof $instanceOf => ErrorDict::NOT_OBJECT_INSTANCE_OF,
                    default => null,
                };

                return null === $errorCode ?: new ErrorMessage($errorCode, $errorsMassages[$errorCode], [
                    '{{ type }}' => gettype($v),
                ]);
            },
        );
    }

    protected function makeOneOfRule(ClaimInterface $claim): RuleInterface
    {
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
        $message = $claim->getAttr(ClaimDict::CLAIM_ERROR_MESSAGE);
        $verifier = static fn(mixed $v): bool => $strict ? $value === $v : $value == $v;

        return RAWRule::new(static fn(mixed $v) => $verifier($v) ?: new ErrorMessage(
            ErrorDict::NOT_SAME,
            'This value should be equal to {{ value }}.',
            [
                '{{ value }}' => $this->formatValue($v),
            ],
        ));
    }

    protected function formatValue(mixed $value, bool $detailed = false): string
    {
        switch (true) {
            case \is_object($value):
                return $detailed
                    ? 'object@{' . ($value instanceof \Stringable ? (string)$value : serialize($value)) . '}'
                    : 'object';
            case \is_array($value):
                return $detailed && array_walk($value, fn(&$v, $k) => $v = $k . ': ' . $this->formatValue($v, true))
                    ? 'array@[' . implode(',', $value) . ']'
                    : 'array';
            case \is_string($value):
                return '"' . $value . '"';
            case \is_resource($value):
                return 'resource';
            case null === $value:
                return 'null';
            case false === $value:
                return 'false';
            case true === $value:
                return 'true';
            default:
                return (string)$value;
        }
    }
}