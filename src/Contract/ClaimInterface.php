<?php

namespace Takeoto\Rule\Contract;

interface ClaimInterface extends ReadableAttributesInterface
{
    public function getType(): string;
}