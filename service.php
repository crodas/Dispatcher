<?php

namespace Dispatcher\Service;

/**
 *  @Service(dispatcher, {
 *      dir: { type: 'hash', required: true},
 *      name: { default: 'generated'},
 *      ns:  { default: 'Dispatcher\Generated'},
 *      temp_dir: { default: '/tmp' },
 *      devel: {default: true}
 *  }, {shared: true})
 */
function dispatcher_service($config)
{
    $router = new \Dispatcher\Router( $config['temp_dir'] . '/dispatcher:' . $config['name']  . '.php');
    foreach ($config['dir'] as $dir) {
        $router->addDirectory($dir);
    }

    if ($config['devel']) {
        $router->development();
    }

    $router->load();

    return $router;
}
