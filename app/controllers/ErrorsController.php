<?php

class ErrorsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index($error_code = 200): void
    {
        $payload = new ErrorsViewModel();
        $status = $error_code ?: $_SERVER['REDIRECT_STATUS'];
        $status_code = '';
        $message = '';
        $codes = array(
            200 => array('200', 'This is the Error Page'),
            400 => array('400', 'Bad Request'),
            403 => array('403', 'You do not have permission to perform this action.'),
            404 => array('404', 'The endpoint or resource was not found.'),
            405 => array('405', 'The method specified in the Request-Line is not allowed for the specified resource.'),
            408 => array('408', 'Your browser failed to send a request in the time allowed by the server.'),
            500 => array('500', 'The request was unsuccessful due to an unexpected condition encountered by the server.'),
            502 => array('502', 'The server received an invalid response from the upstream server while trying to fulfill the request.'),
            504 => array('504', 'The upstream server failed to send a request in the time allowed by the server.'),
        );
        if (array_key_exists($status, $codes)) {
            $status_code = $codes[$status][0];
            $message = $codes[$status][1];
        }
        $payload->title = $status_code;
        $payload->message = $message;
        ob_start();
        header("HTTP/2.1 $status_code");
        $this->view('errors/index', $payload);
        ob_flush();
    }

    protected function loadViewModel(): ViewModel
    {
        // TODO: Implement loadViewModel() method.
    }
}
