<?php

declare(strict_types=1);

namespace Takeoto\Rule\Utility;

use Takeoto\Rule\Claim\CallbackClaim;
use Takeoto\Rule\Claim\OneOfClaim;
use Takeoto\Rule\Claim\Type\ArrayClaim;
use Takeoto\Rule\Claim\Type\IntClaim;
use Takeoto\Rule\Claim\Type\ObjectClaim;
use Takeoto\Rule\Claim\Type\StringClaim;
use Takeoto\Rule\Contract\ClaimInterface;

final class ClaimUtility
{
    /**
     * @param array<string,ClaimInterface>|ClaimInterface|null $structureOrRule
     * @return ArrayClaim
     */
    public static function array(array|ClaimInterface $structureOrRule = null): ArrayClaim
    {
        $claim = new ArrayClaim();

        if ($structureOrRule instanceof ClaimInterface) {
            $claim->each($structureOrRule);
        } elseif (is_array($structureOrRule)) {
            $claim->structure($structureOrRule);
        }

        return $claim;
    }

    public static function object(): ObjectClaim
    {
        return new ObjectClaim();
    }

    public static function int(): IntClaim
    {
        return new IntClaim();
    }

    public static function string(): StringClaim
    {
        return new StringClaim();
    }

    public static function oneOf(mixed ...$values): OneOfClaim
    {
        return (new OneOfClaim())->items($values);
    }

    public static function callback(\Closure $closure): CallbackClaim
    {
        return new CallbackClaim($closure);
    }

    private function __construct()
    {
    }
}