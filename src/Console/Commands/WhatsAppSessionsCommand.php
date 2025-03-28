<?php

namespace LucasGiovanni\LaravelWhatsApp\Console\Commands;

use LucasGiovanni\LaravelWhatsApp\Contracts\WhatsAppClient;
use Illuminate\Console\Command;

class WhatsAppSessionsCommand extends Command
{
    /**
     * O nome e assinatura do comando de console.
     *
     * @var string
     */
    protected $signature = 'whatsapp:sessions {--start= : Inicia uma sessão específica} {--stop= : Para uma sessão específica}';

    /**
     * A descrição do comando de console.
     *
     * @var string
     */
    protected $description = 'Gerenciar sessões do WhatsApp';

    /**
     * Cliente WhatsApp
     *
     * @var WhatsAppClient
     */
    protected $whatsapp;

    /**
     * Criar uma nova instância do comando.
     *
     * @param WhatsAppClient $whatsapp
     * @return void
     */
    public function __construct(WhatsAppClient $whatsapp)
    {
        parent::__construct();
        $this->whatsapp = $whatsapp;
    }

    /**
     * Executar o comando de console.
     *
     * @return int
     */
    public function handle()
    {
        if ($sessionToStart = $this->option('start')) {
            return $this->startSession($sessionToStart);
        }

        if ($sessionToStop = $this->option('stop')) {
            return $this->stopSession($sessionToStop);
        }

        return $this->listSessions();
    }

    /**
     * Listar todas as sessões
     *
     * @return int
     */
    protected function listSessions()
    {
        $sessions = $this->whatsapp->getSessions();

        if (empty($sessions)) {
            $this->info('Nenhuma sessão encontrada.');
            return 0;
        }

        $rows = [];
        foreach ($sessions as $session) {
            $rows[] = [
                $session['name'] ?? 'N/A',
                $session['status'] ?? 'N/A',
                $session['qr'] ? 'Disponível' : 'Indisponível',
                $session['connected'] ? 'Sim' : 'Não'
            ];
        }

        $this->table(['Nome', 'Status', 'QR Code', 'Conectado'], $rows);
        return 0;
    }

    /**
     * Iniciar uma sessão
     *
     * @param string $session
     * @return int
     */
    protected function startSession(string $session)
    {
        $this->info("Iniciando sessão '{$session}'...");
        
        try {
            $result = $this->whatsapp->startSession($session);
            
            if ($result) {
                $this->info("Sessão '{$session}' iniciada com sucesso!");
                return 0;
            } else {
                $this->error("Não foi possível iniciar a sessão '{$session}'.");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Erro ao iniciar sessão: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Parar uma sessão
     *
     * @param string $session
     * @return int
     */
    protected function stopSession(string $session)
    {
        $this->info("Parando sessão '{$session}'...");
        
        try {
            $result = $this->whatsapp->stopSession($session);
            
            if ($result) {
                $this->info("Sessão '{$session}' parada com sucesso!");
                return 0;
            } else {
                $this->error("Não foi possível parar a sessão '{$session}'.");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Erro ao parar sessão: " . $e->getMessage());
            return 1;
        }
    }
} 