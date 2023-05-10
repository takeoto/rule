<?php

namespace Takeoto\Rule\Contract;

use Takeoto\ObjectBuilder\Contract\ObjectBuilderInterface;

interface ClaimBuilderInterface extends ObjectBuilderInterface
{
    /**
     * @param mixed|null $data
     * @return T
     * @throws \Throwable
     */
    public function build(mixed $data = null): ClaimInterface;
}