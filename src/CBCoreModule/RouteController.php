<?php

namespace crocodicstudio\crudbooster\CBCoreModule;

use Route;

class RouteController
{
    public static function routeController($prefix, $controller, $namespace = null)
    {
        $prefix = trim($prefix, '/').'/';

        try {
            Route::get($prefix, ['uses' => $controller.'@getIndex', 'as' => $controller.'GetIndex']);
            $ctrl = self::getControllerPath($controller, $namespace);
            foreach (self::getControllerMethods($ctrl) as $method) {
                self::setRoute($prefix, $controller, $method);
            }
        } catch (\Exception $e) {

        }
    }

    /**
     * @param $prefix
     * @param $controller
     * @param $method
     * @param $wildcards
     */
    private static function routePost($prefix, $controller, $method, $wildcards)
    {
        $methodName = substr($method->name, 4);
        $slug = array_filter(preg_split('/(?=[A-Z])/', $methodName));
        $slug = strtolower(implode('-', $slug));
        Route::post($prefix.$slug.$wildcards, [
            'uses' => $controller.'@'.$method->name,
            'as' => $controller.'Post'.$methodName,
        ]);
    }

    /**
     * @param $prefix
     * @param $controller
     * @param $method
     * @param $wildcards
     */
    private static function routeGet($prefix, $controller, $method, $wildcards)
    {
        $methodName = substr($method->name, 3);
        $slug = array_filter(preg_split('/(?=[A-Z])/', $methodName));
        $slug = strtolower(implode('-', $slug));
        $slug = ($slug == 'index') ? '' : $slug;
        Route::get($prefix.$slug.$wildcards,
            ['uses' => $controller.'@'.$method->name,
            'as' => $controller.'Get'.$methodName]);
    }

    /**
     * @param $ctrl
     * @return array|\ReflectionMethod[]
     * @throws \ReflectionException
     */
    private static function getControllerMethods($ctrl)
    {
        $controller_methods = (new \ReflectionClass($ctrl))->getMethods(\ReflectionMethod::IS_PUBLIC);
        $controller_methods = array_filter($controller_methods, function ($method) {
            return ($method->class !== 'Illuminate\Routing\Controller' && $method->name !== 'getIndex');
        });

        return $controller_methods;
    }

    /**
     * @param $controller
     * @param $namespace
     * @return string
     */
    private static function getControllerPath($controller, $namespace)
    {
        $ns = $namespace ?: ctrlNamespace();
        $ctrl = $ns.'\\'.$controller;

        return $ctrl;
    }

    /**
     * @param $prefix
     * @param $controller
     * @param $method
     */
    private static function setRoute($prefix, $controller, $method)
    {
        $wildcards = '/{one?}/{two?}/{three?}/{four?}/{five?}';
        if (str_start($method, 'get')) {
            self::routeGet($prefix, $controller, $method, $wildcards);
        } elseif (str_start($method, 'post')) {
            self::routePost($prefix, $controller, $method, $wildcards);
        }
    }
}