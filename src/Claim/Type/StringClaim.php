<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim\Type;

use Takeoto\Rule\Claim\AbstractClaim;
use Takeoto\Rule\Dictionary\ClaimDict;

class StringClaim extends AbstractClaim
{
    public function __construct()
    {
        $this->setAttr(ClaimDict::CLAIM_TYPE, ClaimDict::STRING);
    }

    public function pattern(string $pattern): self
    {
        $this->setAttr(ClaimDict::STRING_PATTERN, $pattern);

        return $this;
    }

    public function min(int $minLength): self
    {
        $this->setAttr(ClaimDict::STRING_LENGTH_MIN, $minLength);

        return $this;
    }

    public function max(int $maxLength): self
    {
        $this->setAttr(ClaimDict::STRING_LENGTH_MAX, $maxLength);

        return $this;
    }

    public function notEmpty(): self
    {
        return $this->min(1);
    }
}