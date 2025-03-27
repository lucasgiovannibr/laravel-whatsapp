<?php

namespace DesterroShop\LaravelWhatsApp\Notifications\Channels;

use DesterroShop\LaravelWhatsApp\Contracts\WhatsAppClient;
use DesterroShop\LaravelWhatsApp\Services\TemplateService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\View;

class WhatsAppChannel
{
    /**
     * @var WhatsAppClient
     */
    protected $whatsapp;

    /**
     * @var TemplateService
     */
    protected $templateService;

    /**
     * Construtor
     *
     * @param WhatsAppClient $whatsapp
     * @param TemplateService|null $templateService
     */
    public function __construct(WhatsAppClient $whatsapp, ?TemplateService $templateService = null)
    {
        $this->whatsapp = $whatsapp;
        $this->templateService = $templateService ?? app(TemplateService::class);
    }

    /**
     * Enviar a notificação
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return array|null
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toWhatsApp')) {
            throw new \RuntimeException('Notification does not have toWhatsApp method');
        }

        // Obter dados da notificação
        $message = $notification->toWhatsApp($notifiable);

        // Pular se não tiver destinatário
        if (empty($message['to'])) {
            // Tentar obter número do modelo notificável
            $to = $notifiable->routeNotificationFor('whatsapp', $notification);
            
            if (empty($to)) {
                return null;
            }
            
            $message['to'] = $to;
        }

        // Verificar o tipo de mensagem
        return $this->sendMessage($message);
    }

    /**
     * Enviar mensagem baseada no tipo
     *
     * @param array $message
     * @return array
     */
    protected function sendMessage(array $message): array
    {
        $to = $message['to'];
        $sessionId = $message['session_id'] ?? null;

        // Determinar o tipo de mensagem e enviar
        if (isset($message['template'])) {
            // Enviar template
            $templateName = $message['template'];
            $data = $message['data'] ?? [];
            
            return $this->whatsapp->sendTemplate($to, $templateName, $data, $sessionId);
        } elseif (isset($message['image'])) {
            // Enviar imagem
            $caption = $message['caption'] ?? null;
            
            return $this->whatsapp->sendImage($to, $message['image'], $caption, $sessionId);
        } elseif (isset($message['audio'])) {
            // Enviar áudio
            return $this->whatsapp->sendAudio($to, $message['audio'], $sessionId);
        } elseif (isset($message['file'])) {
            // Enviar arquivo
            $filename = $message['filename'] ?? null;
            
            return $this->whatsapp->sendFile($to, $message['file'], $filename, $sessionId);
        } elseif (isset($message['location'])) {
            // Enviar localização
            $location = $message['location'];
            $title = $message['title'] ?? null;
            
            return $this->whatsapp->sendLocation(
                $to, 
                $location['latitude'], 
                $location['longitude'], 
                $title, 
                $sessionId
            );
        } elseif (isset($message['contact'])) {
            // Enviar contato
            return $this->whatsapp->sendContact($to, $message['contact'], $sessionId);
        } elseif (isset($message['buttons'])) {
            // Enviar botões
            $bodyText = $message['body'] ?? '';
            
            return $this->whatsapp->sendButtons($to, $bodyText, $message['buttons'], $sessionId);
        } elseif (isset($message['view'])) {
            // Renderizar view do Laravel e enviar como texto
            $data = $message['data'] ?? [];
            $rendered = View::make($message['view'], $data)->render();
            
            return $this->whatsapp->sendText($to, $rendered, $sessionId);
        } else {
            // Enviar texto simples
            $text = $message['text'] ?? $message['message'] ?? '';
            
            return $this->whatsapp->sendText($to, $text, $sessionId);
        }
    }
} 