<?php

namespace Takeoto\Rule\Contract;

interface RuleBuilderInterface
{
    public function build(ClaimInterface|array $raw): RuleInterface;
}