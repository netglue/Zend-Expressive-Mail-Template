<?php
declare(strict_types=1);

namespace NetglueMail;

use Zend\Expressive\Template\TemplateRendererInterface;

class TemplateService
{

    /**
     *
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     *
     * @var ModuleOptions
     */
    private $options;

    public function __construct(ModuleOptions $options, TemplateRendererInterface $renderer)
    {
        $this->options  = $options;
        $this->renderer = $renderer;
    }

    public function renderTemplate(string $messageName, ?array $viewModel = null) :? string
    {
        $viewModel = (null === $viewModel) ? [] : $viewModel;
        $template  = $this->getTemplateByName($messageName);
        if (! $template) {
            return null;
        }
        $layout = $this->getLayoutByName($messageName);
        if (! $layout) {
            $layout = $this->options->getEmptyLayoutTemplate();
        }
        $viewModel['layout'] = $layout;
        return $this->renderer->render($template, $viewModel);
    }

    public function renderTextTemplate(string $messageName, ?array $viewModel = null) :? string
    {
        $viewModel = (null === $viewModel) ? [] : $viewModel;
        $template  = $this->getTextTemplateByName($messageName);
        if (! $template) {
            return null;
        }
        $layout = $this->getTextLayoutByName($messageName);
        if (! $layout) {
            $layout = $this->options->getEmptyLayoutTemplate();
        }
        $viewModel['layout'] = $layout;
        return $this->renderer->render($template, $viewModel);
    }

    /**
     * Return the layout template that should be used
     *
     * @param  string $messageName
     * @return string|null
     */
    public function getLayoutByName(string $messageName) :? string
    {
        $layout = $this->options->getDefaultLayout();
        $layout = (string) $this->options->getMessageOption($messageName, 'layout', $layout);
        return empty($layout) ? null : $layout;
    }

    /**
     * Return the layout template that should be used
     *
     * @param  string $messageName
     * @return string|null
     */
    public function getTextLayoutByName(string $messageName) :? string
    {
        $layout = $this->options->getTextLayout();
        $layout = (string) $this->options->getMessageOption($messageName, 'textLayout', $layout);
        return empty($layout) ? null : $layout;
    }

    /**
     * Return template name for specific message
     *
     * @param  string $messageName
     * @return string|null
     */
    public function getTemplateByName(string $messageName) :? string
    {
        $template = (string) $this->options->getMessageOption($messageName, 'template');
        return empty($template) ? null : $template;
    }

    /**
     * Return text template name for specific message
     *
     * @param  string $messageName
     * @return string|null
     */
    public function getTextTemplateByName(string $messageName) :? string
    {
        $template = (string) $this->options->getMessageOption($messageName, 'textTemplate');
        return empty($template) ? null : $template;
    }
}
