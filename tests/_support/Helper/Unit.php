<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends \Codeception\Module
{
    /**
     * @param array $settings
     * @throws \Exception
     */
    public function _beforeSuite($settings = [])
    {
        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            if (!$_SERVER['DOCUMENT_ROOT'] = self::getDocumentRoot()) {
                throw new \Exception('Can\'t find document root');
            }
        }

        include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
        global $arCustomTemplateEngines;
        $arCustomTemplateEngines['twig'] = [
            'templateExt' => ['twig', 'html.twig'],
            'function'    => 'renderTwigTemplate'
        ];
    }

    protected static function getDocumentRoot()
    {
        $dir = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/web/bitrix';
        return is_dir($dir) ? dirname($dir) : false;
    }
}
