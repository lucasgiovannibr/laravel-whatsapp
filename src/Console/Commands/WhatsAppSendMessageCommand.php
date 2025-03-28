<?php

namespace LucasGiovanni\LaravelWhatsApp\Console\Commands;

use LucasGiovanni\LaravelWhatsApp\Contracts\WhatsAppClient;
use Illuminate\Console\Command;

class WhatsAppSendMessageCommand extends Command
{
    /**
     * O nome e assinatura do comando de console.
     */
    protected $signature = 'whatsapp:send {phone : Número do telefone} {message : Mensagem a ser enviada} {--session=default : Nome da sessão}';

    /**
     * A descrição do comando de console.
     */
    protected $description = 'Enviar mensagem via WhatsApp';

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
        $phone = $this->argument('phone');
        $message = $this->argument('message');
        $session = $this->option('session');

        $this->info("Enviando mensagem para {$phone}...");

        try {
            $result = $this->whatsapp->sendMessage($session, $phone, $message);
            
            if ($result) {
                $this->info("Mensagem enviada com sucesso!");
                return self::SUCCESS;
            }

            $this->error("Não foi possível enviar a mensagem.");
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("Erro ao enviar mensagem: " . $e->getMessage());
            return self::FAILURE;
        }
    }
} 