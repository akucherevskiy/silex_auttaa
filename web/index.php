<?php

use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Request;
require_once __DIR__.'/bootstrap.php';

$app->get('/', function () use ($app) {
    return 'Welcome to Auttaa, fuck you';
});

$app->get('/user_auth/{email}/{secret_token}/{crd}', function ($email, $secret_token, $crd) use ($app) {
    if ($email && $secret_token && $crd){
        // create user model
        $app['predis']->set('1', '33');
        var_dump($app['predis']->get('1'));
               // create user in redis, insert email, token, crd
        return true;
    }
    else{
        throw new Exception('404');
    }
})
->value('email', false)
->value('secret_token', false)
->value('crd', false)
;// TODO: add asserts


$app->get('/map/{crd}', function ($crd) use ($app) {
    $fakeData = array('50.424921,30.506669','50.424922,30.506669','50.424923,30.506669');
    if ($crd){
        // request from mobile app, update user location
        var_dump($crd);
    }

    //return events in radius around user
    return $app->json($fakeData);
})
-> value('crd', false);

$app->error(function (\Exception $e, $code) use ($app) {
    if ($code == 404 || $e->getMessage() == '404') {
        return new Response( $app['twig']->render('404.twig', array()), 404);
    }
    return new Response('We are sorry, but something went terribly wrong.', $code);

});


$app->run();
