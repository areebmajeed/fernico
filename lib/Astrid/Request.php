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

class Request {

    /*
     * This function cleans the input and returns clean inputs.
     * Recommended to use to clean the user inputs and to avoid XSS attacks.
     *
     * Usage: Request::cleanInput($my_xss_code);
     */

    static function cleanInput($input) {

        $search = array(
            '@<script[^>]*?>.*?</script>@si',
            '@<[\/\!]*?[^<>]*?>@si',
            '@<style[^>]*?>.*?</style>@siU',
            '@<![\s\S]*?--[ \t\n\r]*>@'
        );

        $output = preg_replace($search, '', $input);
        return strip_tags($output);

    }

    /*
     * You can retrieve a POST value using this function.
     * Cleaning the user input can also be done on the go by setting the second parameter to true.
     */

    static function POST($key, $filtered = false) {

        if (isset($_POST[$key])) {

            if ($filtered == true) {

                return self::cleanInput($_POST[$key]);

            } else {

                return $_POST[$key];

            }

        } else {

            return null;

        }


    }

    /*
     * You can retrieve a GET value using this function.
     * Cleaning the user input can also be done on the go by setting the second parameter to true.
     */

    static function GET($key, $filtered = false) {

        if (isset($_GET[$key])) {

            if ($filtered == true) {

                return self::cleanInput($_GET[$key]);

            } else {

                return $_GET[$key];

            }

        } else {

            return null;

        }


    }

    /*
     * You can retrieve a COOKIE value using this function.
     * Cleaning the user input can also be done on the go by setting the second parameter to true.
     */

    static function COOKIE($key, $filtered = false) {

        if (isset($_COOKIE[$key])) {

            if ($filtered == true) {

                return self::cleanInput($_COOKIE[$key]);

            } else {

                return $_COOKIE[$key];

            }

        } else {

            return null;

        }


    }

}