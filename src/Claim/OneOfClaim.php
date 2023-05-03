<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim;

use Takeoto\Rule\Dictionary\ClaimDict;

class OneOfClaim extends AbstractClaim
{
    public function __construct()
    {
        $this
            ->setAttr(ClaimDict::TYPE, ClaimDict::TYPE_ONE_OF)
            ->attrReadOnly(ClaimDict::TYPE);
    }

    /**
     * @param mixed[] $items
     * @return $this
     */
    public function items(array $items): self
    {
        $this->setAttr(ClaimDict::TYPE_ONE_OF_ITEMS, $items);

        return $this;
    }
}