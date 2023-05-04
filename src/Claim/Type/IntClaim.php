<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim\Type;

use Takeoto\Rule\Claim\AbstractClaim;
use Takeoto\Rule\Dictionary\ClaimDict;

class IntClaim extends AbstractClaim
{
    public function __construct()
    {
        $this
            ->setAttr(ClaimDict::TYPE, ClaimDict::TYPE_INT)
            ->attrReadOnly(ClaimDict::TYPE)
            ->attrRule(ClaimDict::TYPE_INT_SOFT, \Closure::fromCallable('is_bool'))
            ->attrRule(ClaimDict::TYPE_INT_MIN, \Closure::fromCallable('is_int'))
            ->attrRule(ClaimDict::TYPE_INT_MAX, \Closure::fromCallable('is_int'))
        ;
    }

    public function soft(bool $enable = true): self
    {
        $this->setAttr(ClaimDict::TYPE_INT_SOFT, $enable);

        return $this;
    }

    public function min(int $minLength): self
    {
        $this->setAttr(ClaimDict::TYPE_INT_MIN, $minLength);

        return $this;
    }

    public function max(int $maxLength): self
    {
        $this->setAttr(ClaimDict::TYPE_INT_MAX, $maxLength);

        return $this;
    }
}