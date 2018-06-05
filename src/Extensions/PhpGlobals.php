<?php
/**
 * User: andrew
 * Date: 03/06/2018
 * Time: 15:03.
 */

namespace Creative\Twig\Extensions;

/**
 * Access to global variables in twig templates.
 */
class PhpGlobals extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    const NAME = 'php_globals';

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return [
            '_SERVER' => $_SERVER,
            '_REQUEST' => $_REQUEST,
            '_GET' => $_GET,
            '_POST' => $_POST,
            '_FILES' => $_FILES,
            '_SESSION' => $_SESSION,
            '_COOKIE' => $_COOKIE,
            '_GLOBALS' => $GLOBALS,
        ];
    }
}
