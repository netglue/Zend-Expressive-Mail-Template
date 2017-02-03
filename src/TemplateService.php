<?php

namespace NetglueMail;

use Zend\Expressive\Template\TemplateRendererInterface;

class TemplateService
{

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * @var ModuleOptions
     */
    private $options;

    /**
     * Construct
     * @param ModuleOptions             $options
     * @param TemplateRendererInterface $renderer
     */
    public function __construct(ModuleOptions $options, TemplateRendererInterface $renderer)
    {
        $this->options  = $options;
        $this->renderer = $renderer;
    }

    /**
     * Render the given message type with the given view model
     * @param string $messageName
     * @param array  $viewModel
     * @return string
     * @throws Exception\UnknownTemplateException if no template has been set
     */
    public function renderTemplate(string $messageName, array $viewModel = null)
    {
        $viewModel = (null === $viewModel) ? [] : $viewModel;
        $tmpl = $this->getTemplateByName($messageName);
        if (null === $tmpl) {
            return null;
        }
        if ($layout = $this->getLayoutByName($messageName)) {
            $viewModel['layout'] = $layout;
        }
        return $this->renderer->render($tmpl, $viewModel);
    }

    /**
     * Render a plain-text template with the given name and model
     * @param string $messageName
     * @param array  $viewModel
     * @return string
     * @throws Exception\UnknownTemplateException if no template has been set
     */
    public function renderTextTemplate(string $messageName, array $viewModel = null)
    {
        $viewModel = (null === $viewModel) ? [] : $viewModel;
        $tmpl = $this->getTextTemplateByName($messageName);
        if (null === $tmpl) {
            return null;
        }
        if ($layout = $this->getTextLayoutByName($messageName)) {
            $viewModel['layout'] = $layout;
        }
        return $this->renderer->render($tmpl, $viewModel);
    }

    /**
     * Return the layout template that should be used
     * @param string $messageName
     * @return string|null
     */
    public function getLayoutByName(string $messageName)
    {
        $layout = $this->options->getDefaultLayout();
        return $this->options->getMessageOption($messageName, 'layout', $layout);
    }

    /**
     * Return the layout template that should be used
     * @param string $messageName
     * @return string|null
     */
    public function getTextLayoutByName(string $messageName)
    {
        $layout = $this->options->getTextLayout();
        return $this->options->getMessageOption($messageName, 'textLayout', $layout);
    }

    /**
     * Return template name for specific message
     * @param string $messageName
     * @return string|null
     */
    public function getTemplateByName(string $messageName)
    {
        return $this->options->getMessageOption($messageName, 'template');
    }


    /**
     * Return text template name for specific message
     * @param string $messageName
     * @return string|null
     */
    public function getTextTemplateByName(string $messageName)
    {
        return $this->options->getMessageOption($messageName, 'textTemplate');
    }

    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return TemplateRendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }


}
