<?php

namespace DesterroShop\LaravelWhatsApp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array authenticate(?string $apiKey = null)
 * @method static array refreshToken(string $refreshToken)
 * @method static \DesterroShop\LaravelWhatsApp\Contracts\WhatsAppClient withToken(string $token)
 * @method static \DesterroShop\LaravelWhatsApp\Contracts\WhatsAppClient withCorrelationId(string $correlationId)
 * @method static \DesterroShop\LaravelWhatsApp\Contracts\WhatsAppClient withTransaction(string $transactionId)
 * @method static string beginTransaction()
 * @method static bool commitTransaction(string $transactionId)
 * @method static bool rollbackTransaction(string $transactionId)
 * @method static array sendText(string $to, string $message, array $options = [], ?string $session = null)
 * @method static array sendTemplate(string $to, string $template, array $data = [], ?string $session = null)
 * @method static array sendMedia(string $to, string $url, string $type, ?string $caption = null, ?string $session = null)
 * @method static array sendImage(string $to, string $url, ?string $caption = null, ?string $session = null)
 * @method static array sendFile(string $to, string $url, ?string $filename = null, ?string $session = null)
 * @method static array sendAudio(string $to, string $url, ?string $session = null)
 * @method static array sendVideo(string $to, string $url, ?string $caption = null, ?string $session = null)
 * @method static array sendLocation(string $to, float $latitude, float $longitude, ?string $title = null, ?string $session = null)
 * @method static array sendContact(string $to, array $contact, ?string $session = null)
 * @method static array sendButtons(string $to, string $text, array $buttons, ?string $session = null)
 * @method static array sendList(string $to, string $title, string $description, string $buttonText, array $sections, ?string $session = null)
 * @method static array sendPoll(string $to, string $question, array $options, bool $multiSelect = false, ?string $session = null)
 * @method static array sendProduct(string $to, string $catalogId, string $productId, ?string $session = null)
 * @method static array sendCatalog(string $to, string $catalogId, ?array $productIds = null, ?string $session = null)
 * @method static array sendOrder(string $to, array $orderData, ?string $session = null)
 * @method static array scheduleMessage(array $messageData)
 * @method static array cancelScheduledMessage(string $messageId)
 * @method static array registerWebhook(string $url, array $events = [])
 * @method static array listWebhooks()
 * @method static array removeWebhook(string $url)
 * @method static array getLogsByCorrelationId(string $correlationId)
 * @method static array verifySanctumToken(string $sanctumToken)
 * 
 * @see \DesterroShop\LaravelWhatsApp\Services\WhatsAppService
 */
class WhatsApp extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'whatsapp';
    }
} 