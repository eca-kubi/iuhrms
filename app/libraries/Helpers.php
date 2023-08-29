<?php

use JetBrains\PhpStorm\NoReturn;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use function React\Promise\resolve;

abstract class Helpers
{
    /**
     * Logs an error message to the errors log file
     * @param string $message
     * @return void
     */
    public static function log_error(string $message): void
    {
        $log = new Logger('error');
        $log->pushHandler(new StreamHandler(ERROR_LOG_FILE, Logger::ERROR));
        $log->error($message);
    }

    /**
     * Logs an info message to the info log file
     * @param string $message
     * @return void
     *
     */
    public static function log_info(string $message): void
    {
        $log = new Logger('info');
        $log->pushHandler(new StreamHandler(INFO_LOG_FILE, Logger::INFO));
        $log->info($message);
    }

    /**
     * Encrypts the given OTP
     * @param string $otp
     * @return string
     */
    public static function encrypt_otp(string $otp): string
    {
        return password_hash($otp, PASSWORD_DEFAULT);
    }

    /**
     * Verifies the given OTP
     * @throws Exception
     */
    public static function verify_otp(string $otp): bool
    {
        // Has the OTP expired?
        if (time() > self::fetch_session_data(SessionKeys::OTP_EXPIRY)) {
            return false;
        }
        return password_verify($otp, self::fetch_session_data(SessionKeys::OTP));
    }

    /**
     * Adds the given value to the session
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function add_to_session(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Fetches the value of the given key from the session
     * @throws Exception
     */
    public static function fetch_session_data(string $key): mixed
    {
        if (!isset($_SESSION[$key])) {
            throw new Exception(ExceptionType::INVALID_SESSION_KEY);
        }
        return $_SESSION[$key];
    }

    public static function is_logged_in(): bool
    {
        // Logged_in_user is set and not null
        try {
            $logged_in_user = self::get_logged_in_user();
            return !is_null($logged_in_user);
        } catch (Exception $e) {
            self::log_error($e->getMessage());
            return false;
        }
    }

    /**
     * Checks if the logged-in user is an admin
     */
    public static function is_admin(): bool
    {
        try {
            $logged_in_user = self::get_logged_in_user();
            if (is_null($logged_in_user)) {
                return false;
            }
            return $logged_in_user->isAdmin();
        } catch (Exception $e) {
            self::log_error($e->getMessage());
            return false;
        }
    }

    /*    #[NoReturn] public static function redirect(string $relativeURL = ''): void
        {
            $url = self::parseURL($relativeURL);
            header('Location: ' . $url);
            exit;
        }*/

    #[NoReturn] public static function redirect_to($url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /*    public static function redirect_with_params(string $controller, string $method = '', string $params = ''): void
        {
            $url = self::parseURL($controller, $method) . '?' . $params;
            header('location: ' . $url);
        }*/

    public static function concat_string(string $separator, ...$args): string
    {
        $args = func_get_args();
        $parts = array_slice($args, 1);
        return implode($separator, $parts);
    }

