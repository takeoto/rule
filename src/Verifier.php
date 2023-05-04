<?php

declare(strict_types=1);

namespace Takeoto\Rule;

use Takeoto\Rule\Contract\ClaimInterface;
use Takeoto\Rule\Contract\RuleBuilderInterface;
use Takeoto\Rule\Contract\VerifierInterface;
use Takeoto\State\Contract\StateInterface;

class Verifier implements VerifierInterface
{
    public function __construct(private RuleBuilderInterface $builder)
    {
    }

    public function verify(mixed $value, ClaimInterface $claim): StateInterface
    {
        return $this->builder->build($claim)->verify($value);
    }
}