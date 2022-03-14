<?php
namespace Library;

use Library\Dispacher;
use Library\RouteCollection;

class Router {

    protected $dispacher;
    protected $route_collection;

    public function __construct() {
        $this->route_collection = new RouteCollection();
        $this->dispacher = new Dispacher();
    }

    public function get($path, $callback) {
        $this->route_collection->add('get', $path, $callback);
        return $this;
    }

    public function post($path, $callback) {
        $this->route_collection->add('post', $path, $callback);
        return $this;
    }

    public function put($path, $callback) {
        $this->route_collection->add('put', $path, $callback);
        return $this;
    }

    public function delete($path, $callback) {
        $this->route_collection->add('delete', $path, $callback);
        return $this;
    }

    public function find($request_type, $path) {
        return $this->route_collection->where($request_type, $path);
    }

    protected function dispach($route, $params, $namespace = "Http\\Controllers\\") {
        return $this->dispacher->dispach($route->callback, $params, $namespace);
    }

    protected function notFound() {
        return header("HTTP/1.0 404 Not Found", true, 404);
    }
 
    public function resolve($request) {
        $route = $this->find($request->method(), $request->uri());
        
        if($route) {
            $params = $route->callback['values'] ? 
                $this->getValues($request->uri(), $route->callback['values']) : [];

            return $this->dispach($route, $params);
        }
        return $this->notFound();
    }

    protected function getValues($pattern, $positions) {
        $result = [];
    
        $pattern = array_filter(explode('/', $pattern));
    
        foreach($pattern as $key => $value) {
            if(in_array($key, $positions)) {
                $result[array_search($key, $positions)] = $value;
            }
        }
        return $result; 
    }

    public function translate($name, $params) {
        $pattern = $this->route_collection->isThereAnyHow($name);
        
        if($pattern) {
            $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $server = $_SERVER['SERVER_NAME'] . '/';
            $uri = [];

            $uri = implode('/', array_filter($uri)) . '/';
    
            return $protocol . $server . $this->route_collection->convert($pattern, $params);
        }
        return false;
    }
}