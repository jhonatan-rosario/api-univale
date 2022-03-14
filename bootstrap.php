<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/config.php';

use Library\Request;
use Library\Router;

try {
    $router = new Router();
    $request = new Request();
    
    require __DIR__ . '/routes/web.php';

} catch (\Exception $e) {
    echo $e->getMessage();
}