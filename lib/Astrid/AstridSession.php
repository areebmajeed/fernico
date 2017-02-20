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
 * This class is extended by AstridController (fundamental controller).
 */

class AstridSession {

    private $session_name, $secure, $http_only;

    public function __construct() {

        ini_set('session.use_only_cookies', 1);
        ini_set('session.hash_function', 'sha512');
        ini_set('session.hash_bits_per_character', 5);
        ini_set('session.hash_bits_per_character', 5);
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', '512');

        $this->session_name = Config::fetch('SESSION_NAME');
        $this->secure = Config::fetch('SECURE');
        $this->http_only = Config::fetch('HTTP_ONLY');

        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"],
            $this->secure, $this->http_only);
        session_name($this->session_name);
        session_start();

    }

}