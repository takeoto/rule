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
            ->attrRule(ClaimDict::TYPE, \Closure::fromCallable('is_string'))
            ->setAttr(ClaimDict::TYPE, $type)
            ->attrReadOnly(ClaimDict::TYPE);
    }
}