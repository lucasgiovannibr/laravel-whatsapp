<?php

namespace DesterroWhatsApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DesterroWhatsApp\Services\WhatsAppService;
use DesterroWhatsApp\Events\WhatsAppMessageReceived;
use DesterroWhatsApp\Events\WhatsAppSessionEvent;
use DesterroWhatsApp\Exceptions\InvalidSignatureException;

class WebhookController extends Controller
{
    /**
     * WhatsApp service instance
     *
     * @var WhatsAppService
     */
    protected $whatsappService;

    /**
     * Create a new controller instance.
     *
     * @param WhatsAppService $whatsappService
     */
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Manipula eventos de webhooks enviados pelo servidor Node.js
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        // Validar assinatura HMAC
        if (!$this->validateSignature($request)) {
            Log::error('WhatsApp Webhook: Assinatura HMAC inválida', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
            ]);
            
            throw new InvalidSignatureException('Assinatura HMAC inválida');
        }

        // Validar payload
        $validator = Validator::make($request->all(), [
            'event' => 'required|string',
            'timestamp' => 'required|numeric',
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            Log::error('WhatsApp Webhook: Payload inválido', [
                'errors' => $validator->errors()->toArray(),
                'payload' => $request->all()
            ]);
            
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
            $timestamp = $request->input('timestamp');
            
            Log::debug('WhatsApp Webhook: Evento recebido', [
                'event' => $event,
                'timestamp' => date('Y-m-d H:i:s', (int)($timestamp / 1000)),
                'data' => $data
            ]);
            
            // Disparar eventos com base no tipo
            switch ($event) {
                case 'message.received':
                    $this->handleMessageReceived($data);
                    break;
                    
                case 'qr.generated':
                case 'session.ready':
                case 'session.disconnected':
                case 'session.error':
                case 'auth.failure':
                case 'group.join':
                case 'group.leave':
                    $this->handleSessionEvent($event, $data);
                    break;
                    
                case 'message.sent':
                    $this->handleMessageSent($data);
                    break;
                
                default:
                    // Eventos genéricos passam pelo evento de sessão
                    $this->handleSessionEvent($event, $data);
                    break;
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Evento processado com sucesso'
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook: Erro ao processar evento', [
                'event' => $request->input('event'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Erro ao processar evento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Valida a assinatura HMAC do webhook
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
        
        $secret = config('whatsapp.webhook_secret');
        
        if (empty($secret)) {
            Log::warning('WhatsApp Webhook: Secret não configurado. Validação HMAC desabilitada.');
            return true;
        }
        
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Manipula evento de mensagem recebida
     * 
     * @param array $data
     * @return void
     */
    protected function handleMessageReceived(array $data)
    {
        event(new WhatsAppMessageReceived($data));
        
        // Registrar no logger
        $from = $data['from'] ?? 'desconhecido';
        $body = $data['body'] ?? '(sem conteúdo)';
        $type = $data['type'] ?? 'desconhecido';
        
        Log::info("WhatsApp: Mensagem recebida de {$from}", [
            'type' => $type,
            'body' => $body,
            'data' => $data
        ]);
    }
    
    /**
     * Manipula evento de mensagem enviada
     * 
     * @param array $data
     * @return void
     */
    protected function handleMessageSent(array $data)
    {
        // Registrar no logger
        $to = $data['to'] ?? 'desconhecido';
        $type = $data['type'] ?? 'text';
        
        Log::info("WhatsApp: Mensagem enviada para {$to}", [
            'type' => $type,
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
        event(new WhatsAppSessionEvent($event, $data));
        
        // Registrar no logger
        $sessionId = $data['sessionId'] ?? 'desconhecido';
        
        Log::info("WhatsApp: Evento de sessão {$event} para {$sessionId}", [
            'event' => $event,
            'data' => $data
        ]);
    }
} 