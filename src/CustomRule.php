<?php

declare(strict_types=1);

namespace Takeoto\Rule;

use Takeoto\Message\Contract\ErrorMessageInterface;
use Takeoto\Message\Contract\MessageInterface;
use Takeoto\Message\ErrorMessage;
use Takeoto\Rule\Contract\RuleInterface;
use Takeoto\State\Contract\StateInterface;
use Takeoto\State\State;

class CustomRule implements RuleInterface
{
    private \Closure $verifyClosure;

    public function __construct(\Closure $verifyClosure)
    {
        $this->verifyClosure = $verifyClosure;
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
                $errors = array_map([$this, 'toErrorMessage'], $result);
                break;
            default:
                $errors = [$this->toErrorMessage($result)];
        }

        return new State($errors, $isPassed);
    }

    /**
     * @param mixed $message
     * @return ErrorMessageInterface
     */
    private function toErrorMessage($message): ErrorMessageInterface
    {
        switch (true) {
            case is_string($message):
                return new ErrorMessage(self::class . '__verification_error', $message);
            case $message instanceof ErrorMessageInterface:
                return $message;
            case $message instanceof MessageInterface:
                return new ErrorMessage(
                    self::class . '__verification_error',
                    $message->getTemplate(),
                    $message->getVariables(),
                );
            default:
                throw new \RuntimeException(
                    'The result of a verify closure cannot be transformed to the state.'
                );
        }
    }
}