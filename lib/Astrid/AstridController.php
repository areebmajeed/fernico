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
 * This is the fundamental controller. The other child controllers extend this class and come under it.
 */

class AstridController extends AstridSession {

    public function __construct() {

        // Create the session.
        parent::__construct();

    }

    /*
     * This function is usually called from the child controller to render a template.
     * Fernico uses Smarty to render templates and to populate them with data.
     *
     * Necessary settings relating to this function may be found at config/config.php file.
     */

    public function renderTemplate($template, $options = array()) {

        $smarty = new Smarty;

        foreach ($options as $key => $value) {
            $smarty->assign($key, $value);
        }

        $smarty->setCompileDir(Config::fetch('TEMPLATE_COMPILED_DIR'));
        $smarty->display(FERNICO_PATH . '/views/' . Config::fetch("TEMPLATE_DIR") . '/' . $template);

    }

}