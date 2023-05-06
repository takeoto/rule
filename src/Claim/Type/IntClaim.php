<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim\Type;

use Takeoto\Rule\Claim\AbstractClaim;
use Takeoto\Rule\Dictionary\ClaimDict;
use Takeoto\Rule\Dictionary\ErrorDict;

class IntClaim extends AbstractClaim
{
    public function __construct()
    {
        $this
            ->setType(ClaimDict::INT)
            ->setErrorMessage(ErrorDict::NOT_INT, 'The value should be an int, {{ type }} given.')
            ->setErrorMessage(ErrorDict::NOT_INT_MORE_OR_EQ, 'The value should be more or equal then {{ min }}.')
            ->setErrorMessage(ErrorDict::NOT_INT_LESS_OR_EQ, 'The value should be less or equal then {{ max }}.')
            ->soft(false)
            ->min(null)
            ->max(null)
        ;
    }

    public function soft(bool $enable = true): self
    {
        $this->setAttr(ClaimDict::INT_SOFT, $enable);

        return $this;
    }

    public function min(?int $minLength): self
    {
        $this->setAttr(ClaimDict::INT_MIN, $minLength);

        return $this;
    }

    public function max(?int $maxLength): self
    {
        $this->setAttr(ClaimDict::INT_MAX, $maxLength);

        return $this;
    }
}