<?php

namespace DesterroShop\LaravelWhatsApp\Traits;

use DesterroShop\LaravelWhatsApp\Facades\WhatsApp;
use Illuminate\Support\Facades\Log;

trait HasWhatsApp
{
    /**
     * Rota de notificação para WhatsApp
     *
     * @return string
     */
    public function routeNotificationForWhatsApp()
    {
        if (method_exists($this, 'getWhatsAppNumber')) {
            return $this->getWhatsAppNumber();
        }

        if (isset($this->whatsapp) && !empty($this->whatsapp)) {
            return $this->whatsapp;
        }

        if (isset($this->phone) && !empty($this->phone)) {
            return $this->phone;
        }

        if (isset($this->mobile) && !empty($this->mobile)) {
            return $this->mobile;
        }

        return null;
    }

    /**
     * Enviar mensagem de texto via WhatsApp
     *
     * @param string $message
     * @param array $options
     * @param string|null $session
     * @return mixed
     */
    public function sendWhatsApp(string $message, array $options = [], ?string $session = null)
    {
        $number = $this->routeNotificationForWhatsApp();
        
        if (empty($number)) {
            throw new \Exception('Número de WhatsApp não encontrado');
        }
        
        return WhatsApp::sendText($number, $message, $options, $session);
    }

    /**
     * Enviar template via WhatsApp
     *
     * @param string $template
     * @param array $data
     * @param string|null $session
     * @return mixed
     */
    public function sendWhatsAppTemplate(string $template, array $data = [], ?string $session = null)
    {
        $number = $this->routeNotificationForWhatsApp();
        
        if (empty($number)) {
            throw new \Exception('Número de WhatsApp não encontrado');
        }
        
        return WhatsApp::sendTemplate($number, $template, $data, $session);
    }
    
    /**
     * Enviar imagem para este modelo
     *
     * @param string $url
     * @param string|null $caption
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppImage(string $url, ?string $caption = null, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar imagem WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendImage($to, $url, $caption, $session);
    }
    
    /**
     * Enviar arquivo/documento para este modelo
     *
     * @param string $url
     * @param string|null $filename
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppFile(string $url, ?string $filename = null, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar arquivo WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendFile($to, $url, $filename, $session);
    }
    
    /**
     * Enviar áudio para este modelo
     *
     * @param string $url
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppAudio(string $url, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar áudio WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendAudio($to, $url, $session);
    }
    
    /**
     * Enviar vídeo para este modelo
     *
     * @param string $url
     * @param string|null $caption
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppVideo(string $url, ?string $caption = null, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar vídeo WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendVideo($to, $url, $caption, $session);
    }
    
    /**
     * Enviar mídia genérica para este modelo
     *
     * @param string $url
     * @param string $type
     * @param string|null $caption
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppMedia(string $url, string $type, ?string $caption = null, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar mídia WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
                'type' => $type
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendMedia($to, $url, $type, $caption, $session);
    }
    
    /**
     * Enviar localização para este modelo
     *
     * @param float $latitude
     * @param float $longitude
     * @param string|null $title
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppLocation(float $latitude, float $longitude, ?string $title = null, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar localização WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendLocation($to, $latitude, $longitude, $title, $session);
    }
    
    /**
     * Enviar contato para este modelo
     *
     * @param array $contact
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppContact(array $contact, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar contato WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendContact($to, $contact, $session);
    }
    
    /**
     * Enviar mensagem com botões para este modelo
     *
     * @param string $text
     * @param array $buttons
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppButtons(string $text, array $buttons, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar botões WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendButtons($to, $text, $buttons, $session);
    }
    
    /**
     * Enviar lista de opções para este modelo
     *
     * @param string $title
     * @param string $description
     * @param string $buttonText
     * @param array $sections
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppList(string $title, string $description, string $buttonText, array $sections, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar lista WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendList($to, $title, $description, $buttonText, $sections, $session);
    }
    
    /**
     * Enviar enquete para este modelo
     *
     * @param string $question
     * @param array $options
     * @param bool $multiSelect
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppPoll(string $question, array $options, bool $multiSelect = false, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar enquete WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendPoll($to, $question, $options, $multiSelect, $session);
    }
    
    /**
     * Enviar produto para este modelo
     *
     * @param string $catalogId
     * @param string $productId
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppProduct(string $catalogId, string $productId, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar produto WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendProduct($to, $catalogId, $productId, $session);
    }
    
    /**
     * Enviar catálogo de produtos para este modelo
     *
     * @param string $catalogId
     * @param array|null $productIds
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppCatalog(string $catalogId, ?array $productIds = null, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar catálogo WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendCatalog($to, $catalogId, $productIds, $session);
    }
    
    /**
     * Enviar pedido para este modelo
     *
     * @param array $orderData
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppOrder(array $orderData, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar pedido WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendOrder($to, $orderData, $session);
    }
    
    /**
     * Enviar sticker para este modelo
     *
     * @param string $url
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppSticker(string $url, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar sticker WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendSticker($to, $url, $session);
    }
    
    /**
     * Enviar reação a uma mensagem para este modelo
     *
     * @param string $messageId
     * @param string $emoji
     * @param string|null $session
     * @return array
     */
    public function sendWhatsAppReaction(string $messageId, string $emoji, ?string $session = null): array
    {
        $to = $this->routeNotificationForWhatsApp();
        
        if (empty($to)) {
            Log::error('Não foi possível enviar reação WhatsApp: número não definido', [
                'model' => get_class($this),
                'id' => $this->getKey(),
                'messageId' => $messageId,
                'emoji' => $emoji
            ]);
            
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::sendReaction($to, $messageId, $emoji, $session);
    }
    
    /**
     * Obter histórico de mensagens WhatsApp com este modelo
     *
     * @param int $limit
     * @param string|null $sessionId
     * @return array
     */
    public function getWhatsAppMessages(int $limit = 50, ?string $sessionId = null): array
    {
        $phone = $this->routeNotificationFor('whatsapp');
        
        if (empty($phone)) {
            return [
                'success' => false,
                'error' => 'Número de WhatsApp não definido no modelo'
            ];
        }
        
        return WhatsApp::getMessages($phone, $limit, $sessionId);
    }
} 