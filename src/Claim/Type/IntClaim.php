<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim\Type;

use Takeoto\Rule\Claim\AbstractClaim;
use Takeoto\Rule\Dictionary\ClaimDict;

class IntClaim extends AbstractClaim
{
    public function getType(): string
    {
        return ClaimDict::INT;
    }

    public function soft(bool $enable = true): self
    {
        $this->setAttr(ClaimDict::INT_SOFT, $enable);

        return $this;
    }

    public function min(int $minLength): self
    {
        $this->setAttr(ClaimDict::INT_MIN, $minLength);

        return $this;
    }

    public function max(int $maxLength): self
    {
        $this->setAttr(ClaimDict::INT_MAX, $maxLength);

        return $this;
    }
}