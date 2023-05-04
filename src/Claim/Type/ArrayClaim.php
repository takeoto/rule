<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim\Type;

use Takeoto\Rule\Contract\ClaimInterface;
use Takeoto\Rule\Claim\AbstractClaim;
use Takeoto\Rule\Dictionary\ClaimDict;

final class ArrayClaim extends AbstractClaim
{
    public function __construct()
    {
        $this
            ->setAttr(ClaimDict::TYPE, ClaimDict::TYPE_ARRAY)
            ->attrReadOnly(ClaimDict::TYPE)
            ->attrRule(ClaimDict::TYPE_ARRAY_STRUCTURE, \Closure::fromCallable('is_array'))
            ->attrRule(ClaimDict::TYPE_ARRAY_ALLOWED_EXTRA_FIELDS, \Closure::fromCallable('is_bool'))
            ->attrRule(ClaimDict::TYPE_ARRAY_ALLOWED_MISSING_FIELDS, \Closure::fromCallable('is_bool'))
            ->attrRule(ClaimDict::TYPE_ARRAY_EACH, static fn(mixed $v): bool => $v instanceof ClaimInterface)
            ->attrRule(ClaimDict::TYPE_ARRAY_OPTIONAL_FIELD, \Closure::fromCallable([$this, 'areKeysValid']))
            ->attrRule(ClaimDict::TYPE_ARRAY_REQUIRED_FIELD, \Closure::fromCallable([$this, 'areKeysValid']));
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
     * @throws \Throwable
     */
    public function optional(string|array $keys): self
    {
        if (is_string($keys) && $this->hasAttr(ClaimDict::TYPE_ARRAY_OPTIONAL_FIELD)) {
            $keys = array_unique(array_merge(
                (array)$this->getAttr(ClaimDict::TYPE_ARRAY_OPTIONAL_FIELD),
                (array)$keys),
            );
        }

        $this->setAttr(ClaimDict::TYPE_ARRAY_OPTIONAL_FIELD, (array)$keys);

        return $this;
    }

    /**
     * @param string|string[] $keys
     * @return $this
     * @throws \Throwable
     */
    public function required(string|array $keys): self
    {
        if (is_string($keys) && $this->hasAttr(ClaimDict::TYPE_ARRAY_REQUIRED_FIELD)) {
            $keys = array_unique(array_merge(
                (array)$this->getAttr(ClaimDict::TYPE_ARRAY_REQUIRED_FIELD),
                (array)$keys,
            ));
        }

        $this->setAttr(ClaimDict::TYPE_ARRAY_REQUIRED_FIELD, (array)$keys);

        return $this;
    }

    private function areKeysValid(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!is_string($item) || empty($item)) {
                return false;
            }
        }

        return true;
    }
}