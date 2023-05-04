<?php

declare(strict_types=1);

namespace Takeoto\Rule\Builder;

use Takeoto\Message\ErrorMessage;
use Takeoto\Rule\Contract\ClaimInterface;
use Takeoto\Rule\Contract\RuleBuilderInterface;
use Takeoto\Rule\Contract\RuleInterface;
use Takeoto\Rule\Dictionary\ClaimDict;
use Takeoto\Rule\Dictionary\ErrorDict;
use Takeoto\Rule\RAWRule;

class RuleBuilder implements RuleBuilderInterface
{
    /**
     * @var array<\Closure(ClaimInterface $claim): RuleInterface>
     */
    protected array $additionBuilders = [];

    public function build(ClaimInterface $claim): RuleInterface
    {
        return match ($claim->getType()) {
            ClaimDict::TYPE_INT => $this->makeIntRule($claim),
            ClaimDict::TYPE_STRING => $this->makeStringRule($claim),
            ClaimDict::TYPE_OBJECT => $this->makeObjectRule($claim),
            ClaimDict::TYPE_ARRAY => $this->makeArrayRule($claim),
            ClaimDict::TYPE_CALLBACK => $this->makeCallbackRule($claim),
            ClaimDict::TYPE_ONE_OF => $this->makeOneOfRule($claim),
            ClaimDict::TYPE_FLOAT,
            ClaimDict::TYPE_BOOL,
            ClaimDict::TYPE_CALLABLE,
            ClaimDict::TYPE_NULL,
            ClaimDict::TYPE_NUMERIC,
            ClaimDict::TYPE_SCALAR,
            ClaimDict::TYPE_ITERABLE,
            ClaimDict::TYPE_RESOURCE,
            ClaimDict::TYPE_COUNTABLE => $this->makeTypeRule($claim),
            default => $this->buildFromAdditional($claim),
        };
    }

    /**
     * @param string $type
     * @param \Closure(ClaimInterface $claim): RuleInterface $builder
     * @return $this
     */
    public function register(string $type, \Closure $builder): static
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

