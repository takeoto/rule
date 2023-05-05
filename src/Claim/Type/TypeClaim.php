<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim\Type;

use Takeoto\Rule\Claim\AbstractClaim;
use Takeoto\Rule\Dictionary\ClaimDict;

class TypeClaim extends AbstractClaim
{
    public function __construct(string $type)
    {
        $this
            ->setAttr(ClaimDict::TYPE, $type)
            ->attrRule(ClaimDict::TYPE, \Closure::fromCallable('is_string'));
    }

    public function setAttr(string $name, mixed $value): static
    {
        $this->ensureValid($name, $value);
        $this->attributes[$name] = $value;

        return $this;
    }

    public function unsetAttr(string $name): void
    {
        unset($this->attributes[$name]);
    }
}