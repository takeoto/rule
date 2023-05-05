<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim;

use Takeoto\Rule\Dictionary\ClaimDict;

class RAWClaim extends AbstractClaim
{
    /**
     * @param string $type
     * @param mixed[] $attributes
     */
    public function __construct(string $type, array $attributes)
    {
        array_walk($attributes, fn(mixed $v, $k) => $this->setAttr($k, $v));
        $this->setAttr(ClaimDict::TYPE, $type);
    }
}