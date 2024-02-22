<?php

namespace App\Model\Exception;

use App\Model\Exception\Enum\FlashLevel;

class FlashException extends \Exception
{
    private string $level;

    public function __construct(string $message = "", int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function warning($message): FlashException
    {
        $instance = new FlashException($message);
        $instance->setLevel(FlashLevel::WARNING);
        return $instance;
    }

    public static function danger(string|array $message): FlashException
    {
        $instance = new FlashException($message);
        $instance->setLevel(FlashLevel::DANGER);
        return $instance;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(FlashLevel $level): void
    {
        $this->level = $level->value;
    }
}
