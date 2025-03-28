<?php

namespace LucasGiovanni\LaravelWhatsApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use LucasGiovanni\LaravelWhatsApp\Contracts\WhatsAppClient;
use LucasGiovanni\LaravelWhatsApp\Exceptions\InvalidSignatureException;
use LucasGiovanni\LaravelWhatsApp\Events\WhatsAppMessageReceived;
use LucasGiovanni\LaravelWhatsApp\Events\WhatsAppSessionEvent;

class WebhookController extends Controller
{
    /**
     * Cliente WhatsApp
     *
     * @var WhatsAppClient
     */
    protected $whatsapp;

    /**
     * Construtor
     *
     * @param WhatsAppClient $whatsapp
     */
    public function __construct(WhatsAppClient $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Manipula eventos de webhooks
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        // Validar assinatura
        if (!$this->validateSignature($request)) {
            Log::error('WhatsApp Webhook: Assinatura inválida', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
            ]);
            
            throw new InvalidSignatureException('Assinatura inválida');
        }

        // Validar payload
        $validator = Validator::make($request->all(), [
            'event' => 'required|string',
            'timestamp' => 'required|numeric',
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Payload inválido',
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        // Processar evento
        try {
            $event = $request->input('event');
            $data = $request->input('data');
            
            Log::debug('WhatsApp Webhook: Evento recebido', [
                'event' => $event,
                'data' => $data
            ]);
            
            // Processar diferentes tipos de eventos
            switch ($event) {
                case 'message':
                    $this->handleMessage($data);
                    break;
                    
                case 'status':
                case 'qr':
                case 'ready':
                case 'disconnected':
                    $this->handleSessionEvent($event, $data);
                    break;
                
                default:
                    Log::info("WhatsApp: Evento genérico {$event}", ['data' => $data]);
                    break;
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Evento processado com sucesso'
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook: Erro ao processar evento', [
                'event' => $request->input('event'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Erro ao processar evento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Valida a assinatura do webhook
     * 
     * @param Request $request
     * @return bool
     */
    protected function validateSignature(Request $request)
    {
        $signature = $request->header('X-WhatsApp-Signature');
        
        if (empty($signature)) {
            return false;
        }
        
        $secret = config('whatsapp.webhook.secret');
        
        if (empty($secret)) {
            Log::warning('WhatsApp Webhook: Secret não configurado. Validação desabilitada.');
            return true;
        }
        
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Manipula evento de mensagem
     * 
     * @param array $data
     * @return void
     */
    protected function handleMessage(array $data)
    {
        // Disparar evento
        if (config('whatsapp.broadcast.enabled', true)) {
            event(new WhatsAppMessageReceived($data));
        }
        
        $from = $data['from'] ?? 'desconhecido';
        $body = $data['body'] ?? '(sem conteúdo)';
        $type = $data['type'] ?? 'text';
        
        Log::info("WhatsApp: Mensagem recebida de {$from}", [
            'type' => $type,
            'body' => $body,
            'data' => $data
        ]);
    }
    
    /**
     * Manipula eventos de sessão
     * 
     * @param string $event
     * @param array $data
     * @return void
     */
    protected function handleSessionEvent(string $event, array $data)
    {
        // Disparar evento
        if (config('whatsapp.broadcast.enabled', true)) {
            event(new WhatsAppSessionEvent($event, $data));
        }
        
        $sessionId = $data['session'] ?? 'default';
        
        Log::info("WhatsApp: Evento de sessão {$event} para {$sessionId}", [
            'event' => $event,
            'data' => $data
        ]);
    }
} 