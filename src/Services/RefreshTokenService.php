<?php

namespace LucasGiovanni\LaravelWhatsApp\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RefreshTokenService
{
    /**
     * @var \LucasGiovanni\LaravelWhatsApp\Services\WhatsAppService
     */
    protected $whatsappService;
    
    /**
     * Construtor
     *
     * @param \LucasGiovanni\LaravelWhatsApp\Services\WhatsAppService $whatsappService
     */
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }
    
    /**
     * Gerar um novo token de acesso
     *
     * @param string $userId ID do usuário
     * @param array $scopes Escopos de permissão
     * @param int $expiresInMinutes Tempo de expiração em minutos
     * @return array Tokens gerados (access_token, refresh_token)
     */
    public function generateToken(string $userId, array $scopes = ['*'], int $expiresInMinutes = 60): array
    {
        // Gerar token de acesso
        $accessToken = Str::random(64);
        $refreshToken = Str::random(80);
        
        $expiresAt = Carbon::now()->addMinutes($expiresInMinutes);
        
        // Armazenar no cache
        $tokenData = [
            'user_id' => $userId,
            'scopes' => $scopes,
            'expires_at' => $expiresAt->toIso8601String(),
            'refresh_token' => $refreshToken,
        ];
        
        // Armazenar token no cache
        Cache::put("whatsapp_token_{$accessToken}", $tokenData, $expiresInMinutes * 60);
        
        // Armazenar refresh token por um período mais longo (7 dias)
        Cache::put("whatsapp_refresh_{$refreshToken}", [
            'user_id' => $userId,
            'scopes' => $scopes,
            'created_at' => Carbon::now()->toIso8601String(),
        ], Carbon::now()->addDays(7));
        
        Log::info("Novo token gerado para usuário {$userId}", [
            'user_id' => $userId,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
        
        // Registrar o token na API Node.js
        try {
            $this->whatsappService->registerToken($accessToken, [
                'user_id' => $userId,
                'scopes' => $scopes,
                'expires_at' => $expiresAt->timestamp,
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao registrar token na API Node.js: {$e->getMessage()}", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $expiresInMinutes * 60,
            'expires_at' => $expiresAt->toIso8601String(),
            'scopes' => $scopes,
        ];
    }
    
    /**
     * Renovar token usando refresh token
     *
     * @param string $refreshToken Refresh token
     * @param int $expiresInMinutes Tempo de expiração em minutos
     * @return array|null Novos tokens ou null se o refresh token for inválido
     */
    public function refreshToken(string $refreshToken, int $expiresInMinutes = 60): ?array
    {
        $refreshData = Cache::get("whatsapp_refresh_{$refreshToken}");
        
        if (!$refreshData) {
            Log::warning("Tentativa de uso de refresh token inválido", [
                'refresh_token' => substr($refreshToken, 0, 10) . '...',
            ]);
            return null;
        }
        
        // Gerar novo token
        $userId = $refreshData['user_id'];
        $scopes = $refreshData['scopes'];
        
        // Invalidar refresh token atual
        Cache::forget("whatsapp_refresh_{$refreshToken}");
        
        // Gerar novo par de tokens
        return $this->generateToken($userId, $scopes, $expiresInMinutes);
    }
    
    /**
     * Validar token de acesso
     *
     * @param string $accessToken Token de acesso
     * @return array|null Dados do token ou null se inválido
     */
    public function validateToken(string $accessToken): ?array
    {
        $tokenData = Cache::get("whatsapp_token_{$accessToken}");
        
        if (!$tokenData) {
            return null;
        }
        
        // Verificar expiração
        $expiresAt = Carbon::parse($tokenData['expires_at']);
        if ($expiresAt->isPast()) {
            Cache::forget("whatsapp_token_{$accessToken}");
            return null;
        }
        
        return [
            'valid' => true,
            'user_id' => $tokenData['user_id'],
            'scopes' => $tokenData['scopes'],
            'expires_at' => $tokenData['expires_at'],
        ];
    }
    
    /**
     * Revogar tokens de um usuário
     *
     * @param string $userId ID do usuário
     * @param string|null $accessToken Token específico ou null para todos os tokens
     * @return bool Sucesso
     */
    public function revokeTokens(string $userId, ?string $accessToken = null): bool
    {
        try {
            if ($accessToken) {
                // Revogar token específico
                $tokenData = Cache::get("whatsapp_token_{$accessToken}");
                
                if ($tokenData && $tokenData['user_id'] === $userId) {
                    Cache::forget("whatsapp_token_{$accessToken}");
                    
                    // Revogar o refresh token associado
                    if (isset($tokenData['refresh_token'])) {
                        Cache::forget("whatsapp_refresh_{$tokenData['refresh_token']}");
                    }
                    
                    // Notificar a API Node.js
                    $this->whatsappService->revokeToken($accessToken);
                }
            } else {
                // Revogar todos os tokens do usuário (via API Node.js)
                $this->whatsappService->revokeAllTokens($userId);
            }
            
            Log::info("Tokens revogados para usuário {$userId}", [
                'user_id' => $userId,
                'access_token' => $accessToken ? substr($accessToken, 0, 10) . '...' : 'all',
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao revogar tokens: {$e->getMessage()}", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Verificar se token tem um determinado escopo
     *
     * @param string $accessToken Token de acesso
     * @param string $scope Escopo a verificar
     * @return bool Se o token tem o escopo
     */
    public function tokenHasScope(string $accessToken, string $scope): bool
    {
        $tokenData = $this->validateToken($accessToken);
        
        if (!$tokenData) {
            return false;
        }
        
        return in_array('*', $tokenData['scopes']) || in_array($scope, $tokenData['scopes']);
    }
    
    /**
     * Limpar tokens expirados
     *
     * @return int Número de tokens removidos
     */
    public function purgeExpiredTokens(): int
    {
        // Nota: A implementação real dependeria de como você está armazenando os tokens
        // Esta é apenas uma implementação de exemplo
        
        Log::info("Limpeza de tokens expirados realizada");
        
        return 0; // No sistema baseado em cache, os tokens expiram automaticamente
    }
} 