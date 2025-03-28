<?php

namespace LucasGiovanni\LaravelWhatsApp\Exceptions;

use Exception;

class WhatsAppException extends Exception
{
    /**
     * Erros de validação
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Criar uma nova instância de exceção.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @return void
     */
    public function __construct(string $message, int $code = 0, \Throwable $previous = null)
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

    /**
     * Obter erros de validação.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Criar uma exceção de erro de conexão.
     *
     * @param string $message
     * @param \Throwable|null $previous
     * @return static
     */
    public static function connectionError(string $message, \Throwable $previous = null): self
    {
        return new static("Erro de conexão com o serviço WhatsApp: {$message}", 503, $previous);
    }

    /**
     * Criar uma exceção de erro de autenticação.
     *
     * @param string $message
     * @param \Throwable|null $previous
     * @return static
     */
    public static function authenticationError(string $message, \Throwable $previous = null): self
    {
        return new static("Erro de autenticação: {$message}", 401, $previous);
    }

    /**
     * Criar uma exceção de erro de validação.
     *
     * @param string $message
     * @param array $errors
     * @param \Throwable|null $previous
     * @return static
     */
    public static function validationError(string $message, array $errors = [], \Throwable $previous = null): self
    {
        $instance = new static("Erro de validação: {$message}", 422, $previous);
        $instance->errors = $errors;
        return $instance;
    }

    /**
     * Criar uma exceção de recurso não encontrado.
     *
     * @param string $resource
     * @param string $id
     * @param \Throwable|null $previous
     * @return static
     */
    public static function notFound(string $resource, string $id, \Throwable $previous = null): self
    {
        return new static("{$resource} não encontrado com ID: {$id}", 404, $previous);
    }

    /**
     * Criar uma exceção de erro do circuit breaker.
     *
     * @param string $service
     * @param \Throwable|null $previous
     * @return static
     */
    public static function circuitBreakerOpen(string $service, \Throwable $previous = null): self
    {
        return new static("Serviço {$service} indisponível (circuit breaker aberto)", 503, $previous);
    }

    /**
     * Criar uma exceção de erro de transação.
     *
     * @param string $transactionId
     * @param string $message
     * @param \Throwable|null $previous
     * @return static
     */
    public static function transactionError(string $transactionId, string $message, \Throwable $previous = null): self
    {
        return new static("Erro de transação {$transactionId}: {$message}", 500, $previous);
    }
} 