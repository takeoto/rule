<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim;

use Takeoto\Rule\Contract\ClaimInterface;
use Takeoto\Rule\Dictionary\ClaimDict;

abstract class AbstractClaim implements ClaimInterface
{
    /**
     * @var array<string,mixed>
     */
    protected array $attributes;

    /**
     * @var array<string,\Closure>
     */
    private array $attributesRules = [];

    /**
     * @throws \Throwable
     */
    public function getType(): string
    {
        return (string)$this->getAttr(ClaimDict::CLAIM_TYPE);
    }

    public function getAttr(string $name): mixed
    {
        if (!isset($this->attributes[$name])) {
            throw new \Exception(sprintf('Attribute "%" does not exists!', $name));
        }

        return $this->attributes[$name];
    }

    public function hasAttr(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function getAttrs(): array
    {
        return $this->attributes;
    }

    protected function setAttr(string $name, mixed $value): static
    {
        $this->ensureValid($name, $value);
        $this->attributes[$name] = $value;

        return $this;
    }

    protected function unsetAttr(string $name): void
    {
        unset($this->attributes[$name]);
    }

    public function setErrorMessage(string|int $errorCode, string $message): static
    {
        $messages = [$errorCode => $message];
        $this->setAttr(
            ClaimDict::CLAIM_ERROR_MESSAGE,
            $this->hasAttr(ClaimDict::CLAIM_ERROR_MESSAGE)
                ? $messages + $this->getAttr(ClaimDict::CLAIM_ERROR_MESSAGE)
                : $messages
        );

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function ensureValid(string $name, mixed $value): void
    {
        if (!$this->getAttrRule($name)($value)) {
            throw new \InvalidArgumentException(sprintf('The "%s" attribute value isn\'t valid.', $name));
        }
    }

    protected function attrRule(string $attrName, \Closure $rule): static
    {
        $this->attributesRules[$attrName] = $rule;

        return $this;
    }

    protected function getAttrRule(string $name): \Closure
    {
        return $this->attributesRules[$name] ?? static fn(mixed $value): bool => true;
    }
}