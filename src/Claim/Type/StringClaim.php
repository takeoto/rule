<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim\Type;

use Takeoto\Rule\Claim\AbstractClaim;
use Takeoto\Rule\Dictionary\ClaimDict;
use Takeoto\Rule\Dictionary\ErrorDict;

class StringClaim extends AbstractClaim
{
    public function __construct()
    {
        $this
            ->setType(ClaimDict::STRING)
            ->setErrorMessage(ErrorDict::NOT_STRING, 'The value should be a string, {{ type }} given.')
            ->setErrorMessage(
                ErrorDict::NOT_STRING_LENGTH_MORE_OR_EQ,
                'The length of the string should be more or equal then {{ min }}.',
            )
            ->setErrorMessage(
                ErrorDict::NOT_STRING_LENGTH_LESS_OR_EQ,
                'The length of the string should be less or equal then {{ max }}.',
            )
            ->pattern(null)
            ->min(null)
            ->max(null)
        ;
    }

    public function pattern(?string $pattern): self
    {
        $this->setAttr(ClaimDict::STRING_PATTERN, $pattern);

        return $this;
    }

    public function min(?int $minLength): self
    {
        $this->setAttr(ClaimDict::STRING_LENGTH_MIN, $minLength);

        return $this;
    }

    public function max(?int $maxLength): self
    {
        $this->setAttr(ClaimDict::STRING_LENGTH_MAX, $maxLength);

        return $this;
    }

    public function notEmpty(): self
    {
        return $this->min(1);
    }
}