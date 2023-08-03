<?php
//Load the model and the view
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class Controller
{
    protected ViewModel $viewModel;
    public function __construct()
    {
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

    protected abstract function loadViewModel() : ViewModel;
}