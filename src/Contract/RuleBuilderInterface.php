<?php

declare(strict_types=1);

namespace Takeoto\Rule\Contract;

interface RuleBuilderInterface
{
    public function build(ClaimInterface $claim): RuleInterface;
}