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
 * Load all plugins one by one by reading the functions.json file.
 *
 */

$global_fernico_plugins = array();

$handle = fopen(FERNICO_PATH . '/functions/functions.json', "r");
$contents = json_decode(fread($handle, filesize(FERNICO_PATH . '/functions/functions.json')));
fclose($handle);

foreach ($contents as $key => $plugin) {

	$plugin_path = FERNICO_PATH . '/functions/' . $plugin;

    if (file_exists($plugin_path)) {
        $global_fernico_plugins[] = $key;
        require_once($plugin_path);
    } else {
        fernico_reportError("Cannot find the plugin entry point: " . $plugin);
    }

}