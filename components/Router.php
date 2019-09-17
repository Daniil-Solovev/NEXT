<?php

class Router
{
	private $routes;

    /**
     * Router constructor.
     * Подключает правила маршрутов (routes)
     */
	public function __construct()
	{
		$routesPath = ROOT.'/config/routes.php';
		$this->routes = include($routesPath);
	}

    /**
     * Возвращает разделы сайта без домена
     * @return string
     */
    private function getUri()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            return trim($_SERVER['REQUEST_URI'],  '/');
        }
    }

    /**
     * Запуск приложения
     */
	public function run()
	{
	    // Получает строку запроса
        $uri = $this->getUri();

        // Проверяет наличие запроса (в $uri) в routes
        foreach ($this->routes as $uriPattern => $path) {
            if (preg_match("~$uriPattern~", $uri)) {
                $internalRoute = preg_replace("~$uriPattern~", $path, $uri);

                // Определяет контроллер, action, параметры
                $segments = explode('/', $internalRoute);
                $controllerName = ucfirst(array_shift($segments) . 'Controller');

                $actionName = 'action' . ucfirst(array_shift($segments));

                $parameters = $segments;
                // Подключает файл класса контроллера
                $controllerFile = ROOT . '/controllers/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    include_once($controllerFile);
                }

                // Создает объект, вызывает метод (action)
                $controllerObject = new $controllerName;
                if (!method_exists($controllerObject, $actionName)) {
                    http_response_code('404');
                    exit('Страница не найдена');
                }
                $result = call_user_func_array(array($controllerObject, $actionName), $parameters);
                if ($result != null) {
                    break;
                }
            }
        }
	}
}