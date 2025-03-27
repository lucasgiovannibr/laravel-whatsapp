<?php

namespace DesterroShop\LaravelWhatsApp\Contracts;

interface WhatsAppClient
{
    /**
     * Autenticar na API e obter token de acesso
     *
     * @param string|null $apiKey
     * @return array
     */
    public function authenticate(?string $apiKey = null): array;

    /**
     * Atualizar token de acesso usando refresh token
     *
     * @param string $refreshToken
     * @return array
     */
    public function refreshToken(string $refreshToken): array;

    /**
     * Definir token de acesso para próximas requisições
     *
     * @param string $token
     * @return self
     */
    public function withToken(string $token): self;

    /**
     * Definir ID de correlação para rastreamento
     *
     * @param string $correlationId
     * @return self
     */
    public function withCorrelationId(string $correlationId): self;

    /**
     * Definir ID de transação para operações atômicas
     *
     * @param string $transactionId
     * @return self
     */
    public function withTransaction(string $transactionId): self;

    /**
     * Iniciar uma transação para operações atômicas
     * 
     * @return string ID da transação
     */
    public function beginTransaction(): string;

    /**
     * Confirmar uma transação
     * 
     * @param string $transactionId
     * @return bool
     */
    public function commitTransaction(string $transactionId): bool;

    /**
     * Reverter uma transação
     * 
     * @param string $transactionId
     * @return bool
     */
    public function rollbackTransaction(string $transactionId): bool;

    /**
     * Enviar mensagem de texto
     *
     * @param string $to
     * @param string $message
     * @param array $options
     * @param string|null $session
     * @return array
     */
    public function sendText(string $to, string $message, array $options = [], ?string $session = null): array;

    /**
     * Enviar mensagem com template
     *
     * @param string $to
     * @param string $template
     * @param array $data
     * @param string|null $session
     * @return array
     */
    public function sendTemplate(string $to, string $template, array $data = [], ?string $session = null): array;

    /**
     * Enviar mídia (imagem, vídeo, documento, áudio)
     *
     * @param string $to
     * @param string $url
     * @param string $type
     * @param string|null $caption
     * @param string|null $session
     * @return array
     */
    public function sendMedia(string $to, string $url, string $type, ?string $caption = null, ?string $session = null): array;

    /**
     * Enviar imagem
     *
     * @param string $to
     * @param string $url
     * @param string|null $caption
     * @param string|null $session
     * @return array
     */
    public function sendImage(string $to, string $url, ?string $caption = null, ?string $session = null): array;

    /**
     * Enviar arquivo/documento
     *
     * @param string $to
     * @param string $url
     * @param string|null $filename
     * @param string|null $session
     * @return array
     */
    public function sendFile(string $to, string $url, ?string $filename = null, ?string $session = null): array;

    /**
     * Enviar áudio
     *
     * @param string $to
     * @param string $url
     * @param string|null $session
     * @return array
     */
    public function sendAudio(string $to, string $url, ?string $session = null): array;

    /**
     * Enviar vídeo
     *
     * @param string $to
     * @param string $url
     * @param string|null $caption
     * @param string|null $session
     * @return array
     */
    public function sendVideo(string $to, string $url, ?string $caption = null, ?string $session = null): array;

    /**
     * Enviar localização
     *
     * @param string $to
     * @param float $latitude
     * @param float $longitude
     * @param string|null $title
     * @param string|null $session
     * @return array
     */
    public function sendLocation(string $to, float $latitude, float $longitude, ?string $title = null, ?string $session = null): array;

    /**
     * Enviar contato
     *
     * @param string $to
     * @param array $contact
     * @param string|null $session
     * @return array
     */
    public function sendContact(string $to, array $contact, ?string $session = null): array;

    /**
     * Enviar mensagem com botões
     *
     * @param string $to
     * @param string $text
     * @param array $buttons
     * @param string|null $session
     * @return array
     */
    public function sendButtons(string $to, string $text, array $buttons, ?string $session = null): array;

    /**
     * Enviar lista de opções
     *
     * @param string $to
     * @param string $title
     * @param string $description
     * @param string $buttonText
     * @param array $sections
     * @param string|null $session
     * @return array
     */
    public function sendList(string $to, string $title, string $description, string $buttonText, array $sections, ?string $session = null): array;

    /**
     * Enviar enquete
     *
     * @param string $to
     * @param string $question
     * @param array $options
     * @param bool $multiSelect
     * @param string|null $session
     * @return array
     */
    public function sendPoll(string $to, string $question, array $options, bool $multiSelect = false, ?string $session = null): array;

    /**
     * Enviar produto do catálogo
     *
     * @param string $to
     * @param string $catalogId
     * @param string $productId
     * @param string|null $session
     * @return array
     */
    public function sendProduct(string $to, string $catalogId, string $productId, ?string $session = null): array;

    /**
     * Enviar catálogo de produtos
     *
     * @param string $to
     * @param string $catalogId
     * @param array|null $productIds
     * @param string|null $session
     * @return array
     */
    public function sendCatalog(string $to, string $catalogId, ?array $productIds = null, ?string $session = null): array;

    /**
     * Enviar pedido
     *
     * @param string $to
     * @param array $orderData
     * @param string|null $session
     * @return array
     */
    public function sendOrder(string $to, array $orderData, ?string $session = null): array;

    /**
     * Agendar mensagem
     *
     * @param array $messageData
     * @return array
     */
    public function scheduleMessage(array $messageData): array;

    /**
     * Cancelar mensagem agendada
     *
     * @param string $messageId
     * @return array
     */
    public function cancelScheduledMessage(string $messageId): array;

    /**
     * Enviar reação a uma mensagem
     *
     * @param string $to
     * @param string $messageId
     * @param string $emoji
     * @param string|null $session
     * @return array
     */
    public function sendReaction(string $to, string $messageId, string $emoji, ?string $session = null): array;

    /**
     * Enviar sticker
     *
     * @param string $to
     * @param string $url
     * @param string|null $session
     * @return array
     */
    public function sendSticker(string $to, string $url, ?string $session = null): array;

    /**
     * Registrar webhook
     *
     * @param string $url
     * @param array $events
     * @return array
     */
    public function registerWebhook(string $url, array $events = []): array;

    /**
     * Listar webhooks
     *
     * @return array
     */
    public function listWebhooks(): array;

    /**
     * Remover webhook
     *
     * @param string $url
     * @return array
     */
    public function removeWebhook(string $url): array;

    /**
     * Obter logs pelo ID de correlação
     *
     * @param string $correlationId
     * @return array
     */
    public function getLogsByCorrelationId(string $correlationId): array;

    /**
     * Verificar token do Sanctum e autenticar com a API
     *
     * @param string $sanctumToken
     * @return array
     */
    public function verifySanctumToken(string $sanctumToken): array;
} 