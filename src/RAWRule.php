<?php

declare(strict_types=1);

namespace Takeoto\Rule;

use Takeoto\Message\Contract\ErrorMessageInterface;
use Takeoto\Message\Contract\MessageInterface;
use Takeoto\Message\ErrorMessage;
use Takeoto\Rule\Contract\RuleInterface;
use Takeoto\State\Contract\StateInterface;
use Takeoto\State\State;

class RAWRule implements RuleInterface
{
    private \Closure $verifyClosure;

    public function __construct(\Closure $verifyClosure)
    {
        $this->verifyClosure = $verifyClosure;
    }

    public static function new(\Closure $verifyClosure)
    {
        return new self($verifyClosure);
    }

    /**
     * @inheritDoc
     */
    public function verify($value): StateInterface
    {
        $result = $this->verifyClosure->__invoke($value);

        if ($result instanceof StateInterface) {
            return $result;
        }

        $errors = [];
        $isPassed = null;

        switch (true) {
            case is_bool($result):
                $isPassed = $result;
                break;
            case is_array($result):
                $errors = array_map([$this, 'toMessage'], $result);
                break;
            default:
                $errors = [$this->toMessage($result)];
        }

        return new State($errors, $isPassed);
    }

    /**
     * @param mixed $message
     * @return MessageInterface
     */
    private function toMessage($message): MessageInterface
    {
        switch (true) {
            case is_string($message):
                return new ErrorMessage($this->verifyClosure::class . '__verification_error', $message);
            case $message instanceof MessageInterface:
                return $message;
            default:
                throw new \RuntimeException(
                    'The result of a verify closure cannot be transformed to the state.'
                );
        }
    }
}