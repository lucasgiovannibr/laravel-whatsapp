<?php

namespace DesterroShop\LaravelWhatsApp\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppMessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Dados da mensagem recebida
     *
     * @var array
     */
    public $message;

    /**
     * Criar uma nova instância de evento
     *
     * @param array $message
     * @return void
     */
    public function __construct(array $message)
    {
        $this->message = $message;
    }

    /**
     * Obter os canais em que o evento deve ser transmitido.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel(config('whatsapp.broadcast.channel', 'whatsapp'));
    }

    /**
     * O nome do evento a ser transmitido
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'message.received';
    }
} 