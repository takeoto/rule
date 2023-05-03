<?php

declare(strict_types=1);

namespace Takeoto\Rule\Contract;

use Takeoto\State\Contract\StateInterface;

interface RuleInterface
{
    /**
     * Verifies the value.
     *
     * @param mixed $value
     */
    public function verify($value): StateInterface;
}