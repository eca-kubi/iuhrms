<?php

use JetBrains\PhpStorm\NoReturn;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use function React\Promise\resolve;

abstract class Helpers
{
    public static function log_error(string $message): void
    {
        $log = new Logger('error');
        $log->pushHandler(new StreamHandler(dirname(__FILE__, 3) . '/logs/PHP_errors.log', Logger::ERROR));
        $log->error($message);
    }

    public static function log_info(string $message): void
    {
        $log = new Logger('info');
        $log->pushHandler(new StreamHandler(dirname(__FILE__, 3) . '/logs/PHP_info.log', Logger::INFO));
        $log->info($message);
    }

    public static function encrypt_otp(string $otp): string
    {
        return password_hash($otp, PASSWORD_DEFAULT);
    }

    public static function add_to_session(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @throws Exception
     */
    public static function fetch_from_session(string $key): mixed
    {
        if (!isset($_SESSION[$key])) {
            throw new Exception(ExceptionType::INVALID_SESSION_KEY);
        }
        return $_SESSION[$key];
    }

    public static function verify_otp(string $otp): bool
    {
        // Has the OTP expired?
        if (time() > self::fetch_from_session(SessionKeys::OTP_EXPIRY)) {
            return false;
        }
        return password_verify($otp, self::fetch_from_session(SessionKeys::OTP));
    }

    public static function is_logged_in(): bool
    {
        return isset($_SESSION['logged_in_user']);
    }

    // Get role from session and check if it is admin
    public static function is_admin(): bool
    {
        return self::getLoggedInUserRole() == 'admin';
    }

    // Get role from session and check if it is student
    public static function is_student(): bool
    {
        return self::getLoggedInUserRole() == 'student';
    }

    #[NoReturn] public static function redirect(string $relativeURL = ''): void
    {
        $url = self::parseURL($relativeURL);
        header('Location: ' . $url);
        exit;
    }

    #[NoReturn] public static function redirectTo($url): void
    {
        header('Location: ' . $url);
        exit;
    }

    public static function redirect_with_params(string $controller, string $method = '', string $params = ''): void
    {
        $url = self::parseURL($controller, $method) . '?' . $params;
        header('location: ' . $url);
    }

    public static function get_request_method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function is_post_method(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public static function is_get_method(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    public static function concat_string(string $separator, ...$args): string
    {
        $args = func_get_args();
        $parts = array_slice($args, 1);
        return implode($separator, $parts);
    }

    public static function parseURL(string ...$url_parts): string
    {
        $url_parts = func_get_args();
        $processed_args = array_map(fn($arg) => trim($arg, '/'), $url_parts);
        $url_root = rtrim(URL_ROOT, '/');
        return $url_root . '/' . implode('/', $processed_args);
    }

    /**
     * @throws Exception
     */
    public static function fetch_post_data(string $field): string
    {
        // Check if $field is set in $_POST
        if (!isset($_POST[$field])) {
            throw new Exception(ExceptionType::INVALID_POST_DATA_FIELD . ' ' . $field );
        }
        // Validate post data using self::validate_post_data() function
        $validated = self::validate_post_data($field);
        if ($validated === null) {
            throw new Exception(ExceptionType::INVALID_POST_DATA);
        }
        return $validated;
    }

    /**
     * @throws Exception
     */
    public static function fetch_get_data(string $field): string
    {
        if (self::validate_get_data($field) === false) {
            throw new Exception(ExceptionType::INVALID_GET_DATA);
        }
        if (isset($_GET[$field])) {
            return trim(strip_tags(htmlspecialchars($_GET[$field])));
        }
        throw new Exception(ExceptionType::INVALID_GET_DATA_FIELD . ' ' . $field);
    }

    public static function validate_post_data(string $field)
    {
        if ($field === 'email') {
            // Sanitize email
            $email = trim(filter_input(INPUT_POST, $field, FILTER_SANITIZE_EMAIL));
            // Validate email
            return filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE);
        }
        // Sanitize other fields
        return filter_input(INPUT_POST, $field, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
    }

    public static function validate_get_data(string $field)
    {
        if ($field == 'email') {
            $email = filter_input(INPUT_GET, $field, FILTER_VALIDATE_EMAIL);
            return filter_var($email, FILTER_SANITIZE_EMAIL);
        }
        return filter_input(INPUT_GET, $field, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    public static function flash_success(string $title, ?string $message = "Success!"): void
    {
        self::flash(title: $title, message: $message, type: FlashType::SUCCESS);
    }

    public static function flash_error(string $title, ?string $message = "Error!"): void
    {
        self::flash(title: $title, message: $message, type: FlashType::ERROR);
    }

    public static function flash_info(string $title, ?string $message = "Info!"): void
    {
        self::flash(title: $title, message: $message, type: FlashType::INFO);
    }

    /**
     * Flash message helper.
     *
     * EXAMPLE: flash('register_success', 'You are now registered');
     *
     * DISPLAY IN VIEW: echo flash('register_success');
     * @param string $title
     * @param string|null $message
     * @param string $type
     * @param string|null $class
     * @return void
     */
    public static function flash(string $title, ?string $message = null, string $type = FlashType::INFO, ?string $class = null): void
    {
        $class = $class ?? 'text-sm text-center alert alert-' . $type; // default class
        $template = <<<html
        <div class="col-md-12 p-2">
        <div class="$class col-md-12 m-auto" role="alert">
        <button type="button" class="close" data-dismiss="alert" style="position: absolute;top: 10%;left: 95%;" aria-label="Close"><span aria-hidden="true" >&times;</span><span class="sr-only">Close</span></button>
        <p>$message</p>
        </div>
        </div>
html;
        if (!isset($_SESSION[$title]) && !empty($message)) {
            $_SESSION[$title] = $template;
        } else if (isset($_SESSION[$title]) && empty($message)) {
            echo $_SESSION[$title];
            unset($_SESSION[$title]);
        }
    }


    public static function isLoggedIn(): bool
    {
        if (isset($_SESSION['logged_in_user'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws Exception
     */
    public static function generate_otp(): string
    {
        return substr(bin2hex(random_bytes(3)), 0, 5);
    }

    /**
     * @throws Exception
     */
    public static function send_email(EmailModel $email, ?PHPMailer $mailer = null): bool
    {

        // Set up the PHPMailer object
        $mail = $mailer ?? self::configureMailer();
        // Turn on SMTP debugging
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        try {
            // Set recipient
            $mail->addAddress($email->recipient_address);

            // Set the subject and message body
            $mail->Subject = $email->subject;
            $mail->Body = $email->body;

            // Send the email
            $mail->send();

            return true;
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $mail->getSMTPInstance()->reset();
            throw new Exception(ExceptionType::MAILER_ERROR);
        }
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function configureMailer(): PHPMailer
    {
        $mailer = new PHPMailer(true);
        $mailer->SMTPDebug = SMTP::DEBUG_CLIENT;
        // send debug output to log file
        $mailer->Debugoutput = function ($str, $level) {
            // Use Helpers::logInfo() to log the debug output to avoid sending multiple headers to the client
            Helpers::log_info($str);
        };
        $mailer->isSMTP();
        $mailer->Host = EMAIL_SMTP_HOST;
        $mailer->SMTPAuth = true;
        $mailer->Username = EMAIL_SENDER_ADDRESS;
        $mailer->Password = EMAIL_CLIENT_APP_PASSWORD;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = EMAIL_SMTP_PORT;
        // Set the sender
        $mailer->setFrom(EMAIL_SENDER_ADDRESS, EMAIL_SENDER_NAME);

        return $mailer;
    }


    public static function destroy_session(): bool
    {
        // Clear session data and destroy session
        $_SESSION = [];
        return session_destroy();
    }


    /**
     * @throws Exception
     */
    public static function send_otp_to_email($email, $otp): void
    {
        // Create child process using pcntl_fork to send OTP to email asynchronously
        $pid = pcntl_fork();
        if ($pid === -1) {
            // Error: failed to fork
            // log error
            Helpers::log_error("Failed to fork child process to send OTP to $email");
        } else if ($pid === 0) {
            // Child process: start the event loop and send the email
            // Create an event loop
            $loop = React\EventLoop\Loop::get();
            // create email model
            $email_model = EmailModel::factory([
                EmailModelSchema::RECIPIENT_ADDRESS => $email,
                EmailModelSchema::SUBJECT => 'Login Code',
                EmailModelSchema::BODY => "Your login code is: $otp"
            ]);
            // send email asynchronously and use the event loop to wait for the promise to resolve
            $promise = resolve(Helpers::send_email($email_model));
            $promise->then(function ($value) use ($email) {
                if ($value) {
                    // log success
                    Helpers::log_info("OTP sent to $email");
                } else {
                    // log error
                    Helpers::log_error("Failed to send OTP to $email");
                }
            })->otherwise(function ($reason) use ($email) {
                // log error
                Helpers::log_error("Failed to send OTP to $email\n" . $reason);
            })->always(function () use ($loop) {
                // Stop the event loop
                $loop->stop();
            });

            // Start the event loop
            $loop->run();

            // Properly end the child process using posix_kill
            posix_kill(posix_getpid(), SIGTERM);
        } /*else {
            // Parent process: continue executing the main application
            // Add to encrypted otp to session
            $otp_encrypted = Helpers::encryptOTP($otp);
            Helpers::add_to_session(SessionKeys::OTP_ENCRYPTED, $otp_encrypted);
        }*/
        // close current session
        // session_write_close();
    }

    // Function to send json_encoded output. Use JSON_PRETTY_PRINT. Example:
    #[NoReturn] public static function sendJson(array $data, int $status = 200, bool $pretty = true): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data, $pretty ? JSON_PRETTY_PRINT : 0);
        exit;
    }

    // Function to get the currently logged-in user from session. Returns false if no user is logged in.
    public static function getLoggedInUser(): UserModel|bool
    {
        return $_SESSION['logged_in_user'] ?? false;
    }

    // Function to get the currently logged-in user's id from session. Returns false if no user is logged in.
    public static function getLoggedInUserId(): int|bool
    {
        return $_SESSION['logged_in_user']->id ?? false;
    }

    // Function to get the currently logged-in user's role from session. Returns false if no user is logged in.
    public static function getLoggedInUserRole(): string|bool
    {
        return $_SESSION['logged_in_user']->role ?? false;
    }


    #[NoReturn] public static function logout(): void
    {
        // Destroy session
        Helpers::destroy_session();
    }

    /**
     * @throws Exception
     */
    public static function get_from_session(string $key): mixed
    {
        // Check if $key is set in $_SESSION
        if (!isset($_SESSION[$key])) {
            throw new Exception(ExceptionType::INVALID_SESSION_KEY);
        }
        return $_SESSION[$key];
    }

    public static function is_valid_otp(string $otp): bool
    {
        return self::verify_otp($otp);
    }

    public static function remove_from_session(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * @throws Exception
     */
    public static function login(string $email, bool $verify_otp): void
    {
        // Todo: Check if email exists in database
        /*if (!UserModel::emailExists($email)) {
            echo json_encode(array('success' => false, 'message' => 'Email address not found!'));
            return;
        }*/

        if(!$verify_otp) {

            // Add email to session
            Helpers::add_to_session(SessionKeys::EMAIL, $email);

            // Generate a new OTP
            $otp = Helpers::generate_otp();

            // Send otp to email
            Helpers::send_otp_to_email($email, $otp);

            // Add encrypted otp to session
            Helpers::add_to_session(SessionKeys::OTP, Helpers::encrypt_otp($otp));

            // Add otp expiry to session. OTP expires in 5 minutes
            Helpers::add_to_session(SessionKeys::OTP_EXPIRY, time() + 5 * 60);

            // Send response to client
            echo json_encode(array('success' => true, 'message' => 'We have sent a login code to your email address.'));
        } else {
            // Verify user submitted OTP
            // Get OTP from POST data
            $otp = Helpers::fetch_post_data('otp');

            // Check if OTP is valid
            if (!Helpers::is_valid_otp($otp)) {
                echo json_encode(array('success' => false, 'message' => 'Invalid or Expired OTP!'));
                return;
            }
            // Get email from session
            $email = Helpers::get_from_session(SessionKeys::EMAIL);

            // Todo: Get user details from database
            // $user = UserModel::getUserByEmail($email);

            // Todo: Add user details to session
//            Helpers::add_to_session(SessionKeys::USER_ID, $user->id);
//            Helpers::add_to_session(SessionKeys::USER_NAME, $user->name);
//            Helpers::add_to_session(SessionKeys::USER_EMAIL, $user->email);

            // Set is logged in to true
            Helpers::add_to_session(SessionKeys::LOGGED_IN_USER, true);

            // Send response to client
            echo json_encode(array('success' => true, 'message' => 'Login successful!'));

            // Clear otp from session
            Helpers::remove_from_session(SessionKeys::OTP);

            // Clear otp expiry from session
            Helpers::remove_from_session(SessionKeys::OTP_EXPIRY);
        }
    }

    private static function is_async_supported(): bool
    {
        return extension_loaded('pcntl') && extension_loaded('posix');
    }

    public static function is_post(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public static function is_get(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    public static function is_ajax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }


}
