<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim;

use Takeoto\Rule\Dictionary\ClaimDict;

class OneOfClaim extends AbstractClaim
{
    public function __construct(mixed ...$values)
    {
        $this
            ->setAttr(ClaimDict::CLAIM_TYPE, ClaimDict::ONE_OF)
            ->attrRule(ClaimDict::ONE_OF_ITEMS, \Closure::fromCallable('is_array'))
            ->items($values)
        ;
    }

    /**
     * @param mixed[] $items
     * @return $this
     */
    public function items(array $items): self
    {
        $this->setAttr(ClaimDict::ONE_OF_ITEMS, $items);

        return $this;
    }
}