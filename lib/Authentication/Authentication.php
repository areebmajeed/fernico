<?php

/**
 * Fernico - Ridiculously lite PHP framework
 * Function: Authentication.php. Makes authentication ridiculously easy.
 *
 * Please be sure to add Authentication settings to your config.php file before you begun to use.
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
 * This file contains the Authentication class with methods to make it easy to authenticate users.
 */

class Authentication {

    private $cookie_secret, $sessionTime;

    /*
     * This __construct() function checks if the user is having a remember_me cookie on their browser. If yes, it validates the cookie against the server and logs them in.
     *
     */

    public function __construct() {

        $this->cookie_secret = Config::fetch('COOKIE_SECRET');
        $this->sessionTime = 60 * 60 * 24 * Config::fetch('SESSION_DAYS');

        if (Request::COOKIE('remember_me') != null && !isset($_SESSION['user_logged_in'])) {

            $cookie = Request::COOKIE('remember_me', true);

            list($user_id, $hash, $token) = explode(":", $cookie);

            $construct = $token . $this->cookie_secret;
            $construct = hash('sha256', $construct);

            if (is_numeric($user_id) && $hash == $construct) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count,user_id,user_name,user_email FROM users WHERE rememberme_token = ? LIMIT 1");
                $stmt->bind_param("s", $user_name);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

                if ($user['count'] > 0.99) {

                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['user_name'];
                    $_SESSION['user_email'] = $user['user_email'];
                    $_SESSION['user_logged_in'] = 1;

                    $rememberme_token = hash('sha256', mt_rand());
                    $hash = $rememberme_token . $this->cookie_secret;
                    $token = $user['user_id'] . ":" . hash('sha256', $hash) . ":" . $rememberme_token;
                    setcookie('remember_me', $token, time() + $this->sessionTime, "/", Config::fetch('COOKIE_DOMAIN'));

                    $stmt = $fernico_db->stmt_init();
                    $stmt->prepare("UPDATE users SET rememberme_token = ? WHERE user_name = ?");
                    $stmt->bind_param("ss", $rememberme_token, $user['user_name']);
                    $stmt->execute();
                    $stmt->close();

                } else {

                    $_SESSION = array();
                    session_destroy();

                    unset($_COOKIE['remember_me']);
                    setcookie('remember_me', "", time() - $this->sessionTime * 2, "/", Config::fetch('COOKIE_DOMAIN'));

                }

            } else {

                $_SESSION = array();
                session_destroy();

                unset($_COOKIE['remember_me']);
                setcookie('remember_me', "", time() - $this->sessionTime * 2, "/", Config::fetch('COOKIE_DOMAIN'));

            }

        }

    }

    /*
     * This function deletes the remember_me cookie from user browser and destroys the user session.
     */

    public function logout() {

        if (Request::COOKIE('remember_me') != null) {
            unset($_COOKIE['remember_me']);
            setcookie('remember_me', "", time() - $this->sessionTime * 2, "/", Config::fetch('COOKIE_DOMAIN'));
        }

        $_SESSION = array();
        session_destroy();
        return true;

    }

    /*
     * This function validates the user inputs and logs them in.
     */

    public function login($user_name, $password) {

        $user_name = Request::cleanInput($user_name);
        $password = Request::cleanInput($password);

        $stmt = $fernico_db->stmt_init();
        $stmt->prepare("SELECT COUNT(user_id) as count,user_id,user_name,password_hash,user_email,user_verified,failed_logins,last_failed_login FROM users WHERE user_name = ? LIMIT 1");
        $stmt->bind_param("s", $user_name);
        $stmt->execute();
        $data = $stmt->get_result();
        $stmt->close();
        $data = $data->fetch_assoc();

        if (Config::fetch('GOOGLE_RECAPTCHA') == true && $this->verifyCaptcha() == false) {

            return "ER_CAPTCHA_INVALID";

        } elseif (strlen($user_name) < 3) {

            return "ER_USER_NAME_SHORT";

        } elseif (strlen($user_name) > 16) {

            return "ER_USER_NAME_LONG";

        } elseif (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $user_name)) {

            return "ER_USER_NAME_CONTAINS_SPECIAL_CHARACTERS";

        } elseif (strlen($password) < 6) {

            return "ER_PASSWORD_SHORT";

        } elseif (strlen($password) > 64) {

            return "ER_PASSWORD_LONG";

        } elseif ($data['count'] < 0.99) {

            return "ER_USER_NOT_FOUND";

        } elseif (!password_verify($password, $user['password_hash'])) {

            return "ER_PASSWORD_INCORRECT";

            $last_failed_login = time();
            $fernico_db->query("UPDATE users SET failed_logins = failed_logins + 1, last_failed_login = {$last_failed_login} WHERE user_id = {$data['user_id']}");

        } elseif ($user['user_verified'] == 0) {

            return "ER_ACCOUNT_NOT_VERIFIED";

        } elseif ($user['failed_logins'] >= 6 && $user['last_failed_login'] > (time() - 900)) {

            return "ER_TOO_MANY_ATTEMPTS";

        } else {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_email'] = $user['user_email'];
            $_SESSION['user_logged_in'] = 1;

            if (Request::POST('remember_me') == null) {

                $rememberme_token = null;

            } else {

                $rememberme_token = hash('sha256', mt_rand());
                $hash = $rememberme_token . $this->cookie_secret;
                $token = $user['user_id'] . ":" . hash('sha256', $hash) . ":" . $rememberme_token;
                setcookie('remember_me', $token, time() + $this->sessionTime, "/", Config::fetch('COOKIE_DOMAIN'));

            }

            $datetime = date("Y-m-d H:i:s");
            $fernico_db->query("UPDATE users SET last_logged_in = '{$datetime}', failed_logins = 0, rememberme_token = {$rememberme_token} WHERE user_id = {$data['user_id']}");

            return true;

        }

    }

    /*
     * This function can be used to verify Google Recaptcha captcha response.
     */

    public function verifyCaptcha() {

        $recaptcha_secret = Config::fetch('RECAPTCHA_SECRET_KEY');
        $response = fernico_get("https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptcha_secret . "&response=" . Request::POST('g-recaptcha-response'));
        $response = json_decode($response, true);

        if ($response["success"] === true) {

            return true;

        } else {

            return false;

        }

    }

    /*
     * This function registers the user.
     */

    public function register($user_name, $user_email, $password, $password_repeat) {

        $user_name = Request::cleanInput($user_name);
        $user_email = Request::cleanInput($user_email);
        $password = Request::cleanInput($password);
        $password_repeat = Request::cleanInput($password_repeat);

        $stmt = $fernico_db->stmt_init();
        $stmt->prepare("SELECT COUNT(user_id) as count FROM users WHERE user_name = ? LIMIT 1");
        $stmt->bind_param("s", $user_name);
        $stmt->execute();
        $data = $stmt->get_result();
        $stmt->close();
        $confirm_user = $data->fetch_assoc();

        $stmt = $fernico_db->stmt_init();
        $stmt->prepare("SELECT COUNT(user_id) as count FROM users WHERE user_email = ? LIMIT 1");
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $data = $stmt->get_result();
        $stmt->close();
        $confirm_email = $data->fetch_assoc();

        if (Config::fetch('GOOGLE_RECAPTCHA') == true && $this->verifyCaptcha() == false) {

            return "ER_CAPTCHA_INVALID";

        } elseif (strlen($user_name) < 3) {

            return "ER_USER_NAME_SHORT";

        } elseif (strlen($user_name) > 16) {

            return "ER_USER_NAME_LONG";

        } elseif (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $user_name)) {

            return "ER_USER_NAME_CONTAINS_SPECIAL_CHARACTERS";

        } elseif ($user_email == "") {

            return "ER_EMAIL_BLANK";

        } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {

            return "ER_EMAIL_INVALID";

        } elseif (strlen($password) < 6) {

            return "ER_PASSWORD_SHORT";

        } elseif (strlen($password) > 64) {

            return "ER_PASSWORD_LONG";

        } elseif ($password != $password_repeat) {

            return "ER_PASSWORD_REPEATING_NOT_MATCHING";

        } elseif ($confirm_user['count'] > 0.99) {

            return "ER_USER_ALREADY_EXISTS";

        } elseif ($confirm_email['count'] > 0.99) {

            return "ER_EMAIL_ALREADY_EXISTS";

        } else {

            if (Config::fetch('EMAIL_CONFIRMATION') == true) {

                $user_verified = 0;
                $activation_hash = hash('sha256', mt_rand());
                $activation_link = fernico_getAbsURL() . Config::fetch('CONFIRMATION_CONTROLLER') . '/' . Config::fetch('CONFIRMATION_ACTION') . '/' . $activation_hash;
                $subject = "Confirm your email at " . Config::fetch('WEBSITE_NAME');

                $handle = fopen(FERNICO_PATH . '/lib/Authentication/ActivationEmail.txt', "r");
                $body = fread($handle, filesize(FERNICO_PATH . '/lib/Authentication/ActivationEmail.txt'));
                fclose($handle);
                $body = str_replace(array('{$activation_link}', '{$website_name}'),
                    array($activation_link, Config::fetch('WEBSITE_NAME')), $body);

                $this->sendMail($user_email, $subject, $body);

            } else {

                $user_verified = 1;
                $activation_hash = null;

            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT, array('cost' => 12));
            $registration_datetime = date("Y-m-d H:i:s");
            $registration_ip = get_IP();

            $stmt = $fernico_db->stmt_init();
            $stmt->prepare("INSERT INTO users (user_name,user_email,password_hash,user_verified,activation_hash,registration_datetime,registration_ip) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("s", "sssisss", $user_name, $user_email, $password_hash, $user_verified, $activation_hash,
                $registration_datetime, $registration_ip);
            $stmt->execute();
            $stmt->close();

            return true;

        }

    }

    /*
     * A quick function to shoot emails.
     */

    public function sendMail($email, $subject, $body) {

        $mail = new PHPMailer();
        if (Config::fetch('USE_SMTP')) {
            $mail->IsSMTP();
            $mail->SMTPAuth = Config::fetch('SMTP_AUTH');
            if (Config::fetch('EMAIL_SMTP_ENCRYPTION') != "" || Config::fetch('EMAIL_SMTP_ENCRYPTION') != null) {
                $mail->SMTPSecure = Config::fetch('EMAIL_SMTP_ENCRYPTION');
            }
            $mail->Host = Config::fetch('EMAIL_SMTP_HOST');
            $mail->Username = Config::fetch('EMAIL_SMTP_USERNAME');
            $mail->Password = Config::fetch('EMAIL_SMTP_PASSWORD');
            $mail->Port = Config::fetch('EMAIL_SMTP_PORT');
        } else {
            $mail->IsMail();
        }

        $mail->Subject = $subject;
        $mail->SMTPDebug = false;
        $mail->do_debug = 0;
        $mail->MsgHTML($body);
        $mail->AddAddress($email);
        $mail->Send();
        unset($mail);

    }

    /*
     * Enables you to confirm user emails.
     */

    public function confirmEmail($activation_link) {

        $stmt = $fernico_db->stmt_init();
        $stmt->prepare("SELECT COUNT(user_id) as count, user_id FROM users WHERE activation_hash = ? AND user_verified = 0 LIMIT 1");
        $stmt->bind_param("s", $activation_hash);
        $stmt->execute();
        $data = $stmt->get_result();
        $stmt->close();
        $confirm_email = $data->fetch_assoc();

        if ($confirm_email['count'] > 0.99) {

            $fernico_db->query("UPDATE users SET activation_hash = null, user_verified = 1 WHERE user_id = {$confirm_email['user_id']}");

            return true;

        } else {

            return false;

        }

    }

    /*
     * Enables you to send reset password emails.
     */

    public function sendPasswordResetEmail($user_name) {

        $user_name = Request::cleanInput($user_name);

        $stmt = $fernico_db->stmt_init();
        $stmt->prepare("SELECT COUNT(user_id) as count,user_id,user_email FROM users WHERE user_name = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $data = $stmt->get_result();
        $stmt->close();
        $user = $data->fetch_assoc();

        if (Config::fetch('GOOGLE_RECAPTCHA') == true && $this->verifyCaptcha() == false) {

            return "ER_CAPTCHA_INVALID";

        } elseif (strlen($user_name) < 3) {

            return "ER_USER_NAME_SHORT";

        } elseif (strlen($user_name) > 16) {

            return "ER_USER_NAME_LONG";

        } elseif ($user['count'] < 0.99) {

            return "ER_USER_NAME_INVALID";

        } else {

            $reset_hash = hash('sha256', mt_rand());
            $user_id = $user['user_id'];
            $email = $user['user_email'];
            $resetLink = fernico_getAbsURL() . Config::fetch('RESET_PASSWORD_CONTROLLER') . '/' . Config::fetch('RESET_PASSWORD_ACTION') . '/' . $reset_hash;

            $fernico_db->query("UPDATE users SET reset_hash = '{$reset_hash}' WHERE user_id = {$user_id}");

            $subject = "Reset your password at " . Config::fetch('WEBSITE_NAME');
            $handle = fopen(FERNICO_PATH . '/lib/Authentication/PasswordResetEmail.txt', "r");
            $body = fread($handle, filesize(FERNICO_PATH . '/lib/Authentication/PasswordResetEmail.txt'));
            fclose($handle);
            $body = str_replace(array('{$resetLink}', '{$website_name}'),
                array($resetLink, Config::fetch('WEBSITE_NAME')), $body);

            sendMail($email, $subject, $body);

            return true;

        }

    }

    public function isValidResetLink() {

        $reset_hash = Request::GET("hash", true);

        $stmt = $fernico_db->stmt_init();
        $stmt->prepare("SELECT COUNT(user_id) as count FROM users WHERE reset_hash = ? AND reset_hash <> '' LIMIT 1");
        $stmt->bind_param("s", $reset_hash);
        $stmt->execute();
        $data = $stmt->get_result();
        $stmt->close();
        $user = $data->fetch_assoc();

        if ($user['count'] > 0.99) {

            return true;

        } else {

            return false;

        }


    }

    /*
     * Sets new password using the hash provided password reset link.
     */

    public function setNewPassword($hash, $password, $password_repeat) {

        $reset_hash = Request::cleanInput($hash);

        $stmt = $fernico_db->stmt_init();
        $stmt->prepare("SELECT COUNT(user_id) as count, user_id FROM users WHERE reset_hash = ? AND reset_hash <> '' LIMIT 1");
        $stmt->bind_param("s", $reset_hash);
        $stmt->execute();
        $data = $stmt->get_result();
        $stmt->close();
        $user = $data->fetch_assoc();

        if ($password == $password_repeat && $user['count'] > 0.99) {

            $password_hash = password_hash($password, PASSWORD_DEFAULT, array('cost' => 12));

            $fernico_db->query("UPDATE users SET password_hash = '{$password_hash}', reset_hash = null WHERE user_id = {$user['user_id']}");

            return true;

        } else {

            return false;

        }

    }

    /*
     * Resends the activation email.
     */

    public function resendActivationEmail($user_name) {

        $stmt = $fernico_db->stmt_init();
        $stmt->prepare("SELECT COUNT(user_id) as count,user_id,user_email,activation_hash FROM users WHERE user_name = ? AND user_verified = 0 LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $data = $stmt->get_result();
        $stmt->close();
        $user = $data->fetch_assoc();

        if (Config::fetch('GOOGLE_RECAPTCHA') == true && $this->verifyCaptcha() == false) {

            return "ER_CAPTCHA_INVALID";

        } elseif (strlen($user_name) < 3) {

            return "ER_USER_NAME_SHORT";

        } elseif (strlen($user_name) > 16) {

            return "ER_USER_NAME_LONG";

        } elseif ($user['count'] < 0.99) {

            return "ER_USER_NAME_INVALID";

        } else {

            $email = $user['user_email'];
            $activation_hash = $user['activation_hash'];
            $activation_link = fernico_getAbsURL() . Config::fetch('CONFIRMATION_CONTROLLER') . '/' . Config::fetch('CONFIRMATION_ACTION') . '/' . $activation_hash;
            $subject = "Confirm your email at " . Config::fetch('WEBSITE_NAME');

            $handle = fopen(FERNICO_PATH . '/lib/Authentication/ActivationEmail.txt', "r");
            $body = fread($handle, filesize(FERNICO_PATH . '/lib/Authentication/ActivationEmail.txt'));
            fclose($handle);
            $body = str_replace(array('{$activation_link}', '{$website_name}'),
                array($activation_link, Config::fetch('WEBSITE_NAME')), $body);

            sendMail($email, $subject, $body);

            return true;

        }


    }

    /*
     * Change the password, fair enough.
     */

    public function changePassword($password, $password_repeat) {

        if ($password == $password_repeat && $this->UserLoggedIn() == true) {

            $password_hash = password_hash($password, PASSWORD_DEFAULT, array('cost' => 12));

            $fernico_db->query("UPDATE users SET password_hash = '{$password_hash}' WHERE user_id = {$_SESSION['user_id']}");

            return true;

        } else {

            return false;

        }


    }

    /*
     * Is the user logged in? This function lets you know.
     */

    public function UserLoggedIn() {

        if (!empty($_SESSION['user_name']) && ($_SESSION['user_logged_in'] == 1)) {

            return true;

        } else {

            return false;

        }

    }

    /*
     * Change your user email with this method.
     */

    public function changeEmail($user_email) {

        if ($user_email == "") {

            return "ER_EMAIL_BLANK";

        } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {

            return "ER_EMAIL_INVALID";

        } elseif ($email == $_SESSION['user_email']) {

            return "ER_SAME_EMAIL";

        } else {

            $confirm_code = hash('sha256', mt_rand());
            $change_link = fernico_getAbsURL() . Config::fetch('CHANGE_EMAIL_CONTROLLER') . '/' . Config::fetch('CONFIRMATION_ACTION') . '/' . $activation_hash;

            $stmt = $fernico_db->stmt_init();
            $stmt->prepare("UPDATE email_updates SET email = ?, confirm_code = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $email, $confirm_code, $_SESSION['user_id']);
            $stmt->execute();
            $r = $stmt->affected_row;
            $stmt->close();

            if ($r == 0) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("INSERT INTO email_updates (user_id,email,confirm_code) VALUES (?,?,?)");
                $stmt->bind_param("iss", $_SESSION['user_id'], $email, $confirm_code);
                $stmt->execute();
                $stmt->close();

            }

            $subject = "Confirm your email change";
            $handle = fopen(FERNICO_PATH . '/lib/Authentication/EmailChange.txt', "r");
            $body = fread($handle, filesize(FERNICO_PATH . '/lib/Authentication/EmailChange.txt'));
            fclose($handle);
            $body = str_replace(array('{$change_link}', '{$website_name}'),
                array($change_link, Config::fetch('WEBSITE_NAME')), $body);

            sendMail($email, $subject, $body);

            return true;

        }
    }

    /*
     * Confirm user email change by providing the hash.
     */

    public function confirmEmailChange($hash) {

        $hash = Request::cleanInput($hash);

        $stmt = $fernico_db->stmt_init();
        $stmt->prepare("SELECT COUNT(id) as count,id,user_id,email FROM email_updates WHERE confirm_code = ?");
        $stmt->bind_param("s", $hash);
        $stmt->execute();
        $data = $stmt->get_result();
        $stmt->close();
        $user = $data->fetch_assoc();

        if ($user['count'] < 0.99) {

            return "ER_INVALID_EMAIL_CHANGE_LINK";

        } else {

            $_SESSION['user_email'] = $data['email'];
            $fernico_db->query("UPDATE users SET user_email = '{$data['email']}' WHERE user_id = {$user['user_id']}");
            $fernico_db->query("DELETE FROM email_updates WHERE id = {$user['id']}");

            return true;

        }

    }

    /*
     * Echoes the Google Recaptcha widget.
     */

    public function vomitRecaptcha() {

        if (Config::fetch('GOOGLE_RECAPTCHA') == 1) {

            return "<div id='captcha'></div>
                    <script>
                    var onloadCallback = function() {
                    grecaptcha.render('captcha', {
                    'sitekey': '" . Config::fetch('RECAPTCHA_SITE_KEY') . "',
                    'hl': 'en-GB'
                    });
                    };
                    </script>                        
                    <script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit' async defer></script>";

        }


    }


}

