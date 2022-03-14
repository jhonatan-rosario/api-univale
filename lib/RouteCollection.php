<?php
namespace Library;

class RouteCollection {
    protected $routes = [];
    protected $route_names = [];
    protected $method;
    protected $path;

    public function add($request_type, $path, $callback) {
        switch (strtolower($request_type)) {
            case 'get':
                return $this->addGet($path, $callback);
                break;
            case 'post':
                return $this->addPost($path, $callback);
                break;
            case 'put':
                return $this->addPut($path, $callback);
                break;
            case 'delete':
                return $this->addDelete($path, $callback);
                break;
            default:
                throw new \Exception('Tipo de requisição não implementado');
        }
    }

    protected function definePattern($pattern) {
        $pattern = implode('/', array_filter(explode('/', $pattern)));
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
     
        if (preg_match("/\{[A-Za-z0-9\_\-]{1,}\}/", $pattern)) {
            $pattern = preg_replace("/\{[A-Za-z0-9\_\-]{1,}\}/", "[A-Za-z0-9]{1,}", $pattern);
        }
     
        return $pattern;
    }

    protected function parsePattern(array $pattern) {
        // Defina o padrão
        $result['set'] = $pattern['set'] ?? null;
        // Allows route name settings
        $result['as'] = $pattern['as'] ?? null;
        // Allows new namespace definition for Controllers
        $result['namespace'] = $pattern['namespace'] ?? null;
        return $result;
    }

    public function isThereAnyHow($name) {
        return $this->route_names[$name] ?? false;
    }

    protected function addGet($path, $callback) {
        if (is_array($path)) {
            $settings = $this->parsePattern($path);
            
            $path = $settings['set'];

        } else {
            $settings = [];
        }
    
        $values = $this->toMap($path);
    
        $this->routes['get'][$this->definePattern($path)] = 
            ['callback' => $callback,
            'values' => $values,
            'namespace' => $settings['namespace'] ?? null];

        if (isset($settings['as'])) {
            $this->route_names[$settings['as']] = $path;
        }
        return $this;
    }

    protected function addPost($path, $callback) {
        if (is_array($path)) {
            $settings = $this->parsePattern($path);
            
            $path = $settings['set'];

        } else {
            $settings = [];
        }
    
        $values = $this->toMap($path);
    
        $this->routes['post'][$this->definePattern($path)] = 
            ['callback' => $callback,
            'values' => $values,
            'namespace' => $settings['namespace'] ?? null];

        if (isset($settings['as'])) {
            $this->route_names[$settings['as']] = $path;
        }
        return $this;
    }

    protected function addPut($path, $callback) {
        if (is_array($path)) {
            $settings = $this->parsePattern($path);
            
            $path = $settings['set'];

        } else {
            $settings = [];
        }
    
        $values = $this->toMap($path);
    
        $this->routes['put'][$this->definePattern($path)] = 
            ['callback' => $callback,
            'values' => $values,
            'namespace' => $settings['namespace'] ?? null];

        if (isset($settings['as'])) {
            $this->route_names[$settings['as']] = $path;
        }
        return $this;
    }

    protected function addDelete($path, $callback) {
        if (is_array($path)) {
            $settings = $this->parsePattern($path);
            
            $path = $settings['set'];

        } else {
            $settings = [];
        }
    
        $values = $this->toMap($path);
    
        $this->routes['delete'][$this->definePattern($path)] = 
            ['callback' => $callback,
            'values' => $values,
            'namespace' => $settings['namespace'] ?? null];

        if (isset($settings['as'])) {
            $this->route_names[$settings['as']] = $path;
        }
        return $this;
    }

    public function where($request_type, $path) {
        switch ($request_type) {
            case 'post':
                return $this->findPost($path);
                break;
            case 'get':
                return $this->findGet($path);
                break;
            case 'put':
                return $this->findPut($path);
                break;
            case 'delete':
                return $this->findDelete($path);
            break;
            default:
                throw new \Exception('Tipo de requisição não implementado');
        }
    }

    protected function parseUri($uri) {
        return implode('/', array_filter(explode('/', $uri)));
    }

    protected function findGet($path_sent) {
        $path_sent = $this->parseUri($path_sent);
    
        foreach($this->routes['get'] as $path => $callback) {  
            if (preg_match($path, $path_sent, $pieces)) {
                return (object) ['callback' => $callback, 'uri' => $pieces];
            }
        }
        return false;
    }

    protected function findPost($path_sent) {
        $path_sent = $this->parseUri($path_sent);
    
        foreach($this->routes['post'] as $path => $callback) {
            if (preg_match($path, $path_sent, $pieces)) {
                return (object) ['callback' => $callback, 'uri' => $pieces];
            }
        }
        return false;
    }
    
    protected function findPut($path_sent) {
        $path_sent = $this->parseUri($path_sent);
    
        foreach($this->routes['put'] as $path => $callback) {
            if (preg_match($path, $path_sent, $pieces)) {
                return (object) ['callback' => $callback, 'uri' => $pieces];
            }
        }
        return false;
    }
    
    protected function findDelete($path_sent) {
        $path_sent = $this->parseUri($path_sent);
    
        foreach($this->routes['delete'] as $path => $callback) {
            if (preg_match($path, $path_sent, $pieces)) {
                return (object) ['callback' => $callback, 'uri' => $pieces];
            }
        }
        return false;
    }

    protected function stringPosArray(string $haystack, array $needles, int $offset = 0) {
        $result = false;
        if (strlen($haystack) > 0 && count($needles) > 0) {
            foreach($needles as $element){
                $result = strpos($haystack, $element, $offset);
                
                if($result !== false) { break; }
            }
        }
        return $result;
    }

    protected function toMap($pattern) {
 
        $result = [];
 
        $needles = ['{', '[', '(', "\\"];
    
        $pattern = array_filter(explode('/', $pattern));
    
        foreach($pattern as $key => $element) {
            $found = $this->stringPosArray($element, $needles);
    
            if ($found !== false) {
                if (substr($element, 0, 1) === '{') {
                    $result[preg_filter('/([\{\}])/', '', $element)] = $key - 1;
                } else {
                    $index = 'value_' . !empty($result) ? count($result) + 1 : 1;
                    array_merge($result, [$index => $key - 1]);
                }
            }
        }
        return count($result) > 0 ? $result : false;
    }

    public function convert($pattern, $params) {
        if (!is_array($params)) {
            $params = array($params);
        }
    
        $positions = $this->toMap($pattern);

        if ($positions === false) {
            $positions = [];
        }

        $pattern = array_filter(explode('/', $pattern));
    
        if (count($positions) < count($pattern)) {
            $uri = [];
            foreach($pattern as $key => $element) {
                if(in_array($key - 1, $positions)) {
                    $uri[] = array_shift($params);
                } else {
                    $uri[] = $element;
                }
            }
            return implode('/', array_filter($uri));
        }
        return false;
    }
}