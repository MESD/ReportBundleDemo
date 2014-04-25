<?php

namespace Mesd\BreadcrumbBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class DefaultController extends Controller
{
    // public $placeholder
    // placeholder locations
    // values: before, after
    // description: used to define whether breadcrumbs
    //              combine router parameters with routed patterns
    // examples:
    //     before: /entity/{id}/show -- home / entity / show(1) -- correct
    //     after:  /entity/{id}/show -- home / entity(1) / show -- incorrect
    public $placeholder;
    public $separator;
    public $homeLabel; // to include icon, ex. '<i class="icon-home"></i> Home'
    
    public function indexAction($request)
    {
        $config = $this->container->getParameter('mesd_breadcrumb');
        //echo '<pre>';var_dump($config);exit;
        $this->placeholder = $config['placeholder'];
        $this->separator   = $config['separator'];
        $this->homeLabel   = $config['homeLabel'];

/*        // get current uri: /orcase-symfony/agency/new
        $uri = $request->getUri();
        $pathInfo = $request->getPathInfo();

        // break the url into parts
        $url = parse_url($uri);

        // split the path
        $path = preg_split('/\//', $pathInfo);

        // get a list of system routes
        $routerCollection = $this->get('router')->getRouteCollection()->all();

        // build routes array, trim extraneous slashes from right of path
        foreach ($routerCollection as $k => $v){
            $routes[$k] = 1 == strlen($v->getPath()) ? $v->getPath() : rtrim($v->getPath(), '/');
        }
        
        // parse url path for potential parent paths, if none exist, ignore and move on
        $len = count($path);
        for($i = 0; $i < $len; $i++) {
            // if it's the first loop, get the current path
            if($len == count($path)){
                $crumbs[$request->attributes->get('_route')] = $pathInfo;
            }
            // else, if it's the last loop, get the home path
            else if(1 == count($path)){
                $k = array_search('/', $routes);
                $crumbs[$k] = $routes[$k];
            }
            // else, just get the matching path
            // if no matching path found, get nothing
            else{
                if(array_search(implode('/', $path), $routes)){
                    $k = array_search(implode('/', $path), $routes);
                    $crumbs[$k] = $routes[$k];
                }
            }
            // drop the last item from the url
            array_pop($path);
        }*/

        $router = $this->get('router');
        $routeCollection = $router->getRouteCollection();
        $routeParams = $router->match($request->getPathInfo());
        $route = $routeCollection->get($routeParams['_route']);
        
        $baseUrl = $request->getBaseUrl();
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();
        $routePattern = $route->getPattern();
        
        $ignoreParams = array('_route' => true, '_controller' => true);
        $routeParams = array_diff_key($routeParams, $ignoreParams);
        
        $paths = preg_split('@/@', $pathInfo, NULL, PREG_SPLIT_NO_EMPTY);
        $routes[$this->homeLabel] = $baseUrl;

        $path_temp = $baseUrl;
        $name_temp = '';
        $pre_temp = '';
        if('before' == $this->placeholder){
            for($i = 0; $i < count($paths); $i++) {
                if(in_array($paths[$i], $routeParams)){
                    $pre_temp .= '/' . $paths[$i];
                    if('' == $name_temp){
                        $name_temp = $paths[$i];
                    }
                    else{
                        $name_temp .= '\\' . $paths[$i];
                    }
                }
                else {
                    $path_temp .= $pre_temp . '/' . $paths[$i];
                    if('' == $name_temp){
                        $routes[ucfirst($paths[$i])] = $path_temp;
                    }
                    else{
                        $routes[ucfirst($paths[$i]) . ': ' . $name_temp] = $path_temp;
                        $name_temp = '';
                        $pre_temp = '';
                    }
                }
            }
        }
        elseif('after' == $this->placeholder){
            for($i = 0; $i < count($paths); $i++) {
                if(in_array($paths[$i], $route_params)){
                    $path_temp += '/' . $paths[$i];
                    if('' == $name_temp){
                        $name_temp = $paths[$i];
                    }
                    else{
                        $name_temp += '\\' . $paths[$i];
                    }
                }
                else {
                    $path_temp += '/' . $paths[$i];
                    if('' == $name_temp){
                        $routes[ucfirst($paths[$i])] = $path_temp;
                    }
                    else{
                        $routes[ucfirst($paths[$i]) . ': ' . $name_temp] = $path_temp;
                        $name_temp = '';
                    }
                }
            }
        }

        return $this->render('MesdBreadcrumbBundle:Default:index.html.twig', array(
            'routes'    => $routes,
            'separator' => $this->separator
        ));
    }
}