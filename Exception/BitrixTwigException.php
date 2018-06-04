<?php
/**
 * User: andrew
 * Date: 03/06/2018
 * Time: 14:14.
 */

namespace app\twig\Exception;

use Throwable;

/**
 * BitrixTwigException.
 */
class BitrixTwigException extends \Exception
{
    /**
     * BitrixTwigException constructor.
     *
     * @param string|null    $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message = null, $code = 500, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
