<?php

declare(strict_types=1);

namespace Takeoto\Rule;

use Takeoto\Message\Contract\MessageInterface;
use Takeoto\Message\ErrorMessage;
use Takeoto\Rule\Contract\RuleInterface;
use Takeoto\State\Contract\StateInterface;
use Takeoto\State\State;

class RAWRule implements RuleInterface
{
    private \Closure $verifier;

    /**
     * @param \Closure(mixed $v):StateInterface|MessageInterface|array<string|MessageInterface>|string|bool $verifier
     */
    public function __construct(\Closure $verifier)
    {
        $this->verifier = $verifier;
    }

    /**
     * @param \Closure(mixed $v):StateInterface|MessageInterface|array<string|MessageInterface>|string|bool $verifier
     */
    public static function new(\Closure $verifier)
    {
        return new self($verifier);
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

        return new State(is_array($state) ? array_map([$this, 'toMessage'], $state) : (array)$this->toMessage($state));
    }

    /**
     * @param mixed $message
     * @return MessageInterface|null
     */
    private function toMessage(mixed $message): ?MessageInterface
    {
        switch (true) {
            case is_bool($message):
                return $message ? new ErrorMessage(
                    $this->verifier::class . '__verification_error',
                    'Value is not verified.'
                ) : null;
            case is_string($message):
                return new ErrorMessage($this->verifier::class . '__verification_error', $message);
            case $message instanceof MessageInterface:
                return $message;
            default:
                throw new \RuntimeException('The result of a verify closure cannot be transformed to the state.');
        }
    }
}