<?php

namespace Dispatcher\Service;

/**
 *  @Service(dispatcher, {
 *      dir: { type: 'array_dir', required: true},
 *      name: { default: 'generated'},
 *      ns:  { default: 'Dispatcher\Generated'},
 *      temp_dir: { default: '/tmp', type: dir },
 *      devel: {default: true}
 *  }, {shared: true})
 */
function dispatcher_service($config)
{
    $router = new \Dispatcher\Router( $config['temp_dir'] . '/dispatcher__' . $config['name']  . '.php');
    foreach ($config['dir'] as $dir) {
        $router->addDirectory($dir);
    }

    if ($config['devel']) {
        $router->development();
    }

    $router->load();

    return $router;
}
