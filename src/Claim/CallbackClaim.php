<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim;

use Takeoto\Rule\Dictionary\ClaimDict;

class CallbackClaim extends AbstractClaim
{
    public function __construct(\Closure $closure)
    {
        $this
            ->setAttr(ClaimDict::CLAIM_TYPE, ClaimDict::CALLBACK)
            ->setAttr(ClaimDict::CALLBACK_CLOSURE, $closure)
        ;
    }
}