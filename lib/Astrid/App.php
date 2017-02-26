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

require_once(FERNICO_PATH . '/config/config.php');
require_once('Config.php');
require_once('DependenciesLoader.php');
require_once('Core.php');
require_once('Request.php');
require_once('AstridSession.php');
require_once('AstridController.php');
require_once(FERNICO_PATH . '/functions/Loader.php');

class fernico {

    private $controller;
    private $parameters = array();
    private $controller_name;
    private $action_name;
    private $controller_name_parsed;

    /*
     * Initializes the system by calling the necessary functions present in this class.
     */

    public function __construct() {
        $this->startDatabaseConnection();
        $this->parseURL($this->retrievePath());
        $this->defineControllerActions();
        $this->loadController();
    }

    /*
     * This function generates a database connection to the MySQL server. The global variable is available through the framework.
     */

    private function startDatabaseConnection() {

        global $fernico_db;
        $fernico_db = new mysqli(Config::fetch('DATABASE_HOST'), Config::fetch('DATABASE_USER'),
            Config::fetch('DATABASE_PASSWORD'), Config::fetch('DATABASE_NAME'));

        if ($fernico_db->connect_errno) {
            fernico_reportError($fernico_db->connect_errno);
        }

    }

    /*
     * This function breaks the URL and passes the needed format to the parseURL function to obtain controller, action and parameters.
     */

    private function parseURL($url) {
        $url = trim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = explode('/', $url);
        $this->controller_name = isset($url[0]) ? $url[0] : null;
        $this->action_name = isset($url[1]) ? $url[1] : null;
        unset($url[0], $url[1]);
        $this->parameters = array_values($url);
    }

    /*
     * This function checks if the controller and action exists. If either doesn't exist, the Error404Controller is called.
     * In case of present parameters, this calls back the method with an array of parameters set in the URL.
     */

    private function retrievePath() {

        $url = Request::GET('param');
        return $url;

    }

    /*
     * This function splits the URL and obtains the controller, action/method and parameters.
     * For instance, assume the following URL: https:/rapid2fa.com/page/about/history
     *
     * In this URL, 'page' is the controller, 'about' is an action/method, and history is a parameter.
     *
     * This function extracts the controller, action/method and sets all parameters in an array.
     */

    private function defineControllerActions() {

        if (!$this->controller_name) {
            $this->controller_name = Config::fetch('DEFAULT_CONTROLLER');
        }

        if (!$this->action_name OR empty($this->action_name)) {
            $this->action_name = Config::fetch('DEFAULT_ACTION');
        }

        $this->action_name = str_replace('-','__',$this->action_name);
        $this->controller_name = $this->controller_name . 'Controller';
        $this->controller_name_parsed = str_replace('-','__',$this->controller_name);

    }

    /*
     * This function once called defines default controller and method/action in case they haven't been set.
     * Useful when you've to load your homepage (or in similar cases).
     *
     * To set the default controller and action, please edit the config/config.php file.
     *
     */

    private function loadController() {

        if (file_exists(FERNICO_PATH . '/controllers/' . $this->controller_name . '.php')) {

            require(FERNICO_PATH . '/controllers/' . $this->controller_name . '.php');
            $this->controller = new $this->controller_name_parsed();

            if (method_exists($this->controller, $this->action_name)) {

                if (!empty($this->parameters)) {
                    call_user_func_array(array($this->controller, $this->action_name), $this->parameters);
                } else {
                    $this->controller->{$this->action_name}();
                }

            } else {

                $this->controller_name = "Error404Controller";
                $this->action_name = "error404";
                require(FERNICO_PATH . '/controllers/' . $this->controller_name . '.php');
                $this->controller = new $this->controller_name();
                $this->controller->{$this->action_name}();

            }

        } else {

            $this->controller_name = "Error404Controller";
            $this->action_name = "error404";
            require(FERNICO_PATH . '/controllers/' . $this->controller_name . '.php');
            $this->controller = new $this->controller_name();
            $this->controller->{$this->action_name}();

        }

    }

}
