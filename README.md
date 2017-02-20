<p align="center">
  <a href="http://areebmajeed.me/" target="_blank"><img src="http://areebmajeed.me/assets/projects/Fernico.png"/></a>
</p>

## About Fernico ##
Just a simple MVC skeleton framework developed with simplicity at the core. Fernico is easy to use, fast to setup and great for small projects. If you're building a massive application, please consider using frameworks such as Laravel, Symphony, Yii or CakePHP.

## Features ##

Fernico comes with multiple functions and libraries to save the time. I've listed some of the major functions below.

 - Authentication module
 - In-built PHPMailer installation
 - Support for future plugins (due to plugins system)
 - Templating system - Smarty
 - Error reporting and logging
 - Callback and hooking system
 - Anti-CSRF functions

## Core Functions ##

1. `fernico_reportError($error_message)` Raise a non-critical error, stored in the database.
2. `fernico_get($url)` Uses cURL, returns the response by GETTING the `$url`.
3. `fernico_post($url, $data)` Same as above, but it actually POSTS a URL with the data you set in the `$data` array.
4. `fernico_destroy($error)` Same as PHP's native function `exit()`, but in a proper way. If `$error` is set, then it's logged in the database/error log file.
5. `fernico_version()` Returns Fernico version you're using.
6. `fernico_loadComponent($template_dir, $template, $options)` Renders a template (view) file using Smarty.`$template` is the template directory, `$template` is the name of the template file, `$options` is an array to set variables for Smarty to render.
7. `fernico_showLoadedFunctions()` Returns an array of plugins/functions active.
8. `fernico_registerHook($function, $callback, $parameters)` Registers a callback with parameters when a certain function is executed. `$function` is the name of the function when executed calls the `$callback` function with `$parameters`. `$parameters` is an array, you may also include a string there (if your callback function is like `hello($param)`), though.
9. `fernico_executeHooks()` This executes all the hooks that are attached to the function. You need to call this function within your function to call the hooks attached with it.
10. `fernico_generateAntiCSRFToken()` Generates a secure anti-CSRF token and stores it at $_SESSION['fernico_token'].
11. `fernico_verifyAntiCSRFToken($input_token)` Verifies the token for integrity and returns a boolean.
12. `fernico_getAbsURL()` Retrieves the exact root URL of the installation. Very useful to set the absolute URLs in your application. If you echo the function at this page `http://example.com/fernico/page/hello-world`, it will return `http://example.com/fernico/`.
13. `fernico_getIPAddress()` Returns the IP address of the visitor.
14. `fernico_validateIPAddress($ip_address)` Validates the IP address, and returns a boolean.
15. `fernico_countryCode()` Returns the ISO country code (for example, GB for United Kingdom). Please remember that this function uses multiple RESTful API to get the data.
