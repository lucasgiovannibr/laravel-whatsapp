<?php

namespace DesterroWhatsApp\Events;

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
     * Nome do evento de sessão
     *
     * @var string
     */
    public $eventName;

    /**
     * Dados do evento
     *
     * @var array
     */
    public $eventData;

    /**
     * Create a new event instance.
     *
     * @param string $eventName
     * @param array $eventData
     * @return void
     */
    public function __construct(string $eventName, array $eventData)
    {
        $this->eventName = $eventName;
        $this->eventData = $eventData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $sessionId = $this->eventData['sessionId'] ?? 'default';
        
        return [
            new PrivateChannel('whatsapp'),
            new PrivateChannel("whatsapp.session.{$sessionId}")
        ];
    }
    
    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return $this->eventName;
    }
    
    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'event' => $this->eventName,
            'data' => $this->eventData,
            'timestamp' => now()->timestamp
        ];
    }
} 