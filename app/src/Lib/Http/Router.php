<?php

namespace App\Lib\Http;

use App\Lib\Controllers\AbstractController;


class Router {

    const string CONTROLLER_NAMESPACE_PREFIX = "App\\Controllers\\";
    const string ROUTE_CONFIG_PATH = __DIR__ . '/../../../config/routes.json';
    

    public static function route(Request $request): Response {
        $cfg = self::getConfig();

        foreach($cfg as $r) {
            if(self::verifierMethode($request, $r) === false) {
                continue;
            }

            $paramsUri = self::matcherUri($request, $r);
            if($paramsUri === false) {
                continue;
            }

            // mettre les params dans la request
            $request->setUriParams($paramsUri);

            $ctrl = self::getControllerInstance($r['controller']);
            return $ctrl->process($request);
        }

        throw new \Exception('Route not found', 404);
    }
    
    private static function getConfig(): array {
        $routesConfigContent = file_get_contents(self::ROUTE_CONFIG_PATH);
        $routesConfig = json_decode($routesConfigContent, true);

        return $routesConfig;
    }


    private static function verifierMethode(Request $request, array $route): bool {
        return $request->getMethod() === $route['method'];
    }

    // check si l'uri match avec la route
    private static function matcherUri(Request $request, array $route): array|false {
        $uri = $request->getUri();
        $chemin = strtok($uri, '?');
        $cheminRoute = $route['path'];

        // si y'a pas de {} c'est simple
        if (strpos($cheminRoute, '{') === false) {
            return $chemin === $cheminRoute ? [] : false;
        }

        // transformer en regex genre /contact/{filename} => /^\/contact\/([^\/]+)$/
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $cheminRoute);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $chemin, $resultats)) {
            return false;
        }

        // recuperer les noms des params
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $cheminRoute, $nomsParams);

        // faire le tableau avec les valeurs
        $params = [];
        for ($i = 0; $i < count($nomsParams[1]); $i++) {
            $params[$nomsParams[1][$i]] = $resultats[$i + 1];
        }

        return $params;
    }

    private static function getControllerInstance(string $controller): AbstractController {
        $classe = self::CONTROLLER_NAMESPACE_PREFIX . $controller;

        if(class_exists($classe) === false) {
            throw new \Exception('Route not found', 404);
        }

        $instance = new $classe();

        if(is_subclass_of($instance, AbstractController::class)=== false){
            throw new \Exception('Route not found', 404);
        }

        return $instance;
    }

}
