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
     * Enviar mensagem via WhatsApp
     *
     * @param string $message
     * @param string|null $session
     * @return mixed
     */
    public function sendWhatsApp(string $message, ?string $session = null)
    {
        $number = $this->routeNotificationForWhatsApp();
        
        if (empty($number)) {
            throw new \Exception('Número de WhatsApp não encontrado');
        }
        
        return WhatsApp::sendText($number, $message, $session);
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
     * @param string $imageUrl
     * @param string|null $caption
     * @param string|null $sessionId
     * @return array
     */
    public function sendWhatsAppImage(string $imageUrl, ?string $caption = null, ?string $sessionId = null): array
    {
        $to = $this->routeNotificationFor('whatsapp');
        
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
        
        return WhatsApp::sendImage($to, $imageUrl, $caption, $sessionId);
    }
    
    /**
     * Enviar arquivo para este modelo
     *
     * @param string $fileUrl
     * @param string|null $filename
     * @param string|null $sessionId
     * @return array
     */
    public function sendWhatsAppFile(string $fileUrl, ?string $filename = null, ?string $sessionId = null): array
    {
        $to = $this->routeNotificationFor('whatsapp');
        
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
        
        return WhatsApp::sendFile($to, $fileUrl, $filename, $sessionId);
    }
    
    /**
     * Enviar localização para este modelo
     *
     * @param float $latitude
     * @param float $longitude
     * @param string|null $title
     * @param string|null $sessionId
     * @return array
     */
    public function sendWhatsAppLocation(float $latitude, float $longitude, ?string $title = null, ?string $sessionId = null): array
    {
        $to = $this->routeNotificationFor('whatsapp');
        
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
        
        return WhatsApp::sendLocation($to, $latitude, $longitude, $title, $sessionId);
    }
    
    /**
     * Enviar lista de opções para este modelo
     *
     * @param string $title
     * @param string $description
     * @param string $buttonText
     * @param array $sections
     * @param string|null $sessionId
     * @return array
     */
    public function sendWhatsAppList(string $title, string $description, string $buttonText, array $sections, ?string $sessionId = null): array
    {
        $to = $this->routeNotificationFor('whatsapp');
        
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
        
        return WhatsApp::sendList($to, $title, $description, $buttonText, $sections, $sessionId);
    }
    
    /**
     * Enviar enquete para este modelo
     *
     * @param string $question
     * @param array $options
     * @param bool $isMultiSelect
     * @param string|null $sessionId
     * @return array
     */
    public function sendWhatsAppPoll(string $question, array $options, bool $isMultiSelect = false, ?string $sessionId = null): array
    {
        $to = $this->routeNotificationFor('whatsapp');
        
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
        
        return WhatsApp::sendPoll($to, $question, $options, $isMultiSelect, $sessionId);
    }
    
    /**
     * Enviar produto para este modelo
     *
     * @param string $catalogId
     * @param string $productId
     * @param string|null $sessionId
     * @return array
     */
    public function sendWhatsAppProduct(string $catalogId, string $productId, ?string $sessionId = null): array
    {
        $to = $this->routeNotificationFor('whatsapp');
        
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
        
        return WhatsApp::sendProduct($to, $catalogId, $productId, $sessionId);
    }
    
    /**
     * Enviar catálogo de produtos para este modelo
     *
     * @param string $catalogId
     * @param array $productItems
     * @param string|null $sessionId
     * @return array
     */
    public function sendWhatsAppCatalog(string $catalogId, array $productItems = [], ?string $sessionId = null): array
    {
        $to = $this->routeNotificationFor('whatsapp');
        
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
        
        return WhatsApp::sendCatalog($to, $catalogId, $productItems, $sessionId);
    }
    
    /**
     * Enviar pedido para este modelo
     *
     * @param array $orderData
     * @param string|null $sessionId
     * @return array
     */
    public function sendWhatsAppOrder(array $orderData, ?string $sessionId = null): array
    {
        $to = $this->routeNotificationFor('whatsapp');
        
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
        
        return WhatsApp::sendOrder($to, $orderData, $sessionId);
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