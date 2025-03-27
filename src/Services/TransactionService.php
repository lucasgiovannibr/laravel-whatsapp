<?php

namespace DesterroShop\LaravelWhatsApp\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionService
{
    /**
     * @var \DesterroShop\LaravelWhatsApp\Services\WhatsAppService
     */
    protected $whatsappService;
    
    /**
     * Cache de transações ativas
     *
     * @var array
     */
    protected $activeTransactions = [];
    
    /**
     * Construtor
     *
     * @param \DesterroShop\LaravelWhatsApp\Services\WhatsAppService $whatsappService
     */
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }
    
    /**
     * Iniciar uma nova transação
     *
     * @param string|null $transactionId ID personalizado ou null para gerar automaticamente
     * @param array $options Opções da transação
     * @return string ID da transação
     */
    public function begin(?string $transactionId = null, array $options = []): string
    {
        // Gerar ID se não fornecido
        $transactionId = $transactionId ?? Str::uuid()->toString();
        
        try {
            // Iniciar transação na API
            $result = $this->whatsappService->beginTransaction($transactionId, $options);
            
            // Armazenar no cache
            $this->storeTransaction($transactionId, $options);
            
            Log::info("Transação iniciada: {$transactionId}", [
                'transactionId' => $transactionId,
                'options' => $options
            ]);
            
            return $transactionId;
        } catch (\Exception $e) {
            Log::error("Erro ao iniciar transação: {$e->getMessage()}", [
                'transactionId' => $transactionId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Confirmar uma transação
     *
     * @param string $transactionId ID da transação
     * @return bool Sucesso
     */
    public function commit(string $transactionId): bool
    {
        try {
            // Confirmar transação na API
            $result = $this->whatsappService->commitTransaction($transactionId);
            
            // Remover do cache
            $this->removeTransaction($transactionId);
            
            Log::info("Transação confirmada: {$transactionId}", [
                'transactionId' => $transactionId
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao confirmar transação: {$e->getMessage()}", [
                'transactionId' => $transactionId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Reverter uma transação
     *
     * @param string $transactionId ID da transação
     * @return bool Sucesso
     */
    public function rollback(string $transactionId): bool
    {
        try {
            // Reverter transação na API
            $result = $this->whatsappService->rollbackTransaction($transactionId);
            
            // Remover do cache
            $this->removeTransaction($transactionId);
            
            Log::info("Transação revertida: {$transactionId}", [
                'transactionId' => $transactionId
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao reverter transação: {$e->getMessage()}", [
                'transactionId' => $transactionId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Obter status de uma transação
     *
     * @param string $transactionId ID da transação
     * @return array Dados da transação
     */
    public function getStatus(string $transactionId): array
    {
        try {
            return $this->whatsappService->getTransactionStatus($transactionId);
        } catch (\Exception $e) {
            Log::error("Erro ao obter status da transação: {$e->getMessage()}", [
                'transactionId' => $transactionId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Verificar se uma transação está ativa
     *
     * @param string $transactionId ID da transação
     * @return bool Se está ativa
     */
    public function isActive(string $transactionId): bool
    {
        try {
            $status = $this->getStatus($transactionId);
            return $status['active'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Executar uma operação dentro de uma transação
     *
     * @param callable $callback Função a ser executada
     * @param array $options Opções da transação
     * @return mixed Resultado da função
     * @throws \Exception Em caso de erro
     */
    public function execute(callable $callback, array $options = [])
    {
        $transactionId = $this->begin(null, $options);
        
        try {
            // Executar o callback com o ID da transação
            $result = $callback($transactionId, $this);
            
            // Confirmar a transação
            $this->commit($transactionId);
            
            return $result;
        } catch (\Exception $e) {
            // Reverter a transação em caso de erro
            $this->rollback($transactionId);
            
            throw $e;
        }
    }
    
    /**
     * Armazenar transação no cache
     *
     * @param string $transactionId ID da transação
     * @param array $options Opções da transação
     * @return void
     */
    protected function storeTransaction(string $transactionId, array $options): void
    {
        $this->activeTransactions[$transactionId] = [
            'id' => $transactionId,
            'createdAt' => now(),
            'options' => $options
        ];
        
        // Armazenar também no cache do Laravel
        Cache::put("whatsapp_transaction_{$transactionId}", [
            'id' => $transactionId,
            'createdAt' => now(),
            'options' => $options
        ], 60 * 24); // Expirar em 24 horas
    }
    
    /**
     * Remover transação do cache
     *
     * @param string $transactionId ID da transação
     * @return void
     */
    protected function removeTransaction(string $transactionId): void
    {
        if (isset($this->activeTransactions[$transactionId])) {
            unset($this->activeTransactions[$transactionId]);
        }
        
        Cache::forget("whatsapp_transaction_{$transactionId}");
    }
    
    /**
     * Limpar transações expiradas
     *
     * @param int $olderThanMinutes Limpar transações mais antigas que X minutos
     * @return int Número de transações limpas
     */
    public function cleanupExpiredTransactions(int $olderThanMinutes = 60): int
    {
        $count = 0;
        $cutoff = now()->subMinutes($olderThanMinutes);
        
        foreach ($this->activeTransactions as $transactionId => $data) {
            if ($data['createdAt'] < $cutoff) {
                try {
                    $this->rollback($transactionId);
                    $count++;
                } catch (\Exception $e) {
                    Log::warning("Erro ao limpar transação expirada: {$e->getMessage()}", [
                        'transactionId' => $transactionId
                    ]);
                    
                    // Remover do cache mesmo assim
                    $this->removeTransaction($transactionId);
                    $count++;
                }
            }
        }
        
        Log::info("Limpeza de transações concluída: {$count} transações limpas");
        
        return $count;
    }
} 