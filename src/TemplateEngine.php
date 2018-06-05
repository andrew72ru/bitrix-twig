<?php
/**
 * User: andrew
 * Date: 03/06/2018
 * Time: 12:22.
 */

namespace Creative\Twig;

use Creative\Twig\Exception\BitrixTwigException;
use Creative\Twig\Extensions\BitrixExtension;
use Creative\Twig\Extensions\PhpGlobals;
use Bitrix\Main\Event;
use Bitrix\Main\SystemException;

/**
 * Singleton for render twig templates.
 */
class TemplateEngine
{
    /**
     * @var null|self
     */
    private static $instance = null;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var null|\HttpRequest
     */
    private $request;

    /**
     * @var \Twig_Environment
     */
    private $engine;

    /**
     * TemplateEngine constructor.
     *
     * @throws BitrixTwigException
     */
    protected function __construct()
    {
        try {
            $this->request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        } catch (SystemException $e) {
            throw new BitrixTwigException($e->getMessage());
        }

        $config = (array) \Bitrix\Main\Config\Configuration::getInstance()->get('twigRenderer') ?: [];
        $this->options = array_merge($this->getDefaultOptions(), $config);

        $this->engine = new \Twig_Environment(new TwigLoader(), $this->options);
        $this->addExtensions();
        $this->generateInitEvent();

        self::$instance = $this;
    }

    /**
     * @param $templateFile
     * @param $arResult
     * @param $arParams
     * @param $arLangMessages
     * @param $templateFolder
     * @param $parentTemplateFolder
     * @param \CBitrixComponentTemplate $template
     *
     * @throws BitrixTwigException
     */
    public static function render($templateFile, $arResult, $arParams, $arLangMessages,
        $templateFolder, $parentTemplateFolder, \CBitrixComponentTemplate $template)
    {
        if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
            throw new BitrixTwigException('Prolog is not included');
        }

        try {
            $instance = self::getInstance();
            echo $instance->getEngine()->render($templateFile, [
                    'result' => $arResult,
                    'params' => $arParams,
                    'lang' => $arLangMessages,
                    'template' => $template,
                    'templateFolder' => $templateFolder,
                    'parentTemplateFolder' => $parentTemplateFolder,
                ]
            );
        } catch (\Exception $e) {
            throw new BitrixTwigException($e->getMessage(), 500, $e);
        }

        $component_epilog = $templateFolder . '/component_epilog.php';
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $component_epilog)) {
            $component = $template->__component;

            /* @var \CBitrixComponent $component */
            $component->SetTemplateEpilog([
                    'epilogFile' => $component_epilog,
                    'templateName' => $template->__name,
                    'templateFile' => $template->__file,
                    'templateFolder' => $template->__folder,
                    'templateData' => false,
                ]);
        }
    }

    /**
     * Default options for Twig environment.
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [
            'debug' => false,
            'charset' => SITE_CHARSET,
            'cache' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/twig',
            'auto_reload' => $this->request->get('clear_cache')
                ? 'Y' === strtoupper($this->request->get('clear_cache')) : false,
            'autoescape' => false,
        ];
    }

    /**
     * @return TemplateEngine
     *
     * @throws BitrixTwigException
     */
    protected static function getInstance()
    {
        return self::$instance ?: (self::$instance = new self());
    }

    /**
     * @return \Twig_Environment
     */
    protected function getEngine()
    {
        return $this->engine;
    }

    /**
     * Add extensions.
     */
    protected function addExtensions()
    {
        if ($this->engine->isDebug()) {
            $this->engine->addExtension(new \Twig_Extension_Debug());
        }

        $this->engine->addExtension(new PhpGlobals());
        $this->engine->addExtension(new BitrixExtension());
    }

    /**
     * @throws BitrixTwigException
     */
    private function generateInitEvent()
    {
        $eventName = 'onAfterTwigTemplateEngineInit';
        $event = new Event('', [$this->engine]);
        $event->send();

        if ($results = $event->getResults()) {
            foreach ($results as $result) {
                if (\Bitrix\Main\EventResult::SUCCESS == $result->getType()) {
                    $twig = current($result->getParameters());
                    if (!($twig instanceof \Twig_Environment)) {
                        throw new BitrixTwigException("Event {$eventName} must return instance of \\Twig_Environment");
                    }
                }
                $this->engine = $twig;
            }
        }
    }
}
