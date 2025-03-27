<?php

namespace DesterroShop\LaravelWhatsApp\Contracts;

interface WhatsAppClient
{
    /**
     * Autenticar e obter token JWT
     *
     * @return array Tokens de autenticação
     */
    public function authenticate(): array;

    /**
     * Renovar o token usando refresh token
     *
     * @param string $refreshToken Token de atualização
     * @return array Novos tokens
     */
    public function refreshToken(string $refreshToken): array;

    /**
     * Definir token para uso nas requisições
     *
     * @param string $token Token JWT
     * @return self
     */
    public function withToken(string $token): self;

    /**
     * Definir ID de correlação para rastreamento entre sistemas
     *
     * @param string|null $correlationId ID de correlação ou null para gerar um novo
     * @return self
     */
    public function withCorrelationId(?string $correlationId = null): self;

    /**
     * Iniciar uma transação para garantir atomicidade entre operações
     *
     * @return string ID da transação
     */
    public function beginTransaction(): string;

    /**
     * Definir ID de transação para próximas operações
     *
     * @param string $transactionId ID da transação
     * @return self
     */
    public function withTransaction(string $transactionId): self;

    /**
     * Confirmar uma transação
     *
     * @param string $transactionId ID da transação
     * @return array Resposta da API
     */
    public function commitTransaction(string $transactionId): array;

    /**
     * Reverter uma transação
     *
     * @param string $transactionId ID da transação
     * @return array Resposta da API
     */
    public function rollbackTransaction(string $transactionId): array;

    /**
     * Obter status do circuit breaker
     *
     * @param string|null $service Nome do serviço específico ou null para todos
     * @return array Status do circuit breaker
     */
    public function getCircuitBreakerStatus(?string $service = null): array;

    /**
     * Resetar manualmente um circuit breaker
     *
     * @param string $service Nome do serviço
     * @return array Resposta da API
     */
    public function resetCircuitBreaker(string $service): array;

    /**
     * Enviar uma mensagem de texto para um número
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $message Texto da mensagem
     * @param array $options Opções adicionais (priority, delay, quoted_message_id)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendText(string $to, string $message, array $options = [], ?string $sessionId = null): array;
    
    /**
     * Enviar uma mensagem usando um template
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $templateName Nome do template
     * @param array $data Dados para renderizar o template
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendTemplate(string $to, string $templateName, array $data = [], ?string $sessionId = null): array;
    
    /**
     * Enviar uma imagem
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $imageUrl URL da imagem ou caminho para o arquivo
     * @param string|null $caption Legenda da imagem (opcional)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendImage(string $to, string $imageUrl, ?string $caption = null, ?string $sessionId = null): array;
    
    /**
     * Enviar um arquivo
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $fileUrl URL do arquivo ou caminho para o arquivo
     * @param string|null $filename Nome do arquivo (opcional)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendFile(string $to, string $fileUrl, ?string $filename = null, ?string $sessionId = null): array;
    
    /**
     * Enviar um áudio
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $audioUrl URL do áudio ou caminho para o arquivo
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendAudio(string $to, string $audioUrl, ?string $sessionId = null): array;
    
    /**
     * Enviar uma localização
     *
     * @param string $to Número de telefone no formato internacional
     * @param float $latitude Latitude
     * @param float $longitude Longitude
     * @param string|null $title Título da localização (opcional)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendLocation(string $to, float $latitude, float $longitude, ?string $title = null, ?string $sessionId = null): array;
    
    /**
     * Enviar um contato
     *
     * @param string $to Número de telefone no formato internacional
     * @param array $contact Dados do contato
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendContact(string $to, array $contact, ?string $sessionId = null): array;
    
    /**
     * Enviar um botão
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $bodyText Texto principal da mensagem
     * @param array $buttons Array de botões
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendButtons(string $to, string $bodyText, array $buttons, ?string $sessionId = null): array;
    
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
     */
    public function sendList(string $to, string $title, string $description, string $buttonText, array $sections, ?string $sessionId = null): array;
    
    /**
     * Enviar uma enquete
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $question Pergunta da enquete
     * @param array $options Opções de resposta
     * @param bool $isMultiSelect Permitir múltiplas seleções
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendPoll(string $to, string $question, array $options, bool $isMultiSelect = false, ?string $sessionId = null): array;
    
    /**
     * Enviar um produto
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $catalogId ID do catálogo
     * @param string $productId ID do produto
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendProduct(string $to, string $catalogId, string $productId, ?string $sessionId = null): array;
    
    /**
     * Enviar um catálogo de produtos
     *
     * @param string $to Número de telefone no formato internacional
     * @param string $catalogId ID do catálogo
     * @param array $productItems Lista de IDs de produtos
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendCatalog(string $to, string $catalogId, array $productItems = [], ?string $sessionId = null): array;
    
    /**
     * Enviar um pedido
     *
     * @param string $to Número de telefone no formato internacional
     * @param array $orderData Dados do pedido (produtos, quantidades, preços)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Resposta da API
     */
    public function sendOrder(string $to, array $orderData, ?string $sessionId = null): array;
    
    /**
     * Obter todas as sessões ativas
     *
     * @return array Lista de sessões
     */
    public function getSessions(): array;
    
    /**
     * Criar uma nova sessão
     *
     * @param string $sessionId ID da sessão
     * @return array Resposta da API
     */
    public function createSession(string $sessionId): array;
    
    /**
     * Excluir uma sessão
     *
     * @param string $sessionId ID da sessão
     * @return array Resposta da API
     */
    public function deleteSession(string $sessionId): array;
    
    /**
     * Verificar status da sessão
     *
     * @param string $sessionId ID da sessão
     * @return array Status da sessão
     */
    public function getSessionStatus(string $sessionId): array;
    
    /**
     * Obter o QR Code para uma sessão
     *
     * @param string $sessionId ID da sessão
     * @return array QR Code em formato base64
     */
    public function getQrCode(string $sessionId): array;
    
    /**
     * Obter o histórico de mensagens de um número
     *
     * @param string $number Número de telefone no formato internacional
     * @param int $limit Limite de mensagens (opcional, padrão: 50)
     * @param string|null $sessionId ID da sessão WhatsApp (opcional)
     * @return array Lista de mensagens
     */
    public function getMessages(string $number, int $limit = 50, ?string $sessionId = null): array;
    
    /**
     * Configurar um webhook para eventos
     *
     * @param string $url URL do webhook
     * @param array $events Lista de eventos para assinar
     * @return array Resposta da API
     */
    public function setWebhook(string $url, array $events = []): array;

    /**
     * Agendar uma mensagem para envio futuro
     *
     * @param array $data Dados da mensagem
     * @return array Resposta da API
     */
    public function scheduleMessage(array $data): array;

    /**
     * Cancelar uma mensagem agendada
     *
     * @param string $messageId ID da mensagem agendada
     * @return array Resposta da API
     */
    public function cancelScheduledMessage(string $messageId): array;

    /**
     * Obter logs por ID de correlação
     *
     * @param string $correlationId ID de correlação
     * @return array Logs relacionados ao ID de correlação
     */
    public function getLogsByCorrelationId(string $correlationId): array;

    /**
     * Verificar token do Laravel Sanctum e autenticar com a API WhatsApp
     *
     * @param string $sanctumToken Token do Laravel Sanctum
     * @return array Resultado da autenticação
     */
    public function verifySanctumToken(string $sanctumToken): array;
} 