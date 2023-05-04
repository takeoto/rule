<?php

declare(strict_types=1);

namespace Takeoto\Rule\Contract;

use Takeoto\State\Contract\StateInterface;

interface VerifierInterface
{
    public function verify(mixed $value, ClaimInterface $claim): StateInterface;
}