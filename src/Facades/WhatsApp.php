<?php

namespace DesterroShop\LaravelWhatsApp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array authenticate()
 * @method static array refreshToken(string $refreshToken)
 * @method static self withToken(string $token)
 * @method static self withCorrelationId(?string $correlationId = null)
 * @method static string beginTransaction()
 * @method static self withTransaction(string $transactionId)
 * @method static array commitTransaction(string $transactionId)
 * @method static array rollbackTransaction(string $transactionId)
 * @method static array getCircuitBreakerStatus(?string $service = null)
 * @method static array resetCircuitBreaker(string $service)
 * @method static array sendText(string $to, string $message, array $options = [], ?string $sessionId = null)
 * @method static array sendTemplate(string $to, string $templateName, array $data = [], ?string $sessionId = null)
 * @method static array sendMedia(string $to, string $mediaUrl, string $mediaType, ?string $caption = null, ?string $sessionId = null)
 * @method static array sendList(string $to, string $title, string $buttonText, array $items, ?string $description = null, ?string $sessionId = null)
 * @method static array sendButton(string $to, string $body, array $buttons, ?string $sessionId = null)
 * @method static array scheduleMessage(array $data)
 * @method static array cancelScheduledMessage(string $messageId)
 * @method static array getLogsByCorrelationId(string $correlationId)
 * @method static array verifySanctumToken(string $sanctumToken)
 *
 * @see \DesterroShop\LaravelWhatsApp\Services\WhatsAppService
 */
class WhatsApp extends Facade
{
    /**
     * Obter o nome do componente registrado.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'whatsapp';
    }
} 