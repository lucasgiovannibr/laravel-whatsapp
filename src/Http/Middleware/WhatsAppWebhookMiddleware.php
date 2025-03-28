<?php

namespace LucasGiovanni\LaravelWhatsApp\Http\Middleware;

use Closure;
use LucasGiovanni\LaravelWhatsApp\Events\WhatsAppMessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppWebhookMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $keywords
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $keywords = '')
    {
        // Verificar token de webhook para segurança
        $this->validateRequest($request);

        // Extrair dados do webhook
        $data = $request->all();
        
        // Verificar se é uma mensagem recebida
        if (!isset($data['type']) || $data['type'] !== 'message' || !isset($data['body'])) {
            // Não é uma mensagem de texto ou não tem corpo, passar adiante
            return $next($request);
        }
        
        // Verificar se a mensagem contém alguma das palavras-chave
        $message = strtolower($data['body']);
        $keywordList = array_map('trim', explode(',', strtolower($keywords)));
        
        $matchesKeyword = false;
        
        foreach ($keywordList as $keyword) {
            if (empty($keyword)) {
                continue;
            }
            
            // Verificar se é um match exato ou contém a palavra-chave
            if ($keyword === '*' || $message === $keyword || Str::contains($message, $keyword)) {
                $matchesKeyword = true;
                break;
            }
        }
        
        // Disparar evento
        if (config('whatsapp.broadcast_events', true)) {
            event(new WhatsAppMessageReceived($data));
        }
        
        if ($matchesKeyword) {
            // Mensagem corresponde ao padrão, continuar para o controller
            return $next($request);
        }
        
        // Não corresponde ao padrão, retornar resposta vazia
        return response()->json(['success' => true, 'message' => 'No matching keywords']);
    }
    
    /**
     * Validar a requisição do webhook
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function validateRequest(Request $request): void
    {
        $webhookSecret = config('whatsapp.webhook_secret');
        
        // Se não tiver configurado um segredo, pular validação
        if (empty($webhookSecret)) {
            return;
        }
        
        // Verificar cabeçalho de assinatura
        $signature = $request->header('X-Webhook-Signature');
        
        if (empty($signature)) {
            Log::warning('WhatsApp webhook sem assinatura', [
                'ip' => $request->ip(),
            ]);
            
            abort(403, 'Assinatura inválida');
        }
        
        // Calcular assinatura esperada
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        // Comparar assinaturas
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('WhatsApp webhook com assinatura inválida', [
                'ip' => $request->ip(),
                'expected' => $expectedSignature,
                'received' => $signature,
            ]);
            
            abort(403, 'Assinatura inválida');
        }
    }
} 