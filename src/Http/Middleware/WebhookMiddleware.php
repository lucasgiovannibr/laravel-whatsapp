<?php

namespace LucasGiovanni\LaravelWhatsApp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookMiddleware
{
    /**
     * Verifica a assinatura do webhook para garantir que a requisição é válida
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Se a verificação está desabilitada, continuar
        if (!config('whatsapp.webhook.verify_signature', true)) {
            return $next($request);
        }
        
        $signature = $request->header('X-Webhook-Signature');
        
        // Se não houver assinatura, retornar erro 401
        if (!$signature) {
            Log::warning('WhatsApp webhook sem assinatura');
            return response()->json(['error' => 'Assinatura não fornecida'], 401);
        }
        
        $payload = $request->getContent();
        $secret = config('whatsapp.webhook.secret');
        
        // Se não houver segredo configurado, logar aviso e continuar
        if (!$secret) {
            Log::warning('WhatsApp webhook: segredo não configurado');
            return $next($request);
        }
        
        // Verificar assinatura
        $calculatedSignature = hash_hmac('sha256', $payload, $secret);
        
        if (!hash_equals($calculatedSignature, $signature)) {
            Log::warning('WhatsApp webhook com assinatura inválida', [
                'expected' => $calculatedSignature,
                'received' => $signature
            ]);
            return response()->json(['error' => 'Assinatura inválida'], 401);
        }
        
        // Adicionar correlation ID ao request se enviado pela API
        if ($correlationId = $request->header('X-Correlation-ID')) {
            $request->attributes->set('correlation_id', $correlationId);
            
            // Adicionar aos logs
            Log::withContext([
                'correlation_id' => $correlationId
            ]);
        }
        
        return $next($request);
    }
} 