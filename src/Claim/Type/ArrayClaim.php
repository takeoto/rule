<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim\Type;

use Takeoto\Rule\Contract\ClaimInterface;
use Takeoto\Rule\Claim\AbstractClaim;
use Takeoto\Rule\Dictionary\ClaimDict;
use Takeoto\Solver\ArraySolver;
use Takeoto\Solver\FnSolver;

final class ArrayClaim extends AbstractClaim
{
    public function __construct()
    {
        $this
            ->setAttr(ClaimDict::TYPE, ClaimDict::TYPE_ARRAY)
            ->attrReadOnly(ClaimDict::TYPE)
            ->attrRule(ClaimDict::TYPE_ARRAY_STRUCTURE, is_array(...))
            ->attrRule(ClaimDict::TYPE_ARRAY_ALLOWED_EXTRA_FIELDS, is_bool(...))
            ->attrRule(ClaimDict::TYPE_ARRAY_ALLOWED_MISSING_FIELDS, is_bool(...))
            ->attrRule(ClaimDict::TYPE_ARRAY_EACH, static fn(mixed $v): bool => $v instanceof ClaimInterface)
            ->attrRule(
                ClaimDict::TYPE_ARRAY_OPTIONAL_FIELD,
                static fn(mixed $v): bool => is_array($v) && ArraySolver::allAre($v, is_string(...)),
            )
            ->attrRule(
                ClaimDict::TYPE_ARRAY_REQUIRED_FIELD,
                static fn(mixed $v): bool => is_array($v) && ArraySolver::allAre($v, is_string(...))
            );
    }

    /**
     * @param array<string,ClaimInterface>|null $structure
     * @return $this
     */
    public function structure(?array $structure = null): self
    {
        null === $structure
            ? $this->unsetAttr(ClaimDict::TYPE_ARRAY_STRUCTURE)
            : $this->setAttr(ClaimDict::TYPE_ARRAY_STRUCTURE, $structure);

        return $this;
    }

    public function extraFields(bool $allowed = true): self
    {
        $this->setAttr(ClaimDict::TYPE_ARRAY_ALLOWED_EXTRA_FIELDS, $allowed);

        return $this;
    }

    public function missingFields(bool $allowed = true): self
    {
        $this->setAttr(ClaimDict::TYPE_ARRAY_ALLOWED_MISSING_FIELDS, $allowed);

        return $this;
    }

    public function each(ClaimInterface $demand): self
    {
        $this->setAttr(ClaimDict::TYPE_ARRAY_EACH, $demand);

        return $this;
    }

    /**
     * @param string|string[] $keys
     * @return $this
     * @throws \Exception
     */
    public function optional(string|array $keys): self
    {
        if (is_string($keys) && $this->hasAttr(ClaimDict::TYPE_ARRAY_OPTIONAL_FIELD)) {
            $keys = array_unique(array_merge((array)$this->getAttr(ClaimDict::TYPE_ARRAY_OPTIONAL_FIELD), (array)$keys));
        }

        $this->setAttr(ClaimDict::TYPE_ARRAY_OPTIONAL_FIELD, (array)$keys);

        return $this;
    }

    /**
     * @param string|string[] $keys
     * @return $this
     * @throws \Exception
     */
    public function required(string|array $keys): self
    {
        if (is_string($keys) && $this->hasAttr(ClaimDict::TYPE_ARRAY_REQUIRED_FIELD)) {
            $keys = array_unique(array_merge((array)$this->getAttr(ClaimDict::TYPE_ARRAY_REQUIRED_FIELD), (array)$keys));
        }

        $this->setAttr(ClaimDict::TYPE_ARRAY_REQUIRED_FIELD, (array)$keys);

        return $this;
    }

    public function undefined()
    {

    }
# need improve [attributes schema]
//    protected function attrs(): array
//    {
//        $attrs = new AttributesDescriber();
//        $attrs
//            ->define(RuleDict::TYPE)
//                ->default(RuleDict::TYPE_ARRAY)
//                ->demand(Demand::string())
//                ->required()
//                ->permission()
//            ->also()
//            ->define(RuleDict::TYPE)
//                ->demand(Demand::string())
//        ;
//
//        $attributes = $attrs->resolve($values);
//        $attributes = $attrs->createBuilder()->build($values);
//
//        $attributes->set();
//        $attributes->get();
//        $attributes->has();
//        $attributes->toArray();
//
//        $attributes->need();
//        $attributes->demands(RuleDict::TYPE);
//        $attributes->vrifier(RuleDict::TYPE)->verify($value)->isOk();
//
//
//        return [
//            RuleDict::TYPE => RuleDict::TYPE_ARRAY,
//            RuleDict::TYPE_ARRAY_OPTIONAL_FIELD => Demand::array(Demand::string()),
//            RuleDict::TYPE_ARRAY_ALLOWED_MISSING_FIELDS,
//            RuleDict::TYPE_ARRAY_ALLOWED_EXTRA_FIELDS,
//            RuleDict::TYPE_ARRAY_FIELDS,
//        ];
//    }
}