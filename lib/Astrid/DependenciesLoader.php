<?php

/**
 * Fernico - Ridiculously lite PHP framework
 *
 * @author Areeb Majeed, Volcrado Holdings
 * @package Fernico
 * @copyright 2017 - Volcrado Holdings Limited
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://volcrado.com/
 *
 */

if (!defined('FERNICO')) {
    fernico_destroy();
}

/*
 * There's no function here. Just to include some core dependencies in the system for use.
 */

require_once(FERNICO_PATH . '/lib/Smarty/Smarty.class.php');

spl_autoload_register(function ($class) {

    $array = array(

        'PHPMailer' => 'PHPMailer/index.php',
        'Authentication' => 'Authentication/Authentication.php'

    );

    $path = FERNICO_PATH . '/lib/' . $array[$class];
    require_once($path);

});