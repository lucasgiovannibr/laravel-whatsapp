<?php

namespace LucasGiovanni\LaravelWhatsApp\Http\Controllers;

use LucasGiovanni\LaravelWhatsApp\Facades\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * Verificar status da integração WhatsApp
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        try {
            // Obtém o status da conexão com a API
            $status = [
                'success' => true,
                'message' => 'WhatsApp API está conectada',
                'timestamp' => now()->toIso8601String()
            ];
            
            return response()->json($status);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status do WhatsApp', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao conectar com WhatsApp API: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar mensagem de texto
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendText(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'message' => 'required|string',
                'options' => 'nullable|array',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $message = $request->input('message');
            $options = $request->input('options', []);
            $session = $request->input('session');
            
            $result = WhatsApp::sendText($to, $message, $options, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem de texto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar mensagem: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar mensagem de template
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendTemplate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'template' => 'required|string',
                'data' => 'nullable|array',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $template = $request->input('template');
            $data = $request->input('data', []);
            $session = $request->input('session');
            
            $result = WhatsApp::sendTemplate($to, $template, $data, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem de template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'template' => $request->input('template')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar template: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar imagem
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendImage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'url' => 'required|url',
                'caption' => 'nullable|string',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $url = $request->input('url');
            $caption = $request->input('caption');
            $session = $request->input('session');
            
            $result = WhatsApp::sendImage($to, $url, $caption, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar imagem', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'url' => $request->input('url')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar imagem: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar arquivo/documento
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendFile(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'url' => 'required|url',
                'filename' => 'nullable|string',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $url = $request->input('url');
            $filename = $request->input('filename');
            $session = $request->input('session');
            
            $result = WhatsApp::sendFile($to, $url, $filename, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar arquivo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'url' => $request->input('url')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar arquivo: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar áudio
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendAudio(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'url' => 'required|url',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $url = $request->input('url');
            $session = $request->input('session');
            
            $result = WhatsApp::sendAudio($to, $url, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar áudio', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'url' => $request->input('url')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar áudio: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar vídeo
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendVideo(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'url' => 'required|url',
                'caption' => 'nullable|string',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $url = $request->input('url');
            $caption = $request->input('caption');
            $session = $request->input('session');
            
            $result = WhatsApp::sendVideo($to, $url, $caption, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar vídeo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'url' => $request->input('url')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar vídeo: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar mídia genérica
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMedia(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'url' => 'required|url',
                'type' => 'required|string|in:image,video,document,audio',
                'caption' => 'nullable|string',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $url = $request->input('url');
            $type = $request->input('type');
            $caption = $request->input('caption');
            $session = $request->input('session');
            
            $result = WhatsApp::sendMedia($to, $url, $type, $caption, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mídia', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'url' => $request->input('url'),
                'type' => $request->input('type')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar mídia: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar localização
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendLocation(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'title' => 'nullable|string',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $title = $request->input('title');
            $session = $request->input('session');
            
            $result = WhatsApp::sendLocation($to, $latitude, $longitude, $title, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar localização', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar localização: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar contato
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendContact(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'contact' => 'required|array',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $contact = $request->input('contact');
            $session = $request->input('session');
            
            $result = WhatsApp::sendContact($to, $contact, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar contato', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar contato: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar mensagem com botões
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendButtons(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'text' => 'required|string',
                'buttons' => 'required|array',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $text = $request->input('text');
            $buttons = $request->input('buttons');
            $session = $request->input('session');
            
            $result = WhatsApp::sendButtons($to, $text, $buttons, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar botões', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar botões: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar lista de opções
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendList(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'title' => 'required|string',
                'description' => 'required|string',
                'buttonText' => 'required|string',
                'sections' => 'required|array',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $title = $request->input('title');
            $description = $request->input('description');
            $buttonText = $request->input('buttonText');
            $sections = $request->input('sections');
            $session = $request->input('session');
            
            $result = WhatsApp::sendList($to, $title, $description, $buttonText, $sections, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar lista', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar lista: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar enquete
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendPoll(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'question' => 'required|string',
                'options' => 'required|array',
                'multiSelect' => 'boolean',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $question = $request->input('question');
            $options = $request->input('options');
            $multiSelect = $request->input('multiSelect', false);
            $session = $request->input('session');
            
            $result = WhatsApp::sendPoll($to, $question, $options, $multiSelect, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar enquete', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'question' => $request->input('question')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar enquete: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar produto
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendProduct(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'catalogId' => 'required|string',
                'productId' => 'required|string',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $catalogId = $request->input('catalogId');
            $productId = $request->input('productId');
            $session = $request->input('session');
            
            $result = WhatsApp::sendProduct($to, $catalogId, $productId, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar produto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'catalogId' => $request->input('catalogId'),
                'productId' => $request->input('productId')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar produto: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar catálogo
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendCatalog(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'catalogId' => 'required|string',
                'productIds' => 'nullable|array',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $catalogId = $request->input('catalogId');
            $productIds = $request->input('productIds');
            $session = $request->input('session');
            
            $result = WhatsApp::sendCatalog($to, $catalogId, $productIds, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar catálogo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'catalogId' => $request->input('catalogId')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar catálogo: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar pedido
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendOrder(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'orderData' => 'required|array',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $orderData = $request->input('orderData');
            $session = $request->input('session');
            
            $result = WhatsApp::sendOrder($to, $orderData, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar pedido', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar pedido: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar reação
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendReaction(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'messageId' => 'required|string',
                'emoji' => 'required|string',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $messageId = $request->input('messageId');
            $emoji = $request->input('emoji');
            $session = $request->input('session');
            
            $result = WhatsApp::sendReaction($to, $messageId, $emoji, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar reação', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'messageId' => $request->input('messageId')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar reação: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Enviar sticker
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendSticker(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'to' => 'required|string',
                'url' => 'required|url',
                'session' => 'nullable|string'
            ]);
            
            $to = $request->input('to');
            $url = $request->input('url');
            $session = $request->input('session');
            
            $result = WhatsApp::sendSticker($to, $url, $session);
            
            return response()->json([
                'success' => true,
                'message_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar sticker', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'url' => $request->input('url')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao enviar sticker: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Agendar mensagem
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scheduleMessage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|string',
                'to' => 'required|string',
                'message' => 'required_if:type,text',
                'schedule_time' => 'required|date',
                'options' => 'nullable|array',
            ]);
            
            $messageData = $request->all();
            
            $result = WhatsApp::scheduleMessage($messageData);
            
            return response()->json([
                'success' => true,
                'schedule_id' => $result['id'] ?? null,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao agendar mensagem', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $request->input('to'),
                'type' => $request->input('type')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao agendar mensagem: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Cancelar mensagem agendada
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function cancelScheduledMessage(Request $request, string $id): JsonResponse
    {
        try {
            $result = WhatsApp::cancelScheduledMessage($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Mensagem agendada cancelada com sucesso',
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar mensagem agendada', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao cancelar mensagem agendada: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Obter logs por ID de correlação
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function getLogsByCorrelationId(Request $request, string $id): JsonResponse
    {
        try {
            $logs = WhatsApp::getLogsByCorrelationId($id);
            
            return response()->json([
                'success' => true,
                'logs' => $logs,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar logs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'correlation_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Falha ao buscar logs: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }
} 