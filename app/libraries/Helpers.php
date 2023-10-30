<?php
/** @noinspection PhpPossiblePolymorphicInvocationInspection */

/** @noinspection PhpComposerExtensionStubsInspection */

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
        // Customise the time format
        $dateFormat = "Y-m-j, g:i a";
        $output = "%datetime% | %level_name% | %message% %context% %extra%\n";
        $formatter = new Monolog\Formatter\LineFormatter($output, $dateFormat);
        $log->getHandlers()[0]->setFormatter($formatter);
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
        try {
            $log = new Logger('info');
            $log->pushHandler(new StreamHandler(INFO_LOG_FILE, Logger::INFO));// Customise the time format
            $dateFormat = "Y-m-j, g:i a";
            $output = "%datetime% | %level_name% | %message% %context% %extra%\n";
            $formatter = new Monolog\Formatter\LineFormatter($output, $dateFormat);
            $log->getHandlers()[0]->setFormatter($formatter);
            $log->info($message);
        } catch (RuntimeException $e) {
            // log error using PHP's error_log() function
            error_log($e->getMessage());
        }
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

        // Turn off SMTP debugging
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
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
        // create email model
        $email_model = new EmailModel([
            EmailModelSchema::RECIPIENT_ADDRESS => $email,
            EmailModelSchema::SUBJECT => 'Login Code',
            EmailModelSchema::BODY => "Your login code is: $otp"
        ]);

        Helpers::send_email_async($email_model);
    }

    /**
     * Email the user and the admin after the user books a room
     * @throws Exception
     */
    public static function send_booking_email(ReservationModel $reservation, bool $isAdmin): void
    {
        // If $isAdmin is true, send the email to all admins, otherwise send it to the user
        if ($isAdmin) {
            $admins = UserModel::getAllAdmins();
            foreach ($admins as $admin) {
                $email_model = new EmailModel([
                    EmailModelSchema::RECIPIENT_ADDRESS => $admin->email,
                    EmailModelSchema::SUBJECT => 'New Booking',
                    EmailModelSchema::BODY => "A new booking has been submitted and is pending approval. Please login to the admin at " . URL_ROOT . "/dashboard/admin# to approve it."
                ]);
                Helpers::send_email_async($email_model);
            }
            return;
        }
        // Email the user and tell them that their booking has been received
        $email_model = new EmailModel([
            EmailModelSchema::RECIPIENT_ADDRESS => $reservation->user->email,
            EmailModelSchema::SUBJECT => 'Booking Received',
            EmailModelSchema::BODY => "Your booking has been received and is pending approval. You will be notified when it is approved or rejected."
        ]);
        Helpers::send_email_async($email_model);
    }


    /**
     * Email the user about the booking decision
     * @throws Exception
     */
    public static function send_booking_decision_email(ReservationModel $reservation): void
    {
        // Email the user and tell them if their booking was approved or rejected
        $email_model = new EmailModel([
            EmailModelSchema::RECIPIENT_ADDRESS => $reservation->user->email,
            EmailModelSchema::SUBJECT => 'Booking Decision',
            EmailModelSchema::BODY => "Your booking has been " . $reservation->status->name . "."
        ]);
        Helpers::send_email_async($email_model);
    }


    /**
     * Send email asynchronously
     * @throws Exception
     */
    public static function send_email_async(EmailModel $email_model): void
    {
        // Create child process using pcntl_fork to send email asynchronously
        $pid = pcntl_fork();
        $recipient_address = $email_model->recipient_address;
        if ($pid === -1) {
            // Error: failed to fork
            // log error
            Helpers::log_error("Failed to fork child process to send email to $recipient_address");
        } else if ($pid === 0) {
            // Child process: start the event loop and send the email
            // Create an event loop
            $loop = React\EventLoop\Loop::get();

            // send email asynchronously and use the event loop to wait for the promise to resolve
            $promise = resolve(Helpers::send_email($email_model));
            $promise->then(function ($value) use ($email_model) {
                if ($value) {
                    // log success
                    Helpers::log_info("Email sent to $email_model->recipient_address");
                } else {
                    // log error
                    Helpers::log_error("Failed to send email to $email_model->recipient_address");
                }
                // log the email message
                Helpers::log_info("Message: \n$email_model->body");
            })->otherwise(function ($reason) use ($recipient_address) {
                // log error
                Helpers::log_error("Failed to send email to $recipient_address\n" . $reason);
            })->always(function () use ($loop) {
                // Stop the event loop
                $loop->stop();
            });

            // Start the event loop
            $loop->run();

            exit;

        }
    }

    public static function send_email_no_fork(EmailModel $emailModel): void
    {

        try {

            $recipient_address = $emailModel->recipient_address;
            $subject = $emailModel->subject;
            $body = $emailModel->body;
            $path = APP_ROOT. "/../php-scripts/send_email.php";
            $password = EMAIL_CLIENT_APP_PASSWORD;

            // Using nohup to run the process in the background
            $command = "nohup php $path $recipient_address '$subject' '$body' $password > /dev/null 2>&1 &";
            exec($command);

        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
        }
    }

    

    #[NoReturn]
    public static function logout(): void
    {
        // Destroy session
        Helpers::destroy_session();
    }

    public static function remove_from_session(string $key): void
    {
        // Check if $key is set in $_SESSION
        if (!isset($_SESSION[$key])) {
            return;
        }
        unset($_SESSION[$key]);
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
    public static function sendHttpResponse(int $code, string $message, string $contentType): void
    {
        // Get the HTTP status text that corresponds to the code
        $statusText = self::getHttpStatusText($code);

        // Set the HTTP response code and status text
        header("HTTP/1.1 $code $statusText", true, $code);

        // Set the content type
        header("Content-Type: $contentType");

        // If a custom message is provided, send it as the response body
        echo $message;
    }

    /**
     * Returns the HTTP status text that corresponds to the given code
     */
    private static function getHttpStatusText(int $code): string
    {
        $statusTexts = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            204 => 'No Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            // ... and so on
        ];

        return $statusTexts[$code] ?? "Unknown Status (Code: $code)";
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
     * Checks if the given string is a valid URL
     * @param int $statusCode
     * @param array $data
     * @return void
     */
    #[NoReturn]
    public static function sendJsonResponse(int $statusCode, array $data): void
    {
        $message = json_encode($data);

        if ($message === false) {
            // Handle JSON encoding error
            // This might be because $data contains non-UTF-8 strings, resources, or other non-encodable values
            // Adjust error handling as needed for your application
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Failed to encode response data to JSON']);
            exit;
        }

        // Set the HTTP status code
        http_response_code($statusCode);
        // Set the content type to JSON
        header('Content-Type: application/json');
        // Echo the JSON-encoded message body
        echo $message;

        exit;
    }

    private static function get_admin_email(): string
    {
        return 'jane.doe@iu.org';
    }

}
