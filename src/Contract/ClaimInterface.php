<?php

namespace Takeoto\Rule\Contract;

use Takeoto\Attributable\Contract\ReadableAttributesInterface;

interface ClaimInterface extends ReadableAttributesInterface
{
    public function getType(): string;
}