        return $builder($claim);
    }

    protected function makeIntRule(ClaimInterface $claim): RuleInterface
    {
        $attrs = $claim->getAttrs();
        $max = $attrs[ClaimDict::TYPE_INT_MAX] ?? null;
        $min = $attrs[ClaimDict::TYPE_INT_MIN] ?? null;
        $soft = $attrs[ClaimDict::TYPE_INT_SOFT] ?? false;
        $errorsMassages = $attrs[ClaimDict::ERROR_MESSAGE] ?? [];
        $errorsMassages += [
            ErrorDict::NOT_INT => 'Value should be an int, {{ type }} given.',
            ErrorDict::NOT_INT_MORE_OR_EQ => 'Value should be more or equal then {{ min }}.',
            ErrorDict::NOT_INT_LESS_OR_EQ => 'Value should be less or equal then {{ max }}.',
        ];

        return RAWRule::new(
            static function (mixed $v) use ($max, $min, $soft, $errorsMassages): bool|\Stringable {
                $errorCode = match (false) {
                    is_int($v) || ($soft && filter_var($v, FILTER_VALIDATE_INT)) => ErrorDict::NOT_INT,
                    $v >= $min => ErrorDict::NOT_INT_MORE_OR_EQ,
                    $v <= $max => ErrorDict::NOT_INT_LESS_OR_EQ,
                    default => null,
                };

                return null === $errorCode ? true : new ErrorMessage(
                    $errorCode,
                    $errorsMassages[$errorCode],
                    [
                        '{{ type }}' => gettype($v),
                        '{{ min }}' => $min,
                        '{{ max }}' => $max,
                    ]
                );
            },
        );
    }

    protected function makeStringRule(ClaimInterface $claim): RuleInterface
    {
        $attrs = $claim->getAttrs();
        $max = $attrs[ClaimDict::TYPE_STRING_LENGTH_MAX] ?? null;
        $min = $attrs[ClaimDict::TYPE_STRING_LENGTH_MIN] ?? null;
        $pattern = $attrs[ClaimDict::TYPE_STRING_PATTERN] ?? null;
        $errorsMassages = $attrs[ClaimDict::ERROR_MESSAGE] ?? [];
        $errorsMassages += [
            ErrorDict::NOT_STRING => 'Value should be a string, {{ type }} given.',
            ErrorDict::NOT_STRING_LENGTH_MORE_OR_EQ => 'The length of the string should be ' .
                'more or equal then {{ min }}.',
            ErrorDict::NOT_STRING_LENGTH_LESS_OR_EQ => 'The length of the string should be ' .
                'less or equal then {{ max }}.',
        ];

        return RAWRule::new(
            static function (mixed $v) use ($max, $min, $pattern, $errorsMassages): bool|\Stringable {
                $errorCode = match (false) {
                    is_string($v) => ErrorDict::NOT_STRING,
                    null === $pattern || preg_match($pattern, $v) => ErrorDict::NOT_STRING_REGEX,
                    mb_strlen($v) >= $min => ErrorDict::NOT_STRING_LENGTH_MORE_OR_EQ,
                    mb_strlen($v) <= $max => ErrorDict::NOT_STRING_LENGTH_LESS_OR_EQ,
                    default => null,
                };

                return null === $errorCode ? true : new ErrorMessage(
                    $errorCode,
                    $errorsMassages[$errorCode],
                    [
                        '{{ type }}' => gettype($v),
                        '{{ min }}' => $min,
                        '{{ max }}' => $max,
                    ]
                );
            },
        );
    }

    protected function makeCallbackRule(ClaimInterface $claim): RuleInterface
    {
    }

    protected function makeArrayRule(ClaimInterface $claim): RuleInterface
    {

    }

    protected function makeObjectRule(ClaimInterface $claim): RuleInterface
    {
        $attrs = $claim->getAttrs();
        $instanceOf = $attrs[ClaimDict::TYPE_OBJECT_INSTANCE] ?? null;
        $errorsMassages = $attrs[ClaimDict::ERROR_MESSAGE] ?? [];
        $errorsMassages += [
            ErrorDict::NOT_OBJECT => 'Value should be an string, {{ type }} given.',
            ErrorDict::NOT_OBJECT_INSTANCE_OF => 'Value should be an string, {{ type }} given.',
        ];

        return RAWRule::new(
            static function (mixed $v) use ($instanceOf, $errorsMassages): bool|\Stringable {
                $errorCode = match (false) {
                    is_object($v) => ErrorDict::NOT_OBJECT,
                    null === $instanceOf || $v instanceof $instanceOf => ErrorDict::NOT_OBJECT_INSTANCE_OF,
                    default => null,
                };

                return null === $errorCode ? true : new ErrorMessage(
                    $errorCode,
                    $errorsMassages[$errorCode],
                    ['{{ type }}' => gettype($v)]
                );
            },
        );
    }

    protected function makeOneOfRule(ClaimInterface $claim): RuleInterface
    {
    }

    protected function makeTypeRule(ClaimInterface $claim): RuleInterface
    {
        [$name, $errorCode, $verifier] = match ($claim->getType()) {
            ClaimDict::TYPE_INT => ['int', ErrorDict::NOT_INT, \Closure::fromCallable('is_int')],
            ClaimDict::TYPE_BOOL => ['bool', ErrorDict::NOT_BOOL, \Closure::fromCallable('is_bool')],
            ClaimDict::TYPE_FLOAT => ['float', ErrorDict::NOT_FLOAT, \Closure::fromCallable('is_float')],
            ClaimDict::TYPE_STRING => ['string', ErrorDict::NOT_STRING, \Closure::fromCallable('is_string')],
            ClaimDict::TYPE_ARRAY => ['array', ErrorDict::NOT_ARRAY, \Closure::fromCallable('is_array')],
            ClaimDict::TYPE_OBJECT => ['object', ErrorDict::NOT_OBJECT, \Closure::fromCallable('is_object')],
            ClaimDict::TYPE_CALLABLE => ['callable', ErrorDict::NOT_CALLABLE, \Closure::fromCallable('is_callable')],
            ClaimDict::TYPE_NULL => ['null', ErrorDict::NOT_NULL, \Closure::fromCallable('is_null')],
            ClaimDict::TYPE_NUMERIC => ['numeric', ErrorDict::NOT_NUMERIC, \Closure::fromCallable('is_numeric')],
            ClaimDict::TYPE_SCALAR => ['scalar', ErrorDict::NOT_SCALAR, \Closure::fromCallable('is_scalar')],
            ClaimDict::TYPE_ITERABLE => ['iterable', ErrorDict::NOT_ITERABLE, \Closure::fromCallable('is_iterable')],
            ClaimDict::TYPE_RESOURCE => ['resource', ErrorDict::NOT_RESOURCE, \Closure::fromCallable('is_resource')],
            ClaimDict::TYPE_COUNTABLE => [
                'countable',
                ErrorDict::NOT_COUNTABLE,
                \Closure::fromCallable('is_countable'),
            ],
            default => throw new \InvalidArgumentException(sprintf(
                '"%s" is an undefined claim type.',
                $claim->getType(),
            )),
        };
        $errorsMassages = $claim->hasAttr(ClaimDict::ERROR_MESSAGE) ? $claim->getAttr(ClaimDict::ERROR_MESSAGE) : [];
        $errorsMassages += [$errorCode => sprintf('Value should be %s, {{ type }} given.', $name)];

        return RAWRule::new(
            static fn(mixed $v): bool|\Stringable => $verifier($v) ?: new ErrorMessage(
                $errorCode,
                $errorsMassages[$errorCode],
                ['{{ type }}' => gettype($v)],
            ),
        );
    }
}