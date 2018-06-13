<?php
/**
 * User: andrew
 * Date: 03/06/2018
 * Time: 12:22.
 */

namespace Creative\Twig;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;
use Creative\Twig\Exception\BitrixTwigException;
use Creative\Twig\Extensions\BitrixExtension;
use Creative\Twig\Extensions\PhpGlobals;

/**
 * Singleton for render twig templates.
 */
class TemplateEngine
{
    const EVENT_NAME = 'onAfterTwigEngineInit';

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
     * @var Application
     */
    private $application;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * TemplateEngine constructor.
     *
     * @param Application|null          $application
     * @param Configuration|null        $configuration
     * @param \Bitrix\Main\Event|null   $eventClass
     *
     * @throws BitrixTwigException
     */
    public function __construct(Application $application = null, Configuration $configuration = null, \Bitrix\Main\Event $eventClass = null)
    {
        if (null === $application) {
            $this->application = Application::getInstance();
        } else {
            $this->application = $application;
        }

        if (null === $configuration) {
            $this->configuration = Configuration::getInstance();
        } else {
            $this->configuration = $configuration;
        }

        try {
            $this->request = $this->application->getContext()->getRequest();
        } catch (\Exception $e) {
            throw new BitrixTwigException($e->getMessage());
        }

        $config = (array) $this->configuration->get('twigRenderer') ?: [];
        $this->options = array_merge($this->getDefaultOptions(), $config);

        $this->engine = new \Twig_Environment(new TwigLoader(), $this->options);
        $this->addExtensions();

        if($eventClass === null) {
            $eventClass = new \Bitrix\Main\Event('', self::EVENT_NAME);
        }

        $eventClass->setParameters([$this->engine]);
        $this->generateInitEvent($eventClass);

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
     *
     * @return string
     */
    public static function render($templateFile, $arResult, $arParams, $arLangMessages,
        $templateFolder, $parentTemplateFolder, \CBitrixComponentTemplate $template)
    {
        if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
            throw new BitrixTwigException('Prolog is not included');
        }

        try {
            $instance = self::getInstance();
            $content = $instance->getEngine()->render($templateFile, [
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
            $component = $template->getComponent();

            if ($component instanceof \CBitrixComponent) {
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

        return $content;
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
            'charset' => 'utf-8',
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
     * @param \Bitrix\Main\Event $event
     *
     * @throws BitrixTwigException
     */
    private function generateInitEvent($event)
    {
        $event->send();

        if ($results = $event->getResults()) {
            foreach ($results as $result) {
                /**
                 * Use 1 instead of Bitrix\Main\EventResult for tests without all framework
                 */
                if (1 == $result->getType()) {
                    $twig = current($result->getParameters());
                    if (!($twig instanceof \Twig_Environment)) {
                        throw new BitrixTwigException("Event " . self::EVENT_NAME . " must return instance of \\Twig_Environment");
                    }
                }
                $this->engine = $twig;
            }
        }
    }
}
