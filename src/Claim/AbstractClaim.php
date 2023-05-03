<?php

declare(strict_types=1);

namespace Takeoto\Rule\Claim;

use Takeoto\Attributable\Contract\WritableAttributesInterface;
use Takeoto\Rule\Contract\ClaimInterface;

abstract class AbstractClaim implements ClaimInterface, WritableAttributesInterface
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
        return Strict::string($this->getAttr(ClaimDict::TYPE));
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

    public function setAttr(string $name, mixed $value): static
    {
        $this->ensureWritable($name);
        $this->ensureValid($name, $value);
        $this->attributes[$name] = $value;

        return $this;
    }

    public function unsetAttr(string $name): void
    {
        $this->ensureWritable($name);
        unset($this->attributes[$name]);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function ensureValid(string $name, mixed $value): void
    {
        Ensure::true($this->getAttrRule($name)($value), 'The "%s" attribute value isn\'t valid.');
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

    /**
     * @param string $name
     * @return void
     */
    protected function ensureWritable(string $name): void
    {
        Ensure::keyNotExists($this->attributesReadOnly, $name, sprintf('The "%s" attribute is readonly.', $name));
    }

    protected function attrRule(string $attrName, \Closure $rule): static
    {
        $this->attributesRules[$attrName] = $rule;

        return $this;
    }

    protected function getAttrRule(string $name): \Closure
    {
        return $this->attributesRules[$name] ?? fn(mixed $value): bool => true;
    }
}