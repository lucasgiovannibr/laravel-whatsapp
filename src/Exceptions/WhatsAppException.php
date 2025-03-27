<?php

namespace DesterroShop\LaravelWhatsApp\Exceptions;

use Exception;

class WhatsAppException extends Exception
{
    /**
     * Criar uma nova instância da exceção.
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     * @return void
     */
    public function __construct(string $message, int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Converter exceção para string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}";
    }
} 