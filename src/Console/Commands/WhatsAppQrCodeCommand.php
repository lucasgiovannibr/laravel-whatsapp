<?php

namespace LucasGiovanni\LaravelWhatsApp\Console\Commands;

use LucasGiovanni\LaravelWhatsApp\Contracts\WhatsAppClient;
use Illuminate\Console\Command;

class WhatsAppQrCodeCommand extends Command
{
    /**
     * O nome e assinatura do comando de console.
     */
    protected $signature = 'whatsapp:qr {session=default : Nome da sessão}';

    /**
     * A descrição do comando de console.
     */
    protected $description = 'Gerar e exibir QR Code para uma sessão do WhatsApp';

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
        $session = $this->argument('session');
        
        $this->info("Obtendo QR Code para a sessão '{$session}'...");

        try {
            $qrCode = $this->whatsapp->getQrCode($session);
            
            if (!$qrCode) {
                $this->error("Não foi possível obter o QR Code para a sessão '{$session}'.");
                return self::FAILURE;
            }

            $this->info("Escaneie o QR Code a seguir com o WhatsApp no seu celular:");
            $this->displayQrCode($qrCode);
            
            $this->info("Aguardando conexão...");
            
            // Aguardar e verificar a conexão a cada 5 segundos por 60 segundos
            $timeout = config('whatsapp.qr_timeout', 60);
            $start = time();
            
            while (time() - $start < $timeout) {
                $status = $this->whatsapp->getStatus($session);
                
                if ($status === 'connected') {
                    $this->info("Conectado com sucesso!");
                    return self::SUCCESS;
                }
                
                sleep(5);
            }
            
            $this->warn("Tempo limite excedido. Verifique o status da sessão usando 'whatsapp:sessions'.");
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("Erro ao gerar QR Code: " . $e->getMessage());
            return self::FAILURE;
        }
    }
    
    /**
     * Exibir o QR Code no terminal
     */
    protected function displayQrCode(string $qrCode): void
    {
        // Implementação simples para exibir QR Code no terminal
        // Para uma implementação completa, use uma biblioteca como endroid/qr-code
        
        $this->line("\n" . $qrCode);
    }
} 