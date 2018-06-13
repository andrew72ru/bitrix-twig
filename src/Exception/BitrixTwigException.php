<?php
/**
 * User: andrew
 * Date: 03/06/2018
 * Time: 14:14.
 */

namespace Creative\Twig\Exception;

/**
 * BitrixTwigException.
 */
class BitrixTwigException extends \Twig_Error
{
    /**
     * BitrixTwigException constructor.
     *
     * @param string|null   $message
     * @param int           $lineno
     * @param null          $source
     * @param \Exception    $previous
     */
    public function __construct($message, $lineno = -1, $source = null, \Exception $previous = null)
    {
        parent::__construct($message, $lineno, $source, $previous);
    }
}
