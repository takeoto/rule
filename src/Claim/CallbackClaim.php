<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim;

use Takeoto\Rule\Dictionary\ClaimDict;

class CallbackClaim extends AbstractClaim
{
    public function __construct(\Closure $closure)
    {
        $this
            ->setAttr(ClaimDict::TYPE, ClaimDict::TYPE_CALLBACK)
            ->setAttr(ClaimDict::TYPE_CALLBACK_CLOSURE, $closure)
            ->attrReadOnly(ClaimDict::TYPE)
            ->attrReadOnly(ClaimDict::TYPE_CALLBACK_CLOSURE)
        ;
    }
}