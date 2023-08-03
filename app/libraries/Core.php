<?php

/**
* App Core Class
* Creates URL & loads core controller
* URL FORMAT - /controller/method/params
*/

class Core
{
    protected mixed $controller = 'HomeController';
    protected string $method = 'index';
    protected array $params = [];

    public function __construct()
    {

        $url = $this->getUrl();

        if (isset($url[0])) {
            if (file_exists('../app/controllers/' . ucwords($url[0]) . 'Controller.php')) {
                $this->controller = ucwords($url[0]).'Controller';
                unset($url[0]);
            } else {
                Helpers::redirect('errors/index/404');
            }
        }

        // Instantiate controller class
        $this->controller = new $this->controller;

        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                Helpers::redirect('errors/index/404');
            }
        }

        // Get params
        $this->params = $url ? array_values($url) : [];

        // Call a callback with array of params
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function getUrl(): array
    {
        $url = [];
        if (isset($_GET['url'])) {

            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);

            // Remove non-alphabet characters except '/, .' from url
            $url = preg_replace('/[^a-zA-Z0-9\/.]/', '', $url);

            $url = explode('/', $url);
        }
        return $url;
    }
}
