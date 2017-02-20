<p align="center">
  <a href="http://areebmajeed.me/" target="_blank"><img src="http://areebmajeed.me/assets/projects/Fernico.png"/></a>
</p>

## About Fernico ##
Just a simple MVC skeleton framework developed with simplicity at the core. Fernico is easy to use, fast to setup and great for small projects. If you're building a massive application, please consider using frameworks such as Laravel, Symfony, Yii or CakePHP.

## Features ##

Fernico comes with multiple functions and libraries to save the time. I've listed some of the major functions below.

 - Authentication module
 - In-built PHPMailer integration
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

## Useful Classes and Methods ##

 - `Config::fetch('configParameterName')` Static class to obtain the value of a configuration setting set in config.php file.
 - `Request::GET('query', $filtered = false)` Returns a GET value set in the URL. 
 -  `Request::POST('data', $filtered = false)` Returns a POST value set in the page. 
 - `Request::COOKIE('session', $filtered = false)` Returns a COOKIE value set in the headers.
 - `Request::cleanInput($input)` Cleans the input and returns it. Useful to avoid attacks like XSS.

## Constants and Variables ##

 - `FERNICO` This constant would be useful if someone called the page/file directly or using Fernico.
 - `FERNICO_PATH` Returns the absolute path of the installation. If your installation is at `/var/www/projects/Fernico`, it will return the same throughout the script. Please note that `fernico_getAbsURL()` returns the absolute URL with a trailing slash, this constant doesn't.
 - `$fernico_db` It is the MySQLi object. Use this to communicate with the database.
 
## Plugins ##

Fernico contains a tiny plugin system. This project comes with a simple plugin to echo a string containing the version number of the installation. It also utilizes the hooking system of the framework.

To include a plugin (called function, internally), you've to update the json file (called functions.json) at functions folder and include the name of the plugin and its entry point.

## Releases and Versions ##

 - master (containing the stable code)
 - develop

Please commit new code to the **develop** branch.

## Authentication Module ##

I've also created a login system based on Fernico's native Authentication API. It is available for download at this [link](http://areebmajeed.me/assets/downloads/Fernico-login-script.zip).

## Installation ##

It's easy and straightforward to install Fernico.

 1. Clone the repository or download it.
 2. Upload all the files to your web directory (for example, /var/www/).
 3. Open config.php and start configuring it.
 4. It's ready! Now, access your website, you should see the placeholder page loading.
 5. If you're planning to use database too, you also need to create the SQL records at your database by importing the database structure shown below.
 5. If you aren't using Apache as your web-server, some extra configuration may be needed from server-side. Proceed to **nginx configuration** section.

## Database Structure ##

    CREATE TABLE IF NOT EXISTS `email_updates` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `user_id` bigint(20) NOT NULL,
      `email` varchar(255) NOT NULL,
      `confirm_code` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
    
    CREATE TABLE IF NOT EXISTS `error_log` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `message` text NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
    
    CREATE TABLE IF NOT EXISTS `users` (
      `user_id` bigint(11) NOT NULL AUTO_INCREMENT,
      `user_name` varchar(32) NOT NULL,
      `user_email` varchar(64) NOT NULL,
      `password_hash` varchar(255) NOT NULL,
      `user_verified` tinyint(4) NOT NULL DEFAULT '0',
      `activation_hash` varchar(255) DEFAULT NULL,
      `reset_hash` varchar(255) DEFAULT NULL,
      `rememberme_token` varchar(255) DEFAULT NULL,
      `failed_logins` mediumint(9) NOT NULL,
      `last_failed_login` bigint(20) NOT NULL,
      `last_logged_in` datetime NOT NULL,
      `registration_datetime` datetime NOT NULL,
      `registration_ip` varchar(255) NOT NULL,
      `admin_powers` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`user_id`),
      UNIQUE KEY `user_name` (`user_name`),
      UNIQUE KEY `user_name_2` (`user_name`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

## nginx Configuration ##

    server {
    
        listen 80;
        server_name example.com;
        root /var/www/public_html/resources;
        index index.php;
    
        location ~ /(config|controllers|functions|lib|models|views) {
          deny all;
          return 404;
        }
    
        location / {
            try_files $uri /index.php?param=$uri&$args;
        }
    	
        location ~ \.php$ {
            try_files $uri  = 401;
            include /etc/nginx/fastcgi_params;
            fastcgi_pass unix:/var/run/php-fastcgi/php-fastcgi.socket;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    
    }

## Contributions and Development ##

Currently, I am working on the framework in my leisure. I'd be happy if you help to grow this framework. Feel free to contribute to the framework.

Please contribute to the **develop** branch.

## Bugs and Suggestions ##

If you've encountered any bug, please feel free to create a GitHub issue. We're also open to suggestions. Look no further and create a GitHub issue if you've great ideas for the project.

If you have a critical security bug, instead of creating a GitHub issue, please email me at **hello at volcrado dot com**.

## Contributors ##

 - Areeb Majeed - [http://areebmajeed.me/](http://areebmajeed.me/)
 - Other contributors - https://github.com/areebmajeed/fernico/graphs/contributors

If you would like to get your name listed here, hit me an email at **hello at volcrado dot com**.

## License ##

Fernico is a open-sourced framework released under the **[MIT License](https://opensource.org/licenses/MIT)**.
