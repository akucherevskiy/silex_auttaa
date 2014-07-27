<?php
/**
 * Created by JetBrains PhpStorm.
 * User: toha
 * Date: 7/27/14
 * Time: 1:23 AM
 * To change this template use File | Settings | File Templates.
 */
require_once __DIR__.'/bootstrap.php';


$app->before(function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
    $token = $app['security']->getToken();
    $app['user'] = null;

//    if ($token && !$app['security.trust_resolver']->isAnonymous($token)) {
    if ($token) {
        $app['user'] = $token->getUser();
    }
});

$app->get('/login', function () use ($app) {
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

$app->match('/logout', function () {})->bind('logout');

$app->get('/hello/{name}', function ($name) use ($app) {
    var_dump($app['user']);
    return 'Hello '.$app->escape($name);
});

$app->run();