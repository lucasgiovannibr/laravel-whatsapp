<?php

namespace LucasGiovanni\LaravelWhatsApp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CorrelationIdMiddleware
{
    /**
     * Adiciona um ID de correlação a todas as requisições e respostas
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Obter ID de correlação do header ou gerar um novo
        $correlationId = $request->header('X-Correlation-ID') ?? (string) Str::uuid();
        
        // Armazenar no request para uso nas controllers
        $request->attributes->set('correlation_id', $correlationId);
        
        // Adicionar à resposta
        $response = $next($request);
        $response->header('X-Correlation-ID', $correlationId);
        
        // Adicionar aos logs se configurado
        if (config('whatsapp.log.correlation_id', true)) {
            \Illuminate\Support\Facades\Log::withContext([
                'correlation_id' => $correlationId
            ]);
        }
        
        return $response;
    }
} 