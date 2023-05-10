<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim\Type;

use Takeoto\Rule\Contract\ClaimInterface;
use Takeoto\Rule\Claim\AbstractClaim;
use Takeoto\Rule\Dictionary\ClaimDict;
use Takeoto\Rule\Dictionary\ErrorDict;

final class ArrayClaim extends AbstractClaim
{
    public function __construct()
    {
        $this
            ->setType(ClaimDict::ARRAY)
            ->structure(null)
            ->extraFields(false)
            ->missingFields(false)
            ->each(null)
            ->required([])
            ->optional([])
            ->setErrorMessage(ErrorDict::NOT_ARRAY, 'The value should be an string, {{ type }} given.')
            ->setErrorMessage(ErrorDict::ARRAY_KEY_MISSING, 'The key {{ key }} in the array is missing.')
            ->setErrorMessage(ErrorDict::ARRAY_KEY_EXTRA, 'The key {{ key }} of the array was not expected.')
            ->attrRule(ClaimDict::ARRAY_OPTIONAL_FIELD, \Closure::fromCallable([$this, 'areKeysValid']))
            ->attrRule(ClaimDict::ARRAY_REQUIRED_FIELD, \Closure::fromCallable([$this, 'areKeysValid']));
    }

    /**
     * @param array<string,ClaimInterface>|null $structure
     * @return $this
     */
    public function structure(?array $structure): self
    {
        null === $structure
            ? $this->unsetAttr(ClaimDict::ARRAY_STRUCTURE)
            : $this->setAttr(ClaimDict::ARRAY_STRUCTURE, $structure);

        return $this;
    }

    public function extraFields(bool $allowed = true): self
    {
        $this->setAttr(ClaimDict::ARRAY_ALLOWED_EXTRA_FIELDS, $allowed);

        return $this;
    }

    public function missingFields(bool $allowed = true): self
    {
        $this->setAttr(ClaimDict::ARRAY_ALLOWED_MISSING_FIELDS, $allowed);

        return $this;
    }

    public function each(?ClaimInterface $demand): self
    {
        $this->setAttr(ClaimDict::ARRAY_EACH, $demand);

        return $this;
    }

    /**
     * @param string|string[] $keys
     * @return $this
     * @throws \Throwable
     */
    public function optional(string|array $keys): self
    {
        $keys = (array)$keys;
        $keys = array_combine($keys, $keys);
        $previous = $this->hasAttr(ClaimDict::ARRAY_OPTIONAL_FIELD)
            ? (array)$this->getAttr(ClaimDict::ARRAY_OPTIONAL_FIELD)
            : [];
        $this->setAttr(ClaimDict::ARRAY_OPTIONAL_FIELD, $keys + $previous);

        return $this;
    }

    /**
     * @param string|string[] $keys
     * @return $this
     * @throws \Throwable
     */
    public function required(string|array $keys): self
    {
        $keys = (array)$keys;
        $keys = array_combine($keys, $keys);
        $previous = $this->hasAttr(ClaimDict::ARRAY_REQUIRED_FIELD)
            ? (array)$this->getAttr(ClaimDict::ARRAY_REQUIRED_FIELD)
            : [];
        $this->setAttr(ClaimDict::ARRAY_REQUIRED_FIELD, $keys + $previous);

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