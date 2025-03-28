<?php

namespace LucasGiovanni\LaravelWhatsApp\Console\Commands;

use LucasGiovanni\LaravelWhatsApp\Contracts\WhatsAppClient;
use Illuminate\Console\Command;

class WhatsAppQrCodeCommand extends Command
{
    /**
     * O nome e assinatura do comando de console.
     *
     * @var string
     */
    protected $signature = 'whatsapp:qr {session=default : Nome da sessão}';

    /**
     * A descrição do comando de console.
     *
     * @var string
     */
    protected $description = 'Gerar e exibir QR Code para uma sessão do WhatsApp';

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
        $session = $this->argument('session');
        
        $this->info("Obtendo QR Code para a sessão '{$session}'...");

        try {
            $qrCode = $this->whatsapp->getQrCode($session);
            
            if (!$qrCode) {
                $this->error("Não foi possível obter o QR Code para a sessão '{$session}'.");
                return 1;
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
                    return 0;
                }
                
                sleep(5);
            }
            
            $this->warn("Tempo limite excedido. Verifique o status da sessão usando 'whatsapp:sessions'.");
            return 1;
        } catch (\Exception $e) {
            $this->error("Erro ao gerar QR Code: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Exibir o QR Code no terminal
     *
     * @param string $qrCode
     * @return void
     */
    protected function displayQrCode(string $qrCode)
    {
        // Implementação simples para exibir QR Code no terminal
        // Para uma implementação completa, use uma biblioteca como endroid/qr-code
        
        $this->line("\n" . $qrCode);
    }
} 