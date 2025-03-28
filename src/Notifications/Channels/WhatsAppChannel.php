<?php

namespace LucasGiovanni\LaravelWhatsApp\Notifications\Channels;

use LucasGiovanni\LaravelWhatsApp\Contracts\WhatsAppClient;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Cliente WhatsApp
     *
     * @var WhatsAppClient
     */
    protected $whatsapp;

    /**
     * Criar uma nova instância do canal de notificação
     *
     * @param WhatsAppClient $whatsapp
     * @return void
     */
    public function __construct(WhatsAppClient $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Enviar a notificação
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toWhatsApp')) {
            throw new \Exception('Método toWhatsApp não implementado na notificação');
        }

        $to = $this->getRecipientNumber($notifiable);

        if (empty($to)) {
            Log::error('WhatsApp: Número do destinatário não encontrado', [
                'notifiable' => get_class($notifiable)
            ]);
            return false;
        }

        $data = $notification->toWhatsApp($notifiable);
        $session = $data['session'] ?? config('whatsapp.default_session');

        // Enviar mensagem baseada no tipo
        if (isset($data['template'])) {
            // Enviar como template
            return $this->sendTemplate($to, $data['template'], $data['data'] ?? [], $session);
        } elseif (isset($data['message'])) {
            // Enviar como texto simples
            return $this->whatsapp->sendText($to, $data['message'], $session);
        } elseif (isset($data['media'])) {
            // Enviar como mídia
            $caption = $data['caption'] ?? null;
            return $this->whatsapp->sendMedia($to, $data['media'], $data['type'] ?? 'image', $caption, $session);
        }

        Log::error('WhatsApp: Formato de notificação inválido', [
            'data' => $data
        ]);

        return false;
    }

    /**
     * Obter o número do destinatário
     *
     * @param mixed $notifiable
     * @return string|null
     */
    protected function getRecipientNumber($notifiable)
    {
        if (method_exists($notifiable, 'routeNotificationForWhatsApp')) {
            return $notifiable->routeNotificationForWhatsApp();
        }

        if (isset($notifiable->phone) && !empty($notifiable->phone)) {
            return $notifiable->phone;
        }

        if (isset($notifiable->whatsapp) && !empty($notifiable->whatsapp)) {
            return $notifiable->whatsapp;
        }

        if (isset($notifiable->mobile) && !empty($notifiable->mobile)) {
            return $notifiable->mobile;
        }

        return null;
    }

    /**
     * Enviar mensagem com template
     *
     * @param string $to
     * @param string $template
     * @param array $data
     * @param string|null $session
     * @return mixed
     */
    protected function sendTemplate(string $to, string $template, array $data = [], ?string $session = null)
    {
        $templateService = app('LucasGiovanni\LaravelWhatsApp\Services\TemplateService');
        $message = $templateService->renderTemplate($template, $data);

        return $this->whatsapp->sendText($to, $message, $session);
    }
} 