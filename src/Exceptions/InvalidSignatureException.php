<?php

namespace DesterroWhatsApp\Exceptions;

use Exception;

class InvalidSignatureException extends Exception
{
    /**
     * Construtor
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'Assinatura HMAC inválida',
        int $code = 403,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Renderizar a exceção em uma resposta HTTP
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error' => 'invalid_signature'
        ], $this->getCode());
    }
} 