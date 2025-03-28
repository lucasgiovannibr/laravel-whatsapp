<?php

namespace LucasGiovanni\LaravelWhatsApp\Services;

use LucasGiovanni\LaravelWhatsApp\Contracts\WhatsAppClient;
use LucasGiovanni\LaravelWhatsApp\Exceptions\WhatsAppException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Client\Response;

class WhatsAppService implements WhatsAppClient
{
    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $apiToken;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $defaultSession;

    /**
     * @var PendingRequest
     */
    protected $http;

    /**
     * @var string|null
     */
    protected $correlationId;

    /**
     * @var string|null
     */
    protected $transactionId;

    /**
     * Construtor
     *
     * @param string $apiUrl URL da API WhatsApp
     * @param string $apiToken Token de autenticação
     * @param string $defaultSession ID da sessão padrão
     */
    public function __construct(string $apiUrl, string $apiToken = null, string $defaultSession = 'default')
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiToken = $apiToken;
        $this->apiKey = config('whatsapp.api_key');
        $this->defaultSession = $defaultSession;

        // Configurar cliente HTTP
        $this->http = Http::baseUrl($this->apiUrl)
            ->timeout(config('whatsapp.request_timeout', 30));

        // Adicionar token se disponível
        if ($this->apiToken) {
            $this->http = $this->http->withHeaders([
                'Authorization' => "Bearer {$this->apiToken}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
        } elseif ($this->apiKey) {
            $this->http = $this->http->withHeaders([
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
        }
    }

    /**
     * Autenticar e obter token JWT
     *
     * @param string|null $apiKey
     * @return array Tokens de autenticação
     * @throws WhatsAppException
     */
    public function authenticate(?string $apiKey = null): array
    {
        try {
            $apiKey = $apiKey ?? $this->apiKey;
            
            $response = $this->http->post('/api/auth', [
                'api_key' => $apiKey,
            ]);

            $this->checkResponse($response, 'Erro na autenticação');

            $data = $response->json();
            $this->apiToken = $data['accessToken'] ?? null;

            // Atualizar o cliente HTTP com o novo token
            if ($this->apiToken) {
                $this->http = $this->http->withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                ]);
            }

            return $data;
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro na autenticação');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Renovar o token usando refresh token
     *
     * @param string $refreshToken Token de atualização
     * @return array Novos tokens
     * @throws WhatsAppException
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            $response = $this->http->post('/api/auth/refresh', [
                'refresh_token' => $refreshToken,
            ]);

            $this->checkResponse($response, 'Erro ao renovar token');

            $data = $response->json();
            $this->apiToken = $data['accessToken'] ?? null;

            // Atualizar o cliente HTTP com o novo token
            if ($this->apiToken) {
                $this->http = $this->http->withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                ]);
            }

            return $data;
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao renovar token');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Definir token para uso nas requisições
     *
     * @param string $token Token JWT
     * @return self
     */
    public function withToken(string $token): self
    {
        $clone = clone $this;
        $clone->apiToken = $token;
        $clone->http = $clone->http->withHeaders([
            'Authorization' => "Bearer {$token}",
        ]);
        
        return $clone;
    }

    /**
     * Definir ID de correlação para rastreamento entre sistemas
     *
     * @param string|null $correlationId ID de correlação ou null para gerar um novo
     * @return self
     */
    public function withCorrelationId(?string $correlationId = null): self
    {
        $clone = clone $this;
        $clone->correlationId = $correlationId ?? (string) Str::uuid();
        $clone->http = $clone->http->withHeaders([
            'X-Correlation-ID' => $clone->correlationId,
        ]);
        
        return $clone;
    }

    /**
     * Iniciar uma transação para garantir atomicidade entre operações
     *
     * @return string ID da transação
     * @throws WhatsAppException
     */
    public function beginTransaction(): string
    {
        try {
            $response = $this->http->post('/api/transaction/begin');
            
            $this->checkResponse($response, 'Erro ao iniciar transação');
            
            return $response->json()['transaction_id'];
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao iniciar transação');
            throw new WhatsAppException('Falha ao iniciar transação: ' . $e->getMessage());
        }
    }

    /**
     * Definir ID de transação para próximas operações
     *
     * @param string $transactionId ID da transação
     * @return self
     */
    public function withTransaction(string $transactionId): self
    {
        $clone = clone $this;
        $clone->transactionId = $transactionId;
        
        return $clone;
    }

    /**
     * Confirmar uma transação
     *
     * @param string $transactionId ID da transação
     * @return bool
     * @throws WhatsAppException
     */
    public function commitTransaction(string $transactionId): bool
    {
        try {
            $response = $this->http->post('/api/transaction/commit', [
                'transaction_id' => $transactionId
            ]);
            
            $this->checkResponse($response, 'Erro ao confirmar transação');
            
            return $response->json()['success'] ?? true;
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao confirmar transação');
            return false;
        }
    }

    /**
     * Reverter uma transação
     *
     * @param string $transactionId ID da transação
     * @return bool
     * @throws WhatsAppException
     */
    public function rollbackTransaction(string $transactionId): bool
    {
        try {
            $response = $this->http->post('/api/transaction/rollback', [
                'transaction_id' => $transactionId
            ]);
            
            $this->checkResponse($response, 'Erro ao reverter transação');
            
            return $response->json()['success'] ?? true;
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao reverter transação');
            return false;
        }
    }

    /**
     * Obter status do circuit breaker
     *
     * @param string|null $service Nome do serviço específico ou null para todos
     * @return array Status do circuit breaker
     * @throws WhatsAppException
     */
    public function getCircuitBreakerStatus(?string $service = null): array
    {
        try {
            $url = '/api/circuit-breaker';
            if ($service) {
                $url .= "/{$service}";
            }
            
            $response = $this->http->get($url);
            
            $this->checkResponse($response, 'Erro ao obter status do circuit breaker');
            
            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao obter status do circuit breaker');
            throw new WhatsAppException('Falha ao obter status do circuit breaker: ' . $e->getMessage());
        }
    }

    /**
     * Resetar manualmente um circuit breaker
     *
     * @param string $service Nome do serviço
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function resetCircuitBreaker(string $service): array
    {
        try {
            $response = $this->http->post("/api/circuit-breaker/{$service}/reset");
            
            $this->checkResponse($response, 'Erro ao resetar circuit breaker');
            
            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao resetar circuit breaker');
            throw new WhatsAppException('Falha ao resetar circuit breaker: ' . $e->getMessage());
        }
    }

    /**
     * Agendar uma mensagem para envio futuro
     *
     * @param array $data Dados da mensagem
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function scheduleMessage(array $data): array
    {
        try {
            $response = $this->http->post('/api/schedule', $data);
            
            $this->checkResponse($response, 'Erro ao agendar mensagem');
            
            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao agendar mensagem');
            throw new WhatsAppException('Falha ao agendar mensagem: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar uma mensagem agendada
     *
     * @param string $messageId ID da mensagem agendada
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function cancelScheduledMessage(string $messageId): array
    {
        try {
            $response = $this->http->delete("/api/schedule/{$messageId}");
            
            $this->checkResponse($response, 'Erro ao cancelar mensagem agendada');
            
            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao cancelar mensagem agendada');
            throw new WhatsAppException('Falha ao cancelar mensagem agendada: ' . $e->getMessage());
        }
    }

    /**
     * Obter logs por ID de correlação
     *
     * @param string $correlationId ID de correlação
     * @return array Logs relacionados ao ID de correlação
     * @throws WhatsAppException
     */
    public function getLogsByCorrelationId(string $correlationId): array
    {
        try {
            $response = $this->http->get("/api/logs/correlation/{$correlationId}");
            
            $this->checkResponse($response, 'Erro ao obter logs');
            
            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao obter logs');
            throw new WhatsAppException('Falha ao obter logs: ' . $e->getMessage());
        }
    }

    /**
     * Verificar token do Laravel Sanctum e autenticar com a API WhatsApp
     *
     * @param string $sanctumToken Token do Laravel Sanctum
     * @return array Resultado da autenticação
     * @throws WhatsAppException
     */
    public function verifySanctumToken(string $sanctumToken): array
    {
        try {
            $response = $this->http->post('/api/auth/sanctum', [
                'sanctum_token' => $sanctumToken
            ]);
            
            $this->checkResponse($response, 'Erro ao verificar token Sanctum');
            
            $data = $response->json();
            $this->apiToken = $data['accessToken'] ?? null;

            // Atualizar o cliente HTTP com o novo token
            if ($this->apiToken) {
                $this->http = $this->http->withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                ]);
            }
            
            return $data;
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao verificar token Sanctum');
            throw new WhatsAppException('Falha ao verificar token Sanctum: ' . $e->getMessage());
        }
    }

    /**
     * Enviar uma mensagem de texto para um número
     *
     * @param string $to Número de telefone no formato internacional (ex: 5548999998888)
     * @param string $message Texto da mensagem
     * @param array $options Opções adicionais (priority, delay, quoted_message_id)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendText(string $to, string $message, array $options = [], ?string $sessionId = null): array
    {
        try {
            $payload = [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'to' => $this->formatPhoneNumber($to),
                'message' => $message,
            ];
            
            // Adicionar opções adicionais
            if (!empty($options)) {
                $payload = array_merge($payload, $options);
            }
            
            // Adicionar transaction_id se estiver definido
            if ($this->transactionId) {
                $payload['transaction_id'] = $this->transactionId;
            }
            
            $response = $this->http->post('/laravel/send', $payload);

            $this->checkResponse($response, 'Erro ao enviar mensagem de texto');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar mensagem de texto');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar uma mensagem usando um template
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $templateName Nome do template
     * @param array $data Dados para renderizar o template
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendTemplate(string $to, string $templateName, array $data = [], ?string $sessionId = null): array
    {
        try {
            $response = $this->http->post('/laravel/send-template', [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'to' => $this->formatPhoneNumber($to),
                'templateName' => $templateName,
                'data' => $data,
            ]);

            $this->checkResponse($response, 'Erro ao enviar template');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar template');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar uma imagem
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $imageUrl URL da imagem ou caminho para o arquivo
     * @param string|null $caption Legenda da imagem (opcional)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendImage(string $to, string $imageUrl, ?string $caption = null, ?string $sessionId = null): array
    {
        return $this->sendMedia($to, $imageUrl, 'image', $caption, $sessionId);
    }

    /**
     * Enviar um arquivo
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $fileUrl URL do arquivo ou caminho para o arquivo
     * @param string|null $filename Nome do arquivo (opcional)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendFile(string $to, string $fileUrl, ?string $filename = null, ?string $sessionId = null): array
    {
        return $this->sendMedia($to, $fileUrl, 'document', $filename, $sessionId);
    }

    /**
     * Enviar um áudio
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $audioUrl URL do áudio ou caminho para o arquivo
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendAudio(string $to, string $audioUrl, ?string $sessionId = null): array
    {
        return $this->sendMedia($to, $audioUrl, 'audio', null, $sessionId);
    }

    /**
     * Enviar mídia genérica
     *
     * @param string $to Número de telefone
     * @param string $mediaUrl URL ou caminho da mídia
     * @param string $mediaType Tipo de mídia (image, document, audio, video)
     * @param string|null $caption Legenda ou nome do arquivo
     * @param string|null $sessionId ID da sessão
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    protected function sendMedia(string $to, string $mediaUrl, string $mediaType, ?string $caption = null, ?string $sessionId = null): array
    {
        try {
            // Verificar se é um caminho de arquivo ou URL
            $isLocalFile = !filter_var($mediaUrl, FILTER_VALIDATE_URL) && Storage::exists($mediaUrl);
            
            // Método difere se for arquivo local ou URL
            if ($isLocalFile) {
                $file = Storage::get($mediaUrl);
                $fileName = basename($mediaUrl);
                
                $response = Http::asMultipart()
                    ->withHeaders(['Authorization' => "Bearer {$this->apiToken}"])
                    ->attach('file', $file, $fileName)
                    ->post("{$this->apiUrl}/laravel/send-media", [
                        'sessionId' => $sessionId ?? $this->defaultSession,
                        'to' => $this->formatPhoneNumber($to),
                        'caption' => $caption,
                    ]);
            } else {
                $response = $this->http->post('/laravel/send-media', [
                    'sessionId' => $sessionId ?? $this->defaultSession,
                    'to' => $this->formatPhoneNumber($to),
                    'mediaUrl' => $mediaUrl,
                    'mediaType' => $mediaType,
                    'caption' => $caption,
                ]);
            }

            $this->checkResponse($response, "Erro ao enviar $mediaType");

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, "Erro ao enviar $mediaType");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar uma localização
     *
     * @param string $to Número de telefone no formato internacional
     * @param float $latitude Latitude
     * @param float $longitude Longitude
     * @param string|null $title Título da localização (opcional)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendLocation(string $to, float $latitude, float $longitude, ?string $title = null, ?string $sessionId = null): array
    {
        try {
            $response = $this->http->post('/laravel/send-location', [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'to' => $this->formatPhoneNumber($to),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'title' => $title,
            ]);

            $this->checkResponse($response, 'Erro ao enviar localização');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar localização');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar um contato
     *
     * @param string $to Número de telefone no formato internacional
     * @param array $contact Dados do contato
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendContact(string $to, array $contact, ?string $sessionId = null): array
    {
        try {
            $response = $this->http->post('/laravel/send-contact', [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'to' => $this->formatPhoneNumber($to),
                'contact' => $contact,
            ]);

            $this->checkResponse($response, 'Erro ao enviar contato');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar contato');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar um botão
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $bodyText Texto principal da mensagem
     * @param array $buttons Array de botões
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendButtons(string $to, string $bodyText, array $buttons, ?string $sessionId = null): array
    {
        try {
            $response = $this->http->post('/laravel/send-buttons', [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'to' => $this->formatPhoneNumber($to),
                'bodyText' => $bodyText,
                'buttons' => $buttons,
            ]);

            $this->checkResponse($response, 'Erro ao enviar botões');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar botões');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar uma lista de opções
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $title Título da lista
     * @param string $description Descrição da lista
     * @param string $buttonText Texto do botão para mostrar a lista
     * @param array $sections Seções da lista
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendList(string $to, string $title, string $description, string $buttonText, array $sections, ?string $sessionId = null): array
    {
        try {
            $response = $this->http->post('/laravel/send-list', [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'to' => $this->formatPhoneNumber($to),
                'title' => $title,
                'description' => $description,
                'buttonText' => $buttonText,
                'sections' => $sections,
            ]);

            $this->checkResponse($response, 'Erro ao enviar lista');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar lista');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar uma enquete
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $question Pergunta da enquete
     * @param array $options Opções de resposta
     * @param bool $isMultiSelect Permitir múltiplas seleções
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendPoll(string $to, string $question, array $options, bool $isMultiSelect = false, ?string $sessionId = null): array
    {
        try {
            $response = $this->http->post('/laravel/send-poll', [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'to' => $this->formatPhoneNumber($to),
                'question' => $question,
                'options' => $options,
                'isMultiSelect' => $isMultiSelect,
            ]);

            $this->checkResponse($response, 'Erro ao enviar enquete');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar enquete');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar um produto
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $catalogId ID do catálogo
     * @param string $productId ID do produto
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendProduct(string $to, string $catalogId, string $productId, ?string $sessionId = null): array
    {
        try {
            $response = $this->http->post('/laravel/send-product', [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'to' => $this->formatPhoneNumber($to),
                'catalogId' => $catalogId,
                'productId' => $productId,
            ]);

            $this->checkResponse($response, 'Erro ao enviar produto');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar produto');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar um catálogo de produtos
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $catalogId ID do catálogo
     * @param array $productItems Lista de IDs de produtos
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendCatalog(string $to, string $catalogId, array $productItems = [], ?string $sessionId = null): array
    {
        try {
            $response = $this->http->post('/laravel/send-catalog', [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'to' => $this->formatPhoneNumber($to),
                'catalogId' => $catalogId,
                'productItems' => $productItems,
            ]);

            $this->checkResponse($response, 'Erro ao enviar catálogo');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar catálogo');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar um pedido
     *
     * @param string $to Número de telefone no formato internacional
     * @param array $orderData Dados do pedido (produtos, quantidades, preços)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function sendOrder(string $to, array $orderData, ?string $sessionId = null): array
    {
        try {
            $response = $this->http->post('/laravel/send-order', [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'to' => $this->formatPhoneNumber($to),
                'orderData' => $orderData,
            ]);

            $this->checkResponse($response, 'Erro ao enviar pedido');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar pedido');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obter todas as sessões ativas
     *
     * @return array Lista de sessões
     * @throws WhatsAppException
     */
    public function getSessions(): array
    {
        try {
            $response = $this->http->get('/laravel/sessions');

            $this->checkResponse($response, 'Erro ao obter sessões');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao obter sessões');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Criar uma nova sessão
     *
     * @param string $sessionId ID da sessão
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function createSession(string $sessionId): array
    {
        try {
            $response = $this->http->post('/laravel/sessions', [
                'sessionId' => $sessionId,
            ]);

            $this->checkResponse($response, 'Erro ao criar sessão');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao criar sessão');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Excluir uma sessão
     *
     * @param string $sessionId ID da sessão
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function deleteSession(string $sessionId): array
    {
        try {
            $response = $this->http->delete("/laravel/sessions/{$sessionId}");

            $this->checkResponse($response, 'Erro ao excluir sessão');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao excluir sessão');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verificar status da sessão
     *
     * @param string $sessionId ID da sessão
     * @return array Status da sessão
     * @throws WhatsAppException
     */
    public function getSessionStatus(string $sessionId): array
    {
        try {
            $response = $this->http->get("/laravel/sessions/{$sessionId}/status");

            $this->checkResponse($response, 'Erro ao obter status da sessão');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao obter status da sessão');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obter o QR Code para uma sessão
     *
     * @param string $sessionId ID da sessão
     * @return array QR Code em formato base64
     * @throws WhatsAppException
     */
    public function getQrCode(string $sessionId): array
    {
        try {
            $response = $this->http->get("/laravel/sessions/{$sessionId}/qr");

            $this->checkResponse($response, 'Erro ao obter QR Code');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao obter QR Code');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obter o histórico de mensagens de um número
     *
     * @param string $number Número de telefone no formato internacional
     * @param int $limit Limite de mensagens (opcional, padrão: 50)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Lista de mensagens
     * @throws WhatsAppException
     */
    public function getMessages(string $number, int $limit = 50, ?string $sessionId = null): array
    {
        try {
            $response = $this->http->get('/laravel/messages', [
                'sessionId' => $sessionId ?? $this->defaultSession,
                'phone' => $this->formatPhoneNumber($number),
                'limit' => $limit,
            ]);

            $this->checkResponse($response, 'Erro ao obter mensagens');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao obter mensagens');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Configurar um webhook para eventos
     *
     * @param string $url URL do webhook
     * @param array $events Lista de eventos para assinar
     * @return array Resposta da API
     * @throws WhatsAppException
     */
    public function setWebhook(string $url, array $events = []): array
    {
        try {
            $response = $this->http->post('/laravel/webhook', [
                'url' => $url,
                'events' => $events,
            ]);

            $this->checkResponse($response, 'Erro ao configurar webhook');

            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao configurar webhook');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Formatar número de telefone para padrão E.164
     *
     * @param string $phoneNumber Número de telefone
     * @return string Número formatado
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remover caracteres não numéricos
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Garantir que começa com o código do país
        if (!str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }
        
        return $phone;
    }

    /**
     * Verificar resposta da API e lançar exceção se necessário
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param string $errorMessage
     * @return void
     * @throws WhatsAppException
     */
    protected function checkResponse($response, string $errorMessage): void
    {
        if (!$response->successful()) {
            $status = $response->status();
            $body = $response->json() ?? $response->body();
            
            Log::error("WhatsApp API Error: {$errorMessage}", [
                'status' => $status,
                'response' => $body,
            ]);
            
            throw new WhatsAppException("{$errorMessage}: HTTP {$status}", $status);
        }
    }

    /**
     * Tratar exceção e logar erro
     *
     * @param \Exception $exception
     * @param string $context
     * @return void
     * @throws WhatsAppException
     */
    protected function handleException(\Exception $exception, string $context): void
    {
        Log::error("WhatsApp Exception: {$context}", [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
        
        throw new WhatsAppException("{$context}: {$exception->getMessage()}", 500, $exception);
    }

    /**
     * Enviar sticker
     *
     * @param string $to
     * @param string $url
     * @param string|null $sessionId
     * @return array
     */
    public function sendSticker(string $to, string $url, ?string $sessionId = null): array
    {
        try {
            $to = $this->formatPhoneNumber($to);
            $sessionId = $sessionId ?? $this->defaultSession;

            $payload = [
                'to' => $to,
                'url' => $url,
                'session_id' => $sessionId
            ];

            // Adicionar ID de transação se definido
            if ($this->transactionId) {
                $payload['transaction_id'] = $this->transactionId;
            }

            $response = $this->http->post('/api/messages/send-sticker', $payload);
            
            $this->checkResponse($response, 'Erro ao enviar sticker');
            
            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar sticker');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar reação a uma mensagem
     *
     * @param string $to
     * @param string $messageId
     * @param string $emoji
     * @param string|null $sessionId
     * @return array
     */
    public function sendReaction(string $to, string $messageId, string $emoji, ?string $sessionId = null): array
    {
        try {
            $to = $this->formatPhoneNumber($to);
            $sessionId = $sessionId ?? $this->defaultSession;

            $payload = [
                'to' => $to,
                'message_id' => $messageId,
                'emoji' => $emoji,
                'session_id' => $sessionId
            ];

            // Adicionar ID de transação se definido
            if ($this->transactionId) {
                $payload['transaction_id'] = $this->transactionId;
            }

            $response = $this->http->post('/api/messages/send-reaction', $payload);
            
            $this->checkResponse($response, 'Erro ao enviar reação');
            
            return $response->json();
        } catch (\Exception $e) {
            $this->handleException($e, 'Erro ao enviar reação');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
} 