    /**
     * @throws Exception
     */
    public static function fetch_post_data(string $field): string
    {
        // Check if $field is set in $_POST
        if (!isset($_POST[$field])) {
            throw new Exception(ExceptionType::INVALID_POST_DATA_FIELD . ' ' . $field);
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
     * EXAMPLE: flash('register_success' 'You are now registered');
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

    /**
     * Generates a random string
     * @throws Exception
     */
    public static function generate_otp(): string
    {
        return substr(bin2hex(random_bytes(3)), 0, 5);
    }

    /**
     * Sends an email using the given PHPMailer object or creates a new one if none is provided
     * @throws Exception
     */
    public static function send_email(EmailModel $email, ?PHPMailer $mailer = null): bool
    {

        // Set up the PHPMailer object
        $mail = $mailer ?? self::configure_mailer();
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
     * Configures the PHPMailer object for sending emails
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function configure_mailer(): PHPMailer
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


    /**
     *  Destroys the current session
     */
    public static function destroy_session(): bool
    {
        // Clear session data and destroy session
        $_SESSION = [];
        return session_destroy();
    }


    /**
     * Sends an OTP to the given email address
     * @throws Exception
     */
    public static function send_otp_to_email(string $email, string $otp): void
    {
        // Create child process using pcntl_fork to send OTP to email asynchronously and i
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
            $email_model = new EmailModel([
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
            //posix_kill(posix_getpid(), SIGTERM);
            exit;
        }
    }

    #[NoReturn] public static function logout(): void
    {
        // Destroy session
        Helpers::destroy_session();
    }

    public static function remove_from_session(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Logs in a user using their email and OTP
     * @throws Exception
     */
    public static function login(string $email, bool $verify_otp): void
    {
        // Check if email exists in database
        if (!UserModel::emailExists($email)) {
            echo json_encode(array('success' => false, 'message' => 'Email address not found!'));
            return;
        }
        if (!$verify_otp) {

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
            echo json_encode(array('success' => true, 'message' => 'A login code should be sent to your email address shortly! <br /> Please check your email and enter the code below to login!'));
        } else {
            // Verify user submitted OTP
            // Get OTP from POST data
            $otp = Helpers::fetch_post_data('otp');

            // Check if OTP is valid
            if (!Helpers::verify_otp($otp)) {
                echo json_encode(array('success' => false, 'message' => 'Invalid or Expired OTP!'));
                return;
            }
            // Get email from session
            $email_from_session = Helpers::fetch_session_data(SessionKeys::EMAIL);
            // Check if email from session is same as email from POST data
            if ($email_from_session !== $email) {
                echo json_encode(array('success' => false, 'message' => 'Invalid email address!'));
                return;
            }

            // Get user details from database
            $user = UserModel::getUserByEmail($email);

            // Set is logged in to true
            Helpers::add_to_session(SessionKeys::LOGGED_IN_USER, $user);

            // Send response to client
            echo json_encode(array('success' => true, 'message' => 'Login successful!'));

            // Clear otp from session
            Helpers::remove_from_session(SessionKeys::OTP);

            // Clear otp expiry from session
            Helpers::remove_from_session(SessionKeys::OTP_EXPIRY);
        }
    }

    /**
     * Gets the logged-in user from the session
     */
    public static function get_logged_in_user(): UserModel|null
    {
        try {
            return Helpers::fetch_session_data(SessionKeys::LOGGED_IN_USER);
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            return null;
        }
    }

    /**
     *  Returns a json encoded string of the data
     * @param array $data
     * @return string|false
     */
    public static function json_encode(array $data): string|false
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    #[NoReturn]
    public static function redirect_to_404(): void
    {
        header('Location: ' . URL_ROOT . '/errors/index/404', response_code: 302); // 302 is used to redirect browsers to the 404 page
        exit;
    }

    /**
     * Sends an HTTP response with the given status code and message
     */
    public static function http_response_code(int $code, string $message = null, string $contentType = 'application/json'): void
    {
        // Get the HTTP status text that corresponds to the code
        $statusText = self::getHttpStatusText($code);

        // Set the HTTP response code and status text
        header("HTTP/1.1 $code $statusText", true, $code);

        // Set the content type
        header("Content-Type: $contentType");

        // If a custom message is provided, send it as the response body
        if ($message !== null) {
            echo $message;
        }
    }

    /**
     * Returns the HTTP status text that corresponds to the given code
     */
    private static function getHttpStatusText(int $code): string
    {
        $statusTexts = [
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            // Add other status codes as needed
        ];

        return $statusTexts[$code] ?? 'Unknown Status';
    }

    public static function is_valid_date(string $datetime): bool
    {
        return strtotime($datetime) !== false;
    }

    /**
     * Gets the content type of the request
     * @return string
     */
    public static function get_content_type(): string
    {
        return $_SERVER['CONTENT_TYPE'];
    }

    /**
     * Get the request body as an associative array if the Content-Type header is application/json
     * @throws Exception
     */
    public static function get_json_data()
    {
        // Get the raw POST data
        $rawData = file_get_contents('php://input');

        // Decode the JSON data into an associative array
        $jsonData = json_decode($rawData, true);

        // Check if the JSON is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Handle the error as needed
            throw new Exception('Invalid JSON input');
        }

        return $jsonData;
    }

    /**
     * Get the request body as an associative array
     * @return array
     */
    public static function get_post_data(): array
    {
        return $_POST;
    }

    /**
     * Checks if the http request method is PUT
     * @return bool
     */
    public static function is_put(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'PUT';
    }

    /**
     * Checks if the http request method is PATCH
     * @return bool
     */
    public static function is_patch(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'PATCH';
    }

    /**
     * Checks if the http request method is DELETE
     * @return bool
     */
    public static function is_delete(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'DELETE';
    }

    /**
     * Checks if the http request method is POST
     * @return bool
     */
    public static function is_post(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * Checks if the http request method is GET
     * @return bool
     */
    public static function is_get(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    /**
     * Checks if the request is an AJAX request
     * @return bool
     */
    public static function is_ajax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Adds a location header to the response
     * @param string $string
     * @return void
     */
    public static function add_location_header(string $string): void
    {
        header('Location: ' . $string);
    }


}
