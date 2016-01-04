<?php
/**
 * Created by PhpStorm.
 * User: .PolluX
 * Date: 1/4/2016
 * Time: 4:24 PM
 */

namespace TeamManager\Exceptions;

use Exception;

class NotFoundException extends Exception
{

    public $message;

    /**
     * NotFoundException constructor.
     */
    public function __construct()
    {
        $this->message = 'The page you try to access isn\'t here.';
        // TODO: proper message
    }


}