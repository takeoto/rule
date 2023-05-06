<?php

declare(strict_types=1);

namespace Takeoto\Rule\Contract;

use Takeoto\State\Contract\StateInterface;

interface VerifierInterface
{
    /**
     * Verifies the value by the claim.
     *
     * @param mixed $value
     * @param ClaimInterface $claim
     * @return StateInterface
     * @throws \Throwable
     */
    public function verify(mixed $value, ClaimInterface $claim): StateInterface;
}