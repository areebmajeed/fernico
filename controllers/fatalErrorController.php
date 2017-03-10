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

class fatalErrorController extends AstridController {

    public function errorHandler($err_text, $error_msg) {
        fernico_loadComponent('fatalError', 'error.tpl', array('message' => $err_text, 'error_message' => $error_msg));
    }

}
