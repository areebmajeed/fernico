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

class homeIndexController extends AstridController {

    public function __construct() {
        require_once(FERNICO_PATH . "/models/Home.php");
        parent::__construct(); // Generate session. If your class doesn't have a constructer, session is made automatically.
    }

    public function home() {
        $this->renderTemplate('Home.tpl', array('name' => Home::NameSet()));
    }

}
