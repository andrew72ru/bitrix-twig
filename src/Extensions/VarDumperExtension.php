<?php
/**
 * 28.04.2020.
 */

namespace Creative\Twig\Extensions;

use Symfony\Component\VarDumper\VarDumper;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Template;
use Twig\TemplateWrapper;
use Twig\TwigFunction;


class VarDumperExtension extends DebugExtension
{
    public function getFunctions()
    {
        // dump is safe if var_dump is overridden by xdebug
        $isDumpOutputHtmlSafe = \extension_loaded('xdebug')
            // false means that it was not set (and the default is on) or it explicitly enabled
            && (false === ini_get('xdebug.overload_var_dump') || ini_get('xdebug.overload_var_dump'))
            // false means that it was not set (and the default is on) or it explicitly enabled
            // xdebug.overload_var_dump produces HTML only when html_errors is also enabled
            && (false === ini_get('html_errors') || ini_get('html_errors'))
            || 'cli' === \PHP_SAPI
        ;
        return [
            new TwigFunction(
                'dump',
                [__CLASS__,'twig_var_dump'],
                [
                    'is_safe' => $isDumpOutputHtmlSafe ? ['html'] : [],
                    'needs_context' => true,
                    'needs_environment' => true,
                    'is_variadic' => true
                ]
            ),
        ];
    }

    public static function twig_var_dump(Environment $env, $context, array $vars = [])
    {
        if (!$env->isDebug()) {
            return;
        }

        ob_start();

        VarDumper::dump($vars);

        return ob_get_clean();
    }
}