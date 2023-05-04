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
    private array $attributes;

    /**
     * @var array<string,\Closure>
     */
    private array $attributesRules = [];

    /**
     * @var array<string,string>
     */
    private array $attributesReadOnly = [];

    /**
     * @throws \Throwable
     */
    public function getType(): string
    {
        return (string)$this->getAttr(ClaimDict::TYPE);
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
        $this->ensureWritable($name);
        $this->ensureValid($name, $value);
        $this->attributes[$name] = $value;

        return $this;
    }

    protected function unsetAttr(string $name): void
    {
        $this->ensureWritable($name);
        unset($this->attributes[$name]);
    }

    public function setErrorMessage(string|int $errorCode, string $message): static
    {
        $messages = [$errorCode => $message];
        $this->setAttr(
            ClaimDict::ERROR_MESSAGE,
            $this->hasAttr(ClaimDict::ERROR_MESSAGE)
                ? $messages + $this->getAttr(ClaimDict::ERROR_MESSAGE)
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

    protected function attrReadOnly(string $attrName, bool $enabled = true): static
    {
        if ($enabled) {
            $this->attributesReadOnly[$attrName] = $attrName;
        } else {
            unset($this->attributesReadOnly[$attrName]);
        }

        return $this;
    }

    protected function ensureWritable(string $name): void
    {
        if (!isset($this->attributesReadOnly[$name])) {
            throw new \RuntimeException(sprintf('The "%s" attribute is readonly.', $name));
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