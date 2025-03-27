<?php

namespace DesterroShop\LaravelWhatsApp\Http\Controllers;

use DesterroShop\LaravelWhatsApp\Contracts\WhatsAppClient;
use DesterroShop\LaravelWhatsApp\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class WhatsAppController extends Controller
{
    /**
     * O cliente WhatsApp
     *
     * @var WhatsAppClient
     */
    protected $whatsapp;

    /**
     * O serviço de templates
     *
     * @var TemplateService
     */
    protected $templateService;

    /**
     * Construtor
     *
     * @param WhatsAppClient $whatsapp
     * @param TemplateService $templateService
     */
    public function __construct(WhatsAppClient $whatsapp, TemplateService $templateService)
    {
        $this->whatsapp = $whatsapp;
        $this->templateService = $templateService;
    }

    /**
     * Enviar mensagem
     *
     * @param Request $request
     * @return Response
     */
    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
            'session' => 'nullable|string',
        ]);

        $result = $this->whatsapp->sendText(
            $request->input('to'),
            $request->input('message'),
            $request->input('session')
        );

        return response()->json($result);
    }

    /**
     * Enviar template
     *
     * @param Request $request
     * @return Response
     */
    public function sendTemplate(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'template' => 'required|string',
            'data' => 'required|array',
            'session' => 'nullable|string',
        ]);

        $message = $this->templateService->renderTemplate(
            $request->input('template'),
            $request->input('data')
        );

        $result = $this->whatsapp->sendText(
            $request->input('to'),
            $message,
            $request->input('session')
        );

        return response()->json($result);
    }

    /**
     * Enviar mensagens em massa
     *
     * @param Request $request
     * @return Response
     */
    public function sendBulk(Request $request)
    {
        $request->validate([
            'messages' => 'required|array',
            'session' => 'nullable|string',
        ]);

        $results = [];
        $session = $request->input('session');

        foreach ($request->input('messages') as $to => $message) {
            $results[$to] = $this->whatsapp->sendText($to, $message, $session);
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Listar sessões
     *
     * @return Response
     */
    public function sessions()
    {
        $sessions = $this->whatsapp->getSessions();
        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Verificar status
     *
     * @param Request $request
     * @return Response
     */
    public function status(Request $request)
    {
        $session = $request->input('session');
        $status = $this->whatsapp->getStatus($session);
        return response()->json(['status' => $status]);
    }

    /**
     * Iniciar sessão
     *
     * @param Request $request
     * @param string $session
     * @return Response
     */
    public function startSession(Request $request, $session)
    {
        $result = $this->whatsapp->startSession($session);
        return response()->json(['success' => $result]);
    }

    /**
     * Parar sessão
     *
     * @param Request $request
     * @param string $session
     * @return Response
     */
    public function stopSession(Request $request, $session)
    {
        $result = $this->whatsapp->stopSession($session);
        return response()->json(['success' => $result]);
    }

    /**
     * Excluir sessão
     *
     * @param Request $request
     * @param string $session
     * @return Response
     */
    public function deleteSession(Request $request, $session)
    {
        $result = $this->whatsapp->deleteSession($session);
        return response()->json(['success' => $result]);
    }
} 