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

// Nothing strengthens authority so much as silence.

if (!defined('FERNICO')) {
    fernico_destroy();
}

function themeFooter() {

    return fernico_executeHooks();

}

function echoFootComments($ver) {

    $core = "<p align='center''>Powered by Fernico framework - " . $ver . "</p>";
    return $core;

}

fernico_registerHook("themeFooter", "echoFootComments", fernico_version());