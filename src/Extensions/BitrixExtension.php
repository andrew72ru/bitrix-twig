<?php
/**
 * User: andrew
 * Date: 03/06/2018
 * Time: 15:10.
 */

namespace Creative\Twig\Extensions;

use Creative\Twig\Exception\BitrixTwigException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;

/**
 * Class IncludeComponent
 * Extension for various bitrix functions.
 */
class BitrixExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var bool is new (d7) API
     */
    private $d7 = false;

    /**
     * Custom functions for twig templates
     *
     * @return array|\Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('showError', 'ShowError'),
            new \Twig_SimpleFunction('showMessage', 'ShowMessage'),
            new \Twig_SimpleFunction('showNote', 'ShowNote'),
            new \Twig_SimpleFunction('bitrix_sessid_post', 'bitrix_sessid_post'),
            new \Twig_SimpleFunction('bitrix_sessid_get', 'bitrix_sessid_get'),
            new \Twig_SimpleFunction(
                'getMessage',
                $this->isD7() ? '\\Bitrix\\Main\\Localization\\Loc::getMessage' : 'GetMessage'
            ),
            new \Twig_SimpleFunction('include_component', [__CLASS__, 'showComponent']),
            new \Twig_SimpleFunction('component_by_code', [__CLASS__, 'componentByCode']),
            new \Twig_SimpleFunction('_call_static', [__CLASS__, 'callStatic']),
        ];
    }

    /**
     * @return array
     *
     * @throws BitrixTwigException
     */
    public function getGlobals()
    {
        global $APPLICATION, $USER;

        $vars = [
            'APPLICATION' => $APPLICATION,
            'USER' => $USER,
        ];
        if ($this->isD7()) {
            try {
                $vars['app'] = \Bitrix\Main\Application::getInstance();
            } catch (SystemException $e) {
                throw new BitrixTwigException($e->getMessage());
            }
        }

        return $vars;
    }

    /**
     * Call static bitrix methods e.g. CFile::getFileArray, CFile::ResizeImageGet etc.
     *
     * @param $class
     * @param $method
     * @param array $arguments
     *
     * @return mixed
     */
    public static function callStatic($class, $method, $arguments = [])
    {
        return call_user_func([$class, $method], $arguments);
    }

    /**
     * IncludeComponent equivalent.
     *
     * @param $componentName
     * @param $componentTemplate
     * @param array $arParams
     * @param null  $parentComponent
     * @param array $arFunctionParams
     */
    public static function showComponent($componentName, $componentTemplate, $arParams = [], $parentComponent = null, $arFunctionParams = [])
    {
        global $APPLICATION;
        $APPLICATION->IncludeComponent($componentName, $componentTemplate, $arParams, $parentComponent, $arFunctionParams);
    }

    /**
     * !!! WARNING !!!
     * Required \Creative\Foundation\Iblocklocator!
     * Load component by symbol code.
     *
     * @param $code
     *
     * @return mixed
     *
     * @throws BitrixTwigException
     */
    public static function componentByCode($code)
    {
        if (!class_exists('Creative\Foundation\Iblocklocator')) {
            return false;
        }

        try {
            \Bitrix\Main\Loader::includeModule('creative.foundation');

            return \Bitrix\Main\Application::getInstance()->iblocklocator->getIdByCode($code);
        } catch (SystemException $e) {
            throw new BitrixTwigException($e->getMessage(), 500, $e);
        } catch (LoaderException $e) {
            throw new BitrixTwigException($e->getMessage(), 500, $e);
        }
    }

    /**
     * @return bool
     */
    private function isD7()
    {
        if (null === $this->d7) {
            $this->d7 = class_exists('\Bitrix\Main\Application');
        }

        return $this->d7;
    }
}
