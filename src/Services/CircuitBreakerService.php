<?php

namespace LucasGiovanni\LaravelWhatsApp\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CircuitBreakerService
{
    /**
     * @var \LucasGiovanni\LaravelWhatsApp\Services\WhatsAppService
     */
    protected $whatsappService;
    
    /**
     * Construtor
     *
     * @param \LucasGiovanni\LaravelWhatsApp\Services\WhatsAppService $whatsappService
     */
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }
    
    /**
     * Obter todos os circuit breakers
     *
     * @return array Status de todos os circuit breakers
     */
    public function getAll(): array
    {
        try {
            return $this->whatsappService->getCircuitBreakerStatus();
        } catch (\Exception $e) {
            Log::error('Erro ao obter status dos circuit breakers', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Obter status de um circuit breaker específico
     *
     * @param string $service Nome do serviço
     * @return array Status do circuit breaker
     */
    public function get(string $service): array
    {
        try {
            return $this->whatsappService->getCircuitBreakerStatus($service);
        } catch (\Exception $e) {
            Log::error("Erro ao obter status do circuit breaker para o serviço '{$service}'", [
                'error' => $e->getMessage(),
                'service' => $service
            ]);
            return [];
        }
    }
    
    /**
     * Resetar manualmente um circuit breaker
     *
     * @param string $service Nome do serviço
     * @return bool Se foi resetado com sucesso
     */
    public function reset(string $service): bool
    {
        try {
            $result = $this->whatsappService->resetCircuitBreaker($service);
            
            Log::info("Circuit breaker para o serviço '{$service}' foi resetado manualmente", [
                'service' => $service
            ]);
            
            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error("Erro ao resetar circuit breaker para o serviço '{$service}'", [
                'error' => $e->getMessage(),
                'service' => $service
            ]);
            return false;
        }
    }
    
    /**
     * Verificar se um circuit breaker está aberto
     *
     * @param string $service Nome do serviço
     * @return bool Se está aberto
     */
    public function isOpen(string $service): bool
    {
        try {
            $status = $this->get($service);
            return $status['state'] === 'open';
        } catch (\Exception $e) {
            Log::error("Erro ao verificar se o circuit breaker para o serviço '{$service}' está aberto", [
                'error' => $e->getMessage(),
                'service' => $service
            ]);
            return false;
        }
    }
    
    /**
     * Verificar se um circuit breaker está meio-aberto
     *
     * @param string $service Nome do serviço
     * @return bool Se está meio-aberto
     */
    public function isHalfOpen(string $service): bool
    {
        try {
            $status = $this->get($service);
            return $status['state'] === 'half-open';
        } catch (\Exception $e) {
            Log::error("Erro ao verificar se o circuit breaker para o serviço '{$service}' está meio-aberto", [
                'error' => $e->getMessage(),
                'service' => $service
            ]);
            return false;
        }
    }
    
    /**
     * Verificar se um circuit breaker está fechado
     *
     * @param string $service Nome do serviço
     * @return bool Se está fechado
     */
    public function isClosed(string $service): bool
    {
        try {
            $status = $this->get($service);
            return $status['state'] === 'closed';
        } catch (\Exception $e) {
            Log::error("Erro ao verificar se o circuit breaker para o serviço '{$service}' está fechado", [
                'error' => $e->getMessage(),
                'service' => $service
            ]);
            return true; // Assume fechado por padrão
        }
    }
    
    /**
     * Executar uma função com circuit breaker
     *
     * @param string $service Nome do serviço
     * @param callable $callback Função a ser executada
     * @param callable|null $fallback Função de fallback em caso de circuit aberto
     * @return mixed Resultado da função ou do fallback
     * @throws \Exception Se o circuit breaker estiver aberto e não houver fallback
     */
    public function execute(string $service, callable $callback, callable $fallback = null)
    {
        // Se o circuit breaker estiver aberto, não executar a função
        if ($this->isOpen($service)) {
            Log::warning("Circuit breaker para o serviço '{$service}' está aberto, operação rejeitada", [
                'service' => $service
            ]);
            
            // Se existir fallback, executar
            if ($fallback !== null) {
                return $fallback();
            }
            
            throw new \Exception("Serviço '{$service}' indisponível temporariamente (circuit breaker aberto)");
        }
        
        try {
            // Executar a função protegida
            $result = $callback();
            
            // Se estiver meio-aberto, fechar o circuit breaker após sucesso
            if ($this->isHalfOpen($service)) {
                $this->reset($service);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error("Falha na execução protegida por circuit breaker para o serviço '{$service}'", [
                'error' => $e->getMessage(),
                'service' => $service
            ]);
            
            // Se existir fallback, executar
            if ($fallback !== null) {
                return $fallback($e);
            }
            
            throw $e;
        }
    }
} 