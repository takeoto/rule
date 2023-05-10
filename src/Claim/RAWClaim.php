<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim;

use Takeoto\Rule\Dictionary\ClaimDict;

final class RAWClaim extends AbstractClaim
{
    /**
     * @param string|null $type
     * @param mixed[] $attributes
     */
    public function __construct(string $type, array $attributes = [])
    {
        array_walk($attributes, fn(mixed $v, string|int $k) => $this->setAttr((string)$k, $v));
        $this->setAttr(ClaimDict::CLAIM_TYPE, $type);
    }
}