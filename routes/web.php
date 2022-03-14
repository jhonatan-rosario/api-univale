<?php

$router->get('/univale/{local}/linhas-e-horarios', function($request, $local) {
    if ($local === 'ba' || $local === 'mg') {
        echo file_get_contents(DATABASE_PATH . "/linhas_e_horarios_{$local}.json");
    }
    echo 'Local incorreto';
});

$router->get('/univale/{local}/get/linhas-e-horarios', 'UnivalePegarLinhasHorariosController@create');