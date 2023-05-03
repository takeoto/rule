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
            ->setAttr(ClaimDict::TYPE, ClaimDict::TYPE_OBJECT)
            ->attrReadOnly(ClaimDict::TYPE)
            ->attrRule(ClaimDict::TYPE_OBJECT_INSTANCE, static fn(mixed $v): bool => is_string($v) && class_exists($v))
        ;
    }

    public function instanceOf(?string $class = null): self
    {
        null === $class
            ? $this->unsetAttr(ClaimDict::TYPE_OBJECT_INSTANCE)
            : $this->setAttr(ClaimDict::TYPE_OBJECT_INSTANCE, $class);

        return $this;
    }
}