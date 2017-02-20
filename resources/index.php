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

define("FERNICO",true);
define("FERNICO_PATH", str_replace("/resources","",__DIR__));

// I am Fernico. Iron, nickel and cobalt define my identity.
// I am Astrid. I possess great strength.

require(FERNICO_PATH . '/lib/Astrid/App.php');
new fernico();