<?php

namespace DesterroShop\LaravelWhatsApp\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppSessionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Tipo de evento
     *
     * @var string
     */
    public $event;

    /**
     * Dados do evento
     *
     * @var array
     */
    public $data;

    /**
     * Criar uma nova instância de evento
     *
     * @param string $event
     * @param array $data
     * @return void
     */
    public function __construct(string $event, array $data)
    {
        $this->event = $event;
        $this->data = $data;
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
        return 'session.' . $this->event;
    }
} 