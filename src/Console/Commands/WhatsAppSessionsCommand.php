<?php

namespace LucasGiovanni\LaravelWhatsApp\Console\Commands;

use LucasGiovanni\LaravelWhatsApp\Contracts\WhatsAppClient;
use Illuminate\Console\Command;

class WhatsAppSessionsCommand extends Command
{
    /**
     * O nome e assinatura do comando de console.
     */
    protected $signature = 'whatsapp:sessions {--session= : Nome da sessão específica}';

    /**
     * A descrição do comando de console.
     */
    protected $description = 'Listar ou gerenciar sessões do WhatsApp';

    /**
     * Cliente WhatsApp
     */
    protected WhatsAppClient $whatsapp;

    /**
     * Criar uma nova instância do comando.
     */
    public function __construct(WhatsAppClient $whatsapp)
    {
        parent::__construct();
        $this->whatsapp = $whatsapp;
    }

    /**
     * Executar o comando de console.
     */
    public function handle(): int
    {
        $session = $this->option('session');

        if ($session) {
            return $this->showSessionStatus($session);
        }

        return $this->listAllSessions();
    }

    /**
     * Mostrar status de uma sessão específica
     */
    protected function showSessionStatus(string $session): int
    {
        try {
            $status = $this->whatsapp->getStatus($session);
            
            $this->info("Status da sessão '{$session}':");
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Status', $status],
                    ['Conectado', $status === 'connected' ? 'Sim' : 'Não'],
                    ['Última Atividade', $this->whatsapp->getLastActivity($session)],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro ao obter status da sessão: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Listar todas as sessões
     */
    protected function listAllSessions(): int
    {
        try {
            $sessions = $this->whatsapp->getSessions();
            
            if (empty($sessions)) {
                $this->info("Nenhuma sessão encontrada.");
                return self::SUCCESS;
            }

            $this->info("Sessões disponíveis:");
            $this->table(
                ['Sessão', 'Status', 'Última Atividade'],
                collect($sessions)->map(function ($session) {
                    return [
                        $session['name'],
                        $session['status'],
                        $session['last_activity'],
                    ];
                })->toArray()
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro ao listar sessões: " . $e->getMessage());
            return self::FAILURE;
        }
    }
} 