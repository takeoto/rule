<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim;

use Takeoto\Rule\Dictionary\ClaimDict;
use Takeoto\Rule\Dictionary\ErrorDict;

class OneOfClaim extends AbstractClaim
{
    public function __construct(mixed ...$values)
    {
        $this
            ->setType(ClaimDict::ONE_OF)
            ->setErrorMessage(
                ErrorDict::NOT_ONE_OF,
                'This value should satisfy at least one of the following constraints: {{ errors }}'
            )
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