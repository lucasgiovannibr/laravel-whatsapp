<?php

namespace DesterroShop\LaravelWhatsApp\Notifications;

use DesterroShop\LaravelWhatsApp\Contracts\WhatsAppClient;
use DesterroShop\LaravelWhatsApp\Exceptions\WhatsAppException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * @var WhatsAppClient
     */
    protected $client;

    /**
     * @param WhatsAppClient $client
     */
    public function __construct(WhatsAppClient $client)
    {
        $this->client = $client;
    }

    /**
     * Enviar a notificação via WhatsApp.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return array|null
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toWhatsApp')) {
            throw new WhatsAppException('O método toWhatsApp não foi definido na classe de notificação');
        }

        $to = $notifiable->routeNotificationFor('whatsapp', $notification);

        if (empty($to)) {
            Log::warning('WhatsApp: Destino não encontrado para notificação', [
                'notifiable' => get_class($notifiable),
                'notification' => get_class($notification),
            ]);
            return null;
        }

        $message = $notification->toWhatsApp($notifiable);
        $sessionId = $message['session_id'] ?? null;

        // Determinar o tipo de mensagem e enviar de acordo
        if (isset($message['template'])) {
            $templateName = $message['template'];
            $data = $message['data'] ?? [];
            return $this->client->sendTemplate($to, $templateName, $data, $sessionId);
        } elseif (isset($message['image'])) {
            $caption = $message['caption'] ?? null;
            return $this->client->sendImage($to, $message['image'], $caption, $sessionId);
        } elseif (isset($message['file'])) {
            $filename = $message['filename'] ?? null;
            return $this->client->sendFile($to, $message['file'], $filename, $sessionId);
        } elseif (isset($message['location'])) {
            $latitude = $message['location']['latitude'] ?? 0;
            $longitude = $message['location']['longitude'] ?? 0;
            $title = $message['location']['title'] ?? null;
            return $this->client->sendLocation($to, $latitude, $longitude, $title, $sessionId);
        } elseif (isset($message['buttons'])) {
            $title = $message['title'] ?? 'Escolha uma opção';
            $text = $message['text'] ?? '';
            $buttons = $message['buttons'] ?? [];
            return $this->client->sendButtons($to, $title, $text, $buttons, $sessionId);
        } elseif (isset($message['list'])) {
            $title = $message['title'] ?? 'Escolha uma opção';
            $description = $message['description'] ?? '';
            $buttonText = $message['button_text'] ?? 'Ver opções';
            $sections = $message['list']['sections'] ?? [];
            return $this->client->sendList($to, $title, $description, $buttonText, $sections, $sessionId);
        } elseif (isset($message['poll'])) {
            $question = $message['poll']['question'] ?? '';
            $options = $message['poll']['options'] ?? [];
            $isMultiSelect = $message['poll']['is_multi_select'] ?? false;
            return $this->client->sendPoll($to, $question, $options, $isMultiSelect, $sessionId);
        } elseif (isset($message['product'])) {
            $catalogId = $message['product']['catalog_id'] ?? '';
            $productId = $message['product']['product_id'] ?? '';
            return $this->client->sendProduct($to, $catalogId, $productId, $sessionId);
        } elseif (isset($message['catalog'])) {
            $catalogId = $message['catalog']['catalog_id'] ?? '';
            $productItems = $message['catalog']['product_items'] ?? [];
            return $this->client->sendCatalog($to, $catalogId, $productItems, $sessionId);
        } elseif (isset($message['order'])) {
            $orderData = $message['order'] ?? [];
            return $this->client->sendOrder($to, $orderData, $sessionId);
        } elseif (isset($message['text'])) {
            return $this->client->sendText($to, $message['text'], $sessionId);
        }

        // Fallback para texto simples
        if (is_string($message)) {
            return $this->client->sendText($to, $message, $sessionId);
        }

        Log::warning('WhatsApp: Formato de mensagem não reconhecido', [
            'notifiable' => get_class($notifiable),
            'notification' => get_class($notification),
        ]);

        return null;
    }
} 