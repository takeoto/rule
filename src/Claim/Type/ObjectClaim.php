<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim\Type;

use Takeoto\Rule\Dictionary\ClaimDict;
use Takeoto\Rule\Claim\AbstractClaim;

class ObjectClaim extends AbstractClaim
{
    public function __construct()
    {
        $this
            ->setType(ClaimDict::OBJECT)
            ->instanceOf(null)
        ;
    }

    public function instanceOf(?string $class): self
    {
        null === $class
            ? $this->unsetAttr(ClaimDict::OBJECT_INSTANCE)
            : $this->setAttr(ClaimDict::OBJECT_INSTANCE, $class);

        return $this;
    }
}