<?php
/**
 * User: andrew
 * Date: 03/06/2018
 * Time: 12:38.
 */

namespace Creative\Twig;

use Creative\Twig\Exception\BitrixTwigException;

/**
 * Class BitrixLoader
 * Twig loader for bitrix.
 */
class TwigLoader extends \Twig_Loader_Filesystem implements \Twig_LoaderInterface
{
    /**
     * @var array resolved paths
     */
    private static $resolved = [];

    /**
     * @var array normalized paths
     */
    private static $normalized = [];

    /**
     * Include syntax.
     *
     * `vendor:componentname[:template[:specifictemplatefile]]`
     *
     * For example: bitrix:news.list:.default or bitrix:sale.order:show:step1
     *
     * ```
     * {% extends 'bitrix:news.list:.default' %} {# default template name is 'template.twig' or 'template.html.twig' #}
     * ```
     *
     * @param string $name
     *
     * @return bool|mixed|string
     *
     * @throws BitrixTwigException
     * @throws \Twig_Error_Loader
     */
    public function getSource($name)
    {
        return file_get_contents($this->getSourcePath($name));
    }

    /**
     * @param string $name
     *
     * @return bool|null|string|string[]
     *
     * @throws BitrixTwigException
     */
    public function getCacheKey($name)
    {
        return $this->normalizeName($name);
    }

    /**
     * For development.
     *
     * @param string $name
     * @param int    $time
     *
     * @return bool
     *
     * @throws BitrixTwigException
     * @throws \Twig_Error_Loader
     */
    public function isFresh($name, $time)
    {
        return filemtime($this->getSourcePath($name)) <= $time;
    }

    /**
     * @param $name
     *
     * @return mixed|null|string|string[]
     *
     * @throws BitrixTwigException
     * @throws \Twig_Error_Loader
     */
    public function getSourcePath($name)
    {
        $name = $this->normalizeName($name);

        if (isset(static::$resolved[$name])) {
            return static::$resolved[$name];
        }
        $resolved = '';
        if (false !== strpos($name, ':')) {
            $resolved = $this->getComponentTemplatePath($name);
        } elseif (DIRECTORY_SEPARATOR === ($firstChar = substr($name, 0, 1))) {
            $resolved = is_file($name) ? $name : $_SERVER['DOCUMENT_ROOT'] . $name;
        }
        if (!file_exists($resolved)) {
            throw new \Twig_Error_Loader("Unable to find template '{$name}'");
        }

        return static::$resolved[$name] = $resolved;
    }

    /**
     * @param \CBitrixComponentTemplate $template
     *
     * @return string
     */
    public function makeComponentTemplateName(\CBitrixComponentTemplate $template)
    {
        if ($template->__fileAlt) {
            return $template->__fileAlt;
        }
        $templatePage = $template->__page;
        $templateName = $template->__name;
        $componentName = $template->__component->getName();

        return "{$componentName}:{$templateName}:{$templatePage}";
    }

    /**
     * @param $name
     *
     * @return bool|mixed|null|string|string[]
     *
     * @throws BitrixTwigException
     * @throws \Twig_Error_Loader
     */
    protected function findTemplate($name)
    {
        return $this->getSourcePath($name);
    }

    /**
     * @return bool|string
     */
    protected function getLastRenderedTemplate()
    {
        $trace = debug_backtrace();
        foreach ($trace as $point) {
            /** @var $obj \Twig\Template */
            if (isset($point['object']) && ($obj = $point['object']) instanceof \Twig\Template) {
                return $obj->getSourceContext()->getPath();
            }
        }

        return false;
    }

    /**
     * @param $name
     *
     * @return mixed|null|string|string[]
     *
     * @throws BitrixTwigException
     */
    protected function normalizeName($name)
    {
        if (false !== strpos($name, DIRECTORY_SEPARATOR)) {
            $name = preg_replace('#/{2,}#', '/', str_replace('\\', '/', $name));
        }
        $isComponentPath = false !== strpos($name, ':');
        $isGlobalPath = '/' === substr($name, 0, 1);
        if (($isComponentPath || $isGlobalPath) && isset(static::$normalized[$name])) {
            return static::$normalized[$name];
        }
        if ($isComponentPath) {
            $normalizedName = $this->explodeName($name);
        } elseif ($isGlobalPath) {
            $normalizedName = $name;
        } else {
            $lastRendered = $this->getLastRenderedTemplate();
            if ($lastRendered) {
                $normalizedName = dirname($lastRendered) . '/' . $name;
            } else {
                $normalizedName = $name;
            }
        }

        return static::$normalized[$name] = $normalizedName;
    }

    /**
     * @param string $name
     *
     * @return string
     * @throws BitrixTwigException
     */
    private function explodeName($name)
    {
        $pathArr = explode(':', $name);
        if (count($pathArr) < 2) {
            throw new BitrixTwigException("The component path {$name} is wrong");
        }
        $namespace = $pathArr[0];
        $component = $pathArr[1];

        if (!isset($pathArr[2])) {
            $template = '.default';
        } else {
            $template = $pathArr[2];
        }

        if (!isset($pathArr[3])) {
            $file = 'template';
        } else {
            $file = $pathArr[3];
        }
        return "{$namespace}:{$component}:{$template}:{$file}";
    }

    /**
     * @param $name
     *
     * @return string
     *
     * @throws BitrixTwigException
     */
    private function getComponentTemplatePath($name)
    {
        $name = $this->normalizeName($name);
        list($namespace, $component, $template, $page) = explode(':', $this->explodeName($name));
        $isRelative = $page !== basename($page);
        $dotExt = '.twig';
        if ($isRelative) {
            if ('twig' !== pathinfo($page, PATHINFO_EXTENSION)) {
                $page .= $dotExt;
            }
        } else {
            $page = basename($page, $dotExt);
        }

        $componentName = "{$namespace}:{$component}";
        $component = new \CBitrixComponent();

        $component->InitComponent($componentName, $template);
        if (!$isRelative) {
            $component->__templatePage = $page;
        }
        $obTemplate = new \CBitrixComponentTemplate();
        $obTemplate->Init($component);
        $templatePath = $_SERVER['DOCUMENT_ROOT']
            . ($isRelative ? ($obTemplate->GetFolder() . DIRECTORY_SEPARATOR . $page) : $obTemplate->GetFile());

        return $templatePath;
    }
}
