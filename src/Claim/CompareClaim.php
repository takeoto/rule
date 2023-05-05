<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim;

use Takeoto\Rule\Dictionary\ClaimDict;

class CompareClaim extends AbstractClaim
{
    public function __construct(mixed $value)
    {
        $this
            ->setAttr(ClaimDict::CLAIM_TYPE, ClaimDict::COMPARE)
            ->value($value)
            ->strict(false)
        ;
    }

    public function strict(bool $strict = true): self
    {
        $this->setAttr(ClaimDict::COMPARE_STRICT, $strict);

        return $this;
    }

    public function value(mixed $value): self
    {
        $this->setAttr(ClaimDict::COMPARE_VALUE, $value);

        return $this;
    }
}