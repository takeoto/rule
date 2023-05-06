<?php

declare(strict_types=1);

namespace Takeoto\Rule;

use Takeoto\Message\Contract\MessageInterface;
use Takeoto\Message\ErrorMessage;
use Takeoto\Rule\Contract\RuleInterface;
use Takeoto\State\Contract\StateInterface;
use Takeoto\State\State;

final class RAWRule implements RuleInterface
{
    /**
     * @param \Closure(mixed $v):mixed $verifier
     * @param string|int|null $defaultErrorCode
     */
    public function __construct(private \Closure $verifier, private string|int|null $defaultErrorCode = null)
    {
    }

    /**
     * @param \Closure(mixed $v):mixed $verifier
     * @param string|int|null $defaultErrorCode
     * @return self
     */
    public static function new(\Closure $verifier, string|int|null $defaultErrorCode = null): self
    {
        return new self($verifier, $defaultErrorCode);
    }

    /**
     * @inheritDoc
     */
    public function verify(mixed $value): StateInterface
    {
        $state = ($this->verifier)($value);

        if ($state instanceof StateInterface) {
            return $state;
        }
        return new State(array_reduce(
            is_array($state) ? $state : [$state],
            fn(array $ers, mixed $msg) => (null === $msg = $this->toMessage($msg)) ? $ers : [...$ers, $msg],
            [],
        ));
    }

    /**
     * @param mixed $message
     * @return MessageInterface|null
     * @throws \Throwable
     */
    private function toMessage(mixed $message): ?MessageInterface
    {
        switch (true) {
            case is_bool($message):
                return $message ? null : new ErrorMessage($this->getDefaultErrorCode(), 'Value is not verified.');
            case is_string($message):
                return new ErrorMessage($this->getDefaultErrorCode(), $message);
            case $message instanceof MessageInterface:
                return $message;
            default:
                throw new \RuntimeException(sprintf(
                    'The result of a "%s" verifier cannot be transformed to the state. Allowed types: %s',
                    $this->getVerifierName(),
                    implode(', ', [
                        StateInterface::class,
                        MessageInterface::class,
                        'array<string|' . MessageInterface::class . '>',
                        'bool',
                        'string',
                    ]),
                ));
        }
    }

    public function getVerifierName(): string
    {
        return $this->verifier::class;
    }

    public function getDefaultErrorCode(): string
    {
        return $this->defaultErrorCode ??= $this->getVerifierName() . '__verification_error';
    }
}