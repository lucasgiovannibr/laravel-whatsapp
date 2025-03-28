<?php

namespace LucasGiovanni\LaravelWhatsApp\Console\Commands;

use LucasGiovanni\LaravelWhatsApp\Contracts\WhatsAppClient;
use Illuminate\Console\Command;

class WhatsAppSendMessageCommand extends Command
{
    /**
     * O nome e assinatura do comando de console.
     *
     * @var string
     */
    protected $signature = 'whatsapp:send 
                            {to : Número para envio no formato internacional (ex: 5548999998888)} 
                            {message : Mensagem a ser enviada} 
                            {--session= : Nome da sessão (opcional)}';

    /**
     * A descrição do comando de console.
     *
     * @var string
     */
    protected $description = 'Enviar mensagem via WhatsApp';

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
        $to = $this->argument('to');
        $message = $this->argument('message');
        $session = $this->option('session');

        $this->info("Enviando mensagem para {$to}...");

        try {
            $result = $this->whatsapp->sendText($to, $message, $session);
            
            if ($result) {
                $this->info("Mensagem enviada com sucesso!");
                return 0;
            } else {
                $this->error("Não foi possível enviar a mensagem.");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Erro ao enviar mensagem: " . $e->getMessage());
            return 1;
        }
    }
} 