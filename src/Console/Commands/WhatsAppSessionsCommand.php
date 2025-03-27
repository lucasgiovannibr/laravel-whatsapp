<?php

namespace DesterroShop\LaravelWhatsApp\Console\Commands;

use DesterroShop\LaravelWhatsApp\Facades\WhatsApp;
use Illuminate\Console\Command;

class WhatsAppSessionsCommand extends Command
{
    /**
     * Nome e assinatura do comando console.
     *
     * @var string
     */
    protected $signature = 'whatsapp:sessions
                            {action? : Ação a executar (list, create, delete, status)}
                            {id? : ID da sessão (para create/delete/status)}';

    /**
     * Descrição do comando console.
     *
     * @var string
     */
    protected $description = 'Gerenciar sessões do WhatsApp';

    /**
     * Executar o comando console.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action') ?? 'list';
        $sessionId = $this->argument('id');

        switch ($action) {
            case 'list':
                return $this->listSessions();
            case 'create':
                return $this->createSession($sessionId);
            case 'delete':
                return $this->deleteSession($sessionId);
            case 'status':
                return $this->sessionStatus($sessionId);
            default:
                $this->error("Ação desconhecida: {$action}");
                return 1;
        }
    }

    /**
     * Listar todas as sessões.
     *
     * @return int
     */
    protected function listSessions()
    {
        $this->info('Buscando sessões ativas...');
        
        try {
            $response = WhatsApp::getSessions();
            
            if (!isset($response['success']) || !$response['success']) {
                $this->error('Erro ao buscar sessões: ' . ($response['error'] ?? 'Erro desconhecido'));
                return 1;
            }
            
            $sessions = $response['sessions'] ?? [];
            
            if (empty($sessions)) {
                $this->info('Nenhuma sessão ativa encontrada.');
                return 0;
            }
            
            // Preparar dados para tabela
            $tableData = [];
            foreach ($sessions as $session) {
                $tableData[] = [
                    'ID' => $session['id'] ?? 'N/A',
                    'Status' => $session['status'] ?? 'N/A',
                    'Connected' => isset($session['connected']) && $session['connected'] ? 'Sim' : 'Não',
                    'Created At' => $session['createdAt'] ?? 'N/A',
                ];
            }
            
            $this->table(['ID', 'Status', 'Connected', 'Created At'], $tableData);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao buscar sessões: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Criar uma nova sessão.
     *
     * @param string|null $sessionId
     * @return int
     */
    protected function createSession(?string $sessionId)
    {
        if (empty($sessionId)) {
            $sessionId = $this->ask('Digite o ID da sessão a ser criada');
        }
        
        if (empty($sessionId)) {
            $this->error('ID da sessão é obrigatório');
            return 1;
        }
        
        $this->info("Criando sessão '{$sessionId}'...");
        
        try {
            $response = WhatsApp::createSession($sessionId);
            
            if (!isset($response['success']) || !$response['success']) {
                $this->error('Erro ao criar sessão: ' . ($response['error'] ?? 'Erro desconhecido'));
                return 1;
            }
            
            $this->info("Sessão '{$sessionId}' criada com sucesso!");

            // Verificar se precisa escanear QR code
            if (isset($response['qrCode']) && $this->confirm('Deseja exibir o QR Code para escanear?')) {
                // Mostrar QR code CLI
                $this->displayQrCode($response['qrCode']);
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao criar sessão: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Excluir uma sessão existente.
     *
     * @param string|null $sessionId
     * @return int
     */
    protected function deleteSession(?string $sessionId)
    {
        if (empty($sessionId)) {
            $sessionId = $this->ask('Digite o ID da sessão a ser excluída');
        }
        
        if (empty($sessionId)) {
            $this->error('ID da sessão é obrigatório');
            return 1;
        }
        
        if (!$this->confirm("Tem certeza que deseja excluir a sessão '{$sessionId}'?")) {
            $this->info('Operação cancelada.');
            return 0;
        }
        
        $this->info("Excluindo sessão '{$sessionId}'...");
        
        try {
            $response = WhatsApp::deleteSession($sessionId);
            
            if (!isset($response['success']) || !$response['success']) {
                $this->error('Erro ao excluir sessão: ' . ($response['error'] ?? 'Erro desconhecido'));
                return 1;
            }
            
            $this->info("Sessão '{$sessionId}' excluída com sucesso!");
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao excluir sessão: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Verificar status de uma sessão.
     *
     * @param string|null $sessionId
     * @return int
     */
    protected function sessionStatus(?string $sessionId)
    {
        if (empty($sessionId)) {
            $sessionId = $this->ask('Digite o ID da sessão a ser verificada');
        }
        
        if (empty($sessionId)) {
            $this->error('ID da sessão é obrigatório');
            return 1;
        }
        
        $this->info("Verificando status da sessão '{$sessionId}'...");
        
        try {
            $response = WhatsApp::getSessionStatus($sessionId);
            
            if (!isset($response['success']) || !$response['success']) {
                $this->error('Erro ao verificar status da sessão: ' . ($response['error'] ?? 'Erro desconhecido'));
                return 1;
            }
            
            $status = $response['status'] ?? [];
            
            $this->info("Informações da sessão '{$sessionId}':");
            $this->table(['Propriedade', 'Valor'], $this->formatStatusData($status));
            
            // Verificar se precisa escanear QR code
            if (isset($status['qrCode']) && $this->confirm('Deseja exibir o QR Code para escanear?')) {
                $this->displayQrCode($status['qrCode']);
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao verificar status da sessão: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Formatar dados de status para exibição em tabela.
     *
     * @param array $status
     * @return array
     */
    protected function formatStatusData(array $status): array
    {
        $result = [];
        
        foreach ($status as $key => $value) {
            if ($key === 'qrCode') {
                $result[] = [$key, '[QR Code disponível]'];
                continue;
            }
            
            if (is_bool($value)) {
                $value = $value ? 'Sim' : 'Não';
            } elseif (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            
            $result[] = [$key, $value];
        }
        
        return $result;
    }

    /**
     * Exibir QR Code no terminal.
     *
     * @param string $qrCode
     * @return void
     */
    protected function displayQrCode(string $qrCode): void
    {
        $this->info('Escaneie o QR Code usando o WhatsApp no seu celular:');
        $this->line('');
        
        // Renderizar QR Code no terminal
        $this->line($qrCode);
        
        $this->line('');
        $this->info('O QR Code expira em ' . config('whatsapp.qr_timeout', 60) . ' segundos.');
        $this->line('Aguarde após escanear o código...');
    }
} 