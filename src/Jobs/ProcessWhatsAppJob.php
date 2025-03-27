<?php

namespace DesterroShop\LaravelWhatsApp\Jobs;

use DesterroShop\LaravelWhatsApp\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tipo de job (envio de mensagem, webhook, etc)
     * 
     * @var string
     */
    protected $type;

    /**
     * Dados do job
     * 
     * @var array
     */
    protected $data;

    /**
     * Opções adicionais (correlationId, transactionId, etc)
     * 
     * @var array
     */
    protected $options;

    /**
     * Número de tentativas por padrão
     * 
     * @var int
     */
    public $tries = 3;

    /**
     * O número de segundos de espera antes que o job seja processado
     *
     * @var int
     */
    public $delay = 0;

    /**
     * O timeout do job em segundos
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Criar um novo job
     *
     * @param string $type Tipo de job
     * @param array $data Dados do job
     * @param array $options Opções adicionais
     * @return void
     */
    public function __construct(string $type, array $data, array $options = [])
    {
        $this->type = $type;
        $this->data = $data;
        $this->options = $options;

        // Configurar tentativas
        if (isset($options['attempts']) && is_int($options['attempts'])) {
            $this->tries = $options['attempts'];
        }

        // Configurar delay
        if (isset($options['delay']) && is_int($options['delay'])) {
            $this->delay = $options['delay'];
        }

        // Configurar timeout
        if (isset($options['timeout']) && is_int($options['timeout'])) {
            $this->timeout = $options['timeout'];
        }

        // Configurar fila
        if (isset($options['queue'])) {
            $this->queue = $options['queue'];
        } elseif (isset($options['priority'])) {
            // Mapeamento de prioridade para nome de fila
            $priorityMap = [
                'high' => 'whatsapp-high',
                'medium' => 'whatsapp-default',
                'low' => 'whatsapp-low'
            ];
            $this->queue = $priorityMap[$options['priority']] ?? 'whatsapp-default';
        }

        // Configurar connection
        if (isset($options['connection'])) {
            $this->connection = $options['connection'];
        }
    }

    /**
     * Executar o job
     *
     * @param WhatsAppService $whatsappService
     * @return void
     */
    public function handle(WhatsAppService $whatsappService)
    {
        $context = [
            'jobId' => $this->job->getJobId(),
            'type' => $this->type,
            'queue' => $this->queue,
        ];

        try {
            // Adicionar correlation ID se disponível
            if (!empty($this->options['correlation_id'])) {
                $whatsappService = $whatsappService->withCorrelationId($this->options['correlation_id']);
                $context['correlation_id'] = $this->options['correlation_id'];
            }

            // Adicionar transaction ID se disponível
            if (!empty($this->options['transaction_id'])) {
                $whatsappService = $whatsappService->withTransaction($this->options['transaction_id']);
                $context['transaction_id'] = $this->options['transaction_id'];
            }

            Log::info("Processando job WhatsApp: {$this->type}", $context);

            // Processar o job com base no tipo
            switch ($this->type) {
                case 'send-text':
                    $result = $whatsappService->sendText(
                        $this->data['to'],
                        $this->data['message'],
                        $this->data['options'] ?? [],
                        $this->data['sessionId'] ?? null
                    );
                    break;

                case 'send-template':
                    $result = $whatsappService->sendTemplate(
                        $this->data['to'],
                        $this->data['templateName'],
                        $this->data['data'] ?? [],
                        $this->data['sessionId'] ?? null
                    );
                    break;

                case 'send-media':
                    $result = $whatsappService->sendMedia(
                        $this->data['to'],
                        $this->data['mediaUrl'],
                        $this->data['mediaType'],
                        $this->data['caption'] ?? null,
                        $this->data['sessionId'] ?? null
                    );
                    break;

                case 'webhook-event':
                    // Processar evento de webhook
                    $result = $this->processWebhookEvent($whatsappService, $this->data);
                    break;

                default:
                    throw new \InvalidArgumentException("Tipo de job desconhecido: {$this->type}");
            }

            Log::info("Job WhatsApp {$this->type} processado com sucesso", array_merge($context, ['result' => $result]));

            return $result;
        } catch (\Exception $e) {
            Log::error("Erro ao processar job WhatsApp {$this->type}: {$e->getMessage()}", array_merge($context, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]));

            // Se estiver usando transação, fazer rollback
            if (!empty($this->options['transaction_id'])) {
                try {
                    $whatsappService->rollbackTransaction($this->options['transaction_id']);
                    Log::info("Transação {$this->options['transaction_id']} revertida após falha no job", $context);
                } catch (\Exception $rollbackException) {
                    Log::error("Erro ao reverter transação: {$rollbackException->getMessage()}", array_merge($context, [
                        'error' => $rollbackException->getMessage()
                    ]));
                }
            }

            // Se for a última tentativa, logar
            if ($this->attempts() >= $this->tries) {
                Log::warning("Job WhatsApp {$this->type} falhou após {$this->tries} tentativas", $context);
            }

            throw $e;
        }
    }

    /**
     * Processar evento de webhook
     *
     * @param WhatsAppService $whatsappService
     * @param array $data Dados do evento
     * @return array
     */
    protected function processWebhookEvent(WhatsAppService $whatsappService, array $data)
    {
        // Implementação específica para processar eventos de webhook
        // Isso pode disparar eventos Laravel, chamar APIs, etc.
        
        // Este é apenas um stub, a implementação real dependerá das necessidades específicas
        
        return ['success' => true, 'processed' => true];
    }

    /**
     * O job falhou
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error("Job WhatsApp {$this->type} falhou permanentemente: {$exception->getMessage()}", [
            'jobId' => $this->job->getJobId(),
            'type' => $this->type,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Adicionar aqui lógica adicional para lidar com falhas permanentes
        // Por exemplo, notificar administrador, registrar em log especial, etc.
    }
} 