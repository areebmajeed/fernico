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
 * This file contains crucial Fernico functions that are used across the system.
 */


/*
 * This function can be used to log the errors.
 * Errors are usually logged in the database, but can also be set to log in the a file.
 */

function fernico_reportError($error) {

    global $fernico_db;

    if (Config::fetch('ERROR_REPORTING') == true) {

        if (Config::fetch('ERROR_LOG_DATABASE') == true) {

            $stmt = $fernico_db->stmt_init();
            $stmt->prepare("INSERT INTO error_log (message) VALUES (?)");
            $stmt->bind_param("s", $error);
            $stmt->execute();
            $stmt->close();

        } else {

            $error_file = fopen(FERNICO_PATH . "/storage/log/error.log", "a");
            fwrite($error_file, PHP_EOL . $error);
            fclose($error_file);

        }
    }

}

/*
 * This function allows you to send GET requests.
 * Equivalent of file_get_contents(), but in cURL.
 */

function fernico_get($url) {

    $ch = curl_init();
    $timeout = 10;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;

}

/*
 * This function allows you to send POST data.
 * Uses cURL to send the data.
 */

function fernico_post($url, $data = array()) {

    $timeout = 10;
    $fields_string = '';

    foreach ($data as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }

    rtrim($fields_string, '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;

}

/*
 * This function destroys the process. It can also raise an error.
 */

function fernico_destroy($error = null) {

    global $fernico_db;

    if ($error != null) {
        fernico_reportError($error);
    }

    if (isset($fernico_db)) {
        $fernico_db->close();
    }

    exit();

}

/*
 * Simple function to tell you what version you're using.
 */

function fernico_version() {

    $handle = fopen(FERNICO_PATH . '/lib/Astrid/Version.php', "r");
    $contents = fread($handle, filesize(FERNICO_PATH . '/lib/Astrid/Version.php'));
    fclose($handle);

    return $contents;

}

/*
 * This function can be used to render a template manually, instead of using the AstridController.
 */

function fernico_loadComponent($template_dir, $template, $options) {

    $smarty = new Smarty;

    foreach ($options as $key => $value) {

        $smarty->assign($key, $value);

    }

    $smarty->setCompileDir(Config::fetch('TEMPLATE_COMPILED_DIR'));
    $smarty->display(FERNICO_PATH . '/views/' . $template_dir . '/' . $template);

}

/*
 * This function handles non-critical errors that do not terminate the system.
 */

function fernico_nonCriticalErrorHandler($err_no, $err_str, $err_file, $err_line) {

    $error = "[" . $err_line . "] [" . $err_file . "] $err_str";
    fernico_reportError($error);

}

/*
 * This function handles critical errors that break the script. It logs the errors and shows necessary debugging infromation.
 */

function fernico_criticalErrorHandler() {

    $error = error_get_last();

    if (in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {

        $error_msg = $error['message'];
        $err_text = "A critical error occurred in the application. Terminating.";

        fernico_reportError($error_msg);
        fernico_loadComponent('fatalError', 'error.tpl', array('message' => $err_text, 'error_message' => $error_msg));
        fernico_destroy();

    }

}

/*
 * Returns a list of active functions.
 */

function fernico_showLoadedFunctions() {

    global $global_fernico_plugins;
    return $global_fernico_plugins;

}

/*
 * Registers a hook enabling developers to perform tasks when a function is called.
 */

function fernico_registerHook($function, $callback, $parameters = array()) {

    global $fernico_hooks_registered;

    $ar = array(
        "onFunction" => $function,
        "callFunction" => $callback,
        "withParameters" => $parameters
    );

    $fernico_hooks_registered[] = $ar;

}

/*
 * This function must be called whenever you want to execute the hooks assigned for your function.
 */

function fernico_executeHooks() {

    global $fernico_hooks_registered;

    $fn_name = debug_backtrace()[1]['function'];
    $found_hooks = array_keys(array_column($fernico_hooks_registered, 'onFunction'), $fn_name);

    foreach ($found_hooks as $val) {

        $callFunction = $fernico_hooks_registered[$val]['callFunction'];
        $withParameters = $fernico_hooks_registered[$val]['withParameters'];

        if (is_array($withParameters)) {
            return call_user_func_array($callFunction, $withParameters);
        } else {
            return $callFunction($withParameters);
        }

    }

}

/*
 * Generate a quick, secure CSRF token. Include it in the SESSION and return it.
 */

function fernico_generateAntiCSRFToken() {
    $fernico_token = bin2hex(random_bytes(32));
    $_SESSION['fernico_token'] = $fernico_token;
    return $fernico_token;
}

/*
 * Validates the user input token. This function uses hash_equals() function to mitigate timing attack attempts.
 */

function fernico_verifyAntiCSRFToken($input_token) {

    if (hash_equals($_SESSION['fernico_token'], $input_token)) {
        return true;
    } else {
        return false;
    }

}

/*
 * Gets the exact URL with a trailing slash at the end.
 */

function fernico_getAbsURL() {

    $delimiter = '';

    if (Request::GET('param')) {

        $delimiter = Request::GET('param', true);

    }
	
	if ($delimiter == "/") {
		
		$delimiter = "";
		
	}
	
	if(substr($delimiter, 0, 1) == "/") {	
	
		$delimiter = substr($delimiter, 1);
		
	}
	
    $url = explode($delimiter, $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    return $url[0];

}

/*
 * Get user IP address.
 */

function fernico_getIPAddress() {
    // check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && fernico_validateIPAddress($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    // check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check if multiple ips exist in var
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (fernico_validateIPAddress($ip)) {
                    return $ip;
                }
            }
        } else {
            if (fernico_validateIPAddress($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && fernico_validateIPAddress($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    }
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && fernico_validateIPAddress($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && fernico_validateIPAddress($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (!empty($_SERVER['HTTP_FORWARDED']) && fernico_validateIPAddress($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    }

    // return unreliable ip since all else failed
    return $_SERVER['REMOTE_ADDR'];
}

/*
 * Validate an IP address.
 */

function fernico_validateIPAddress($ip) {
    if (strtolower($ip) === 'unknown') {
        return false;
    }

    // generate ipv4 network address
    $ip = ip2long($ip);

    // if the ip is set and not equivalent to 255.255.255.255
    if ($ip !== false && $ip !== -1) {
        // make sure to get unsigned long representation of ip
        // due to discrepancies between 32 and 64 bit OSes and
        // signed numbers (ints default to signed in PHP)
        $ip = sprintf('%u', $ip);
        // do private network range checking
        if ($ip >= 0 && $ip <= 50331647) {
            return false;
        }
        if ($ip >= 167772160 && $ip <= 184549375) {
            return false;
        }
        if ($ip >= 2130706432 && $ip <= 2147483647) {
            return false;
        }
        if ($ip >= 2851995648 && $ip <= 2852061183) {
            return false;
        }
        if ($ip >= 2886729728 && $ip <= 2887778303) {
            return false;
        }
        if ($ip >= 3221225984 && $ip <= 3221226239) {
            return false;
        }
        if ($ip >= 3232235520 && $ip <= 3232301055) {
            return false;
        }
        if ($ip >= 4294967040) {
            return false;
        }
    }
    return true;
}

/*
 * Get user country (ISO Code).
 */

function fernico_countryCode() {

    $country = filterData($_SERVER["HTTP_CF_IPCOUNTRY"]);

    if ($country == "XX" OR $country == "") {

        $country = fetch_content('http://freegeoip.net/json/' . fernico_getIPAddress());
        $country = json_decode($country, true);
        $country = $country['country_code'];

        if ($country == "") {

            $json = fetch_content("http://ipinfo.io/" . fernico_getIPAddress() . "/geo");
            $details = json_decode($json, true);
            $country = $details['country'];

        }

    }

    return $country;

}

/*
 * Not much, just set the error handlers.
 */

set_error_handler("fernico_nonCriticalErrorHandler", E_ALL);
register_shutdown_function('fernico_criticalErrorHandler');
