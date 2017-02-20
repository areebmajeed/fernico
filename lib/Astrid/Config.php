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
 * This class (and function) can be used to retrieve settings by their keys.
 * This function returns the settings arrays set in config/config.php.
 */

class Config {

    public static function fetch($key) {

        global $global_fernico_settings;

        if (isset($global_fernico_settings[$key])) {

            return $global_fernico_settings[$key];

        } else {

            return null;

        }

    }

}