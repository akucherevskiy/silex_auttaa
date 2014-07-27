<?php

require_once __DIR__.'/bootstrap.php';

$app->get('/', function () use ($app) {
    $services = array_keys($app['oauth.services']);

    return $app['twig']->render('index.twig', array(
        'login_paths' => array_map(function ($service) use ($app) {
            return $app['url_generator']->generate('_auth_service', array(
                'service' => $service,
                '_csrf_token' => $app['form.csrf_provider']->generateCsrfToken('oauth')
            ));
        }, array_combine($services, $services)),
        'logout_path' => $app['url_generator']->generate('logout', array(
            '_csrf_token' => $app['form.csrf_provider']->generateCsrfToken('logout')
        ))
    ));
});

//$app->get('//login/{service}/check', function ($service) use ($app) {
//    var_dump($service);
//    var_dump($app['user']);
//});

$app->match('/logout', function () {})->bind('logout');

$app->run();
