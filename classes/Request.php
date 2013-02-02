<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Hack
 * @package Kohana-my-base
 * @copyright 2012 pussbb@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE v3
 * @version 0.1.2
 * @link https://github.com/pussbb/Kohana-my-base
 * @category access
 * @subpackage access
 */

class Request extends Kohana_Request {

    /**
    * @todo overloaded kohana's function need to watch for the changes
    */
    /**
     * Process a request to find a matching route
     *
     * @param object|\Request $request Request
     * @param   array   $routes  Route
     * @return  array
     */
    public static function process(Request $request, $routes = NULL)
    {
        // Load routes
        $routes = (empty($routes)) ? Route::all() : $routes;
        ksort($routes);
        $params = NULL;

        foreach ($routes as $name => $route)
        {
            // We found something suitable
            if ($params = $route->matches($request))
            {
                return array(
                    'params' => $params,
                    'route' => $route,
                );
            }
        }

        return NULL;

    }

}


