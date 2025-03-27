<?php

namespace DesterroShop\LaravelWhatsApp\Facades;

use DesterroShop\LaravelWhatsApp\Contracts\WhatsAppClient;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendText(string $to, string $message, ?string $sessionId = null)
 * @method static array sendTemplate(string $to, string $templateName, array $data = [], ?string $sessionId = null)
 * @method static array sendImage(string $to, string $imageUrl, ?string $caption = null, ?string $sessionId = null)
 * @method static array sendFile(string $to, string $fileUrl, ?string $filename = null, ?string $sessionId = null)
 * @method static array sendAudio(string $to, string $audioUrl, ?string $sessionId = null)
 * @method static array sendLocation(string $to, float $latitude, float $longitude, ?string $title = null, ?string $sessionId = null)
 * @method static array sendContact(string $to, array $contact, ?string $sessionId = null)
 * @method static array sendButtons(string $to, string $title, string $message, array $buttons, ?string $sessionId = null)
 * @method static array sendList(string $to, string $title, string $description, string $buttonText, array $sections, ?string $sessionId = null)
 * @method static array sendPoll(string $to, string $question, array $options, bool $isMultiSelect = false, ?string $sessionId = null)
 * @method static array sendProduct(string $to, string $catalogId, string $productId, ?string $sessionId = null)
 * @method static array sendCatalog(string $to, string $catalogId, array $productItems = [], ?string $sessionId = null)
 * @method static array sendOrder(string $to, array $orderData, ?string $sessionId = null)
 * @method static array getSessions()
 * @method static array createSession(string $sessionName, string $webhookUrl = null)
 * @method static array getSessionInfo(string $sessionId)
 * @method static array deleteSession(string $sessionId)
 * @method static array getTemplates()
 * @method static array createTemplate(string $name, string $content)
 * @method static array deleteTemplate(string $name)
 * @method static array getMessages(string $phone, int $limit = 50, ?string $sessionId = null)
 * @method static array setWebhook(string $url, array $events = [])
 *
 * @see \DesterroShop\LaravelWhatsApp\Contracts\WhatsAppClient
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