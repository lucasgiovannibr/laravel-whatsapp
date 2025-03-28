<?php

namespace LucasGiovanni\LaravelWhatsApp\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CorrelationIdMiddleware
{
    /**
     * Cabeçalho para o ID de correlação
     *
     * @var string
     */
    protected $header = 'X-Correlation-ID';

    /**
     * Manipular a requisição
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Obter o ID de correlação do cabeçalho, ou gerar um novo
        $correlationId = $request->header($this->header) ?? $this->generateCorrelationId();

        // Definir o ID de correlação no request para uso futuro
        $request->correlationId = $correlationId;

        // Adicionar ao contexto de log
        Log::withContext(['correlation_id' => $correlationId]);

        // Adicionar o ID de correlação à resposta
        $response = $next($request);
        $response->headers->set($this->header, $correlationId);

        return $response;
    }

    /**
     * Gerar um novo ID de correlação
     *
     * @return string
     */
    protected function generateCorrelationId(): string
    {
        // Geração mais simples: uuid
        return (string) Str::uuid();
    }

    /**
     * Registrar o middleware no kernel HTTP
     *
     * Adicione isto ao seu App\Http\Kernel:
     * protected $middlewareGroups = [
     *     'api' => [
     *         // ...
     *         \LucasGiovanni\LaravelWhatsApp\Middleware\CorrelationIdMiddleware::class,
     *     ],
     * ];
     */
} 