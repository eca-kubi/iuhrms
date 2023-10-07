<?php
//Load the model and the view
use JetBrains\PhpStorm\NoReturn;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class Controller
{
    protected ViewModel $viewModel;

    public function __construct()
    {
        $this->viewModel = new ViewModel();
    }
    public function view(string $view, ViewModel $viewModel): void
    {
        // Use Twig
        global $twig;
        try {
            $twig->display($view, ['viewmodel' => $viewModel]);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
        }
    }

    #[NoReturn]
    protected function sendJSONResponse(int $statusCode, array $data): void
    {
        $message = Helpers::json_encode($data);
        Helpers::sendHttpResponse($statusCode, $message);
        exit;
    }
}