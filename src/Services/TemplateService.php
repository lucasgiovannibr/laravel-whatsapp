<?php

namespace LucasGiovanni\LaravelWhatsApp\Services;

use LucasGiovanni\LaravelWhatsApp\Exceptions\WhatsAppException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;

class TemplateService
{
    /**
     * @var Handlebars
     */
    protected $handlebars;

    /**
     * @var string
     */
    protected $templatesPath;

    /**
     * @var bool
     */
    protected $cacheTemplates;

    /**
     * @var int
     */
    protected $cacheTtl = 1440; // 24 horas em minutos

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->templatesPath = config('whatsapp.templates_path', resource_path('views/whatsapp/templates'));
        $this->cacheTemplates = config('whatsapp.cache_templates', true);

        // Criar diretório de templates se não existir
        if (!File::exists($this->templatesPath)) {
            File::makeDirectory($this->templatesPath, 0755, true);
        }

        // Inicializar Handlebars
        $this->initHandlebars();
    }

    /**
     * Inicializar o motor de templates Handlebars
     *
     * @return void
     */
    protected function initHandlebars(): void
    {
        // Configurar loader para o diretório de templates
        $loader = new FilesystemLoader(
            $this->templatesPath,
            ['extension' => 'hbs']
        );

        // Inicializar Handlebars
        $this->handlebars = new Handlebars([
            'loader' => $loader,
            'partials_loader' => $loader,
        ]);

        // Registrar helpers personalizados
        $this->registerHelpers();
    }

    /**
     * Registrar helpers para o Handlebars
     *
     * @return void
     */
    protected function registerHelpers(): void
    {
        // Helper para formatação de data
        $this->handlebars->addHelper('formatDate', function($context, $options) {
            if (empty($context)) {
                return '';
            }

            $format = $options['format'] ?? 'd/m/Y H:i';
            $date = is_string($context) ? new \DateTime($context) : $context;

            return $date->format($format);
        });

        // Helper para formatação de moeda
        $this->handlebars->addHelper('formatMoney', function($context, $options) {
            if (empty($context)) {
                return 'R$ 0,00';
            }

            $decimals = $options['decimals'] ?? 2;
            $decPoint = $options['dec_point'] ?? ',';
            $thousandsSep = $options['thousands_sep'] ?? '.';

            return 'R$ ' . number_format((float)$context, $decimals, $decPoint, $thousandsSep);
        });

        // Helper para condicionais if/else
        $this->handlebars->addHelper('ifCond', function($context, $options) {
            $operator = $options['operator'];
            $val = $options['value'];
            
            switch($operator) {
                case '==':
                    return ($context == $val) ? $options->fn() : $options->inverse();
                case '===':
                    return ($context === $val) ? $options->fn() : $options->inverse();
                case '!=':
                    return ($context != $val) ? $options->fn() : $options->inverse();
                case '!==':
                    return ($context !== $val) ? $options->fn() : $options->inverse();
                case '<':
                    return ($context < $val) ? $options->fn() : $options->inverse();
                case '<=':
                    return ($context <= $val) ? $options->fn() : $options->inverse();
                case '>':
                    return ($context > $val) ? $options->fn() : $options->inverse();
                case '>=':
                    return ($context >= $val) ? $options->fn() : $options->inverse();
                default:
                    return $options->inverse();
            }
        });
    }

    /**
     * Renderizar um template
     *
     * @param string $templateName Nome do template (sem extensão)
     * @param array $data Dados para renderizar
     * @return string Template renderizado
     * @throws WhatsAppException Se o template não existir
     */
    public function renderTemplate(string $templateName, array $data = []): string
    {
        $cacheKey = "whatsapp_template:{$templateName}";

        try {
            // Verificar se o template existe
            if (!$this->templateExists($templateName)) {
                throw new WhatsAppException("Template '{$templateName}' não encontrado");
            }

            // Usar cache se habilitado
            if ($this->cacheTemplates) {
                return Cache::remember($cacheKey, $this->cacheTtl, function() use ($templateName, $data) {
                    return $this->handlebars->render($templateName, $data);
                });
            }

            // Renderizar template
            return $this->handlebars->render($templateName, $data);
        } catch (\Exception $e) {
            if ($e instanceof WhatsAppException) {
                throw $e;
            }
            
            throw new WhatsAppException("Erro ao renderizar template: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Verificar se um template existe
     *
     * @param string $templateName Nome do template (sem extensão)
     * @return bool
     */
    public function templateExists(string $templateName): bool
    {
        $templateFile = "{$this->templatesPath}/{$templateName}.hbs";
        return File::exists($templateFile);
    }

    /**
     * Obter todos os templates disponíveis
     *
     * @return array Lista de templates
     */
    public function getTemplates(): array
    {
        $files = File::files($this->templatesPath);
        $templates = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'hbs') {
                $templates[] = $file->getFilenameWithoutExtension();
            }
        }

        return $templates;
    }

    /**
     * Salvar um template
     *
     * @param string $templateName Nome do template (sem extensão)
     * @param string $content Conteúdo do template
     * @return bool
     */
    public function saveTemplate(string $templateName, string $content): bool
    {
        $templateFile = "{$this->templatesPath}/{$templateName}.hbs";
        
        // Limpar o cache para este template
        if ($this->cacheTemplates) {
            $cacheKey = "whatsapp_template:{$templateName}";
            Cache::forget($cacheKey);
        }
        
        return (bool) File::put($templateFile, $content);
    }

    /**
     * Excluir um template
     *
     * @param string $templateName Nome do template
     * @return bool
     */
    public function deleteTemplate(string $templateName): bool
    {
        $templateFile = "{$this->templatesPath}/{$templateName}.hbs";
        
        // Verificar se o template existe
        if (!File::exists($templateFile)) {
            return false;
        }
        
        // Limpar o cache para este template
        if ($this->cacheTemplates) {
            $cacheKey = "whatsapp_template:{$templateName}";
            Cache::forget($cacheKey);
        }
        
        return File::delete($templateFile);
    }

    /**
     * Carregar o conteúdo de um template
     *
     * @param string $templateName Nome do template
     * @return string|null
     */
    public function getTemplateContent(string $templateName): ?string
    {
        $templateFile = "{$this->templatesPath}/{$templateName}.hbs";
        
        if (!File::exists($templateFile)) {
            return null;
        }
        
        return File::get($templateFile);
    }
} 