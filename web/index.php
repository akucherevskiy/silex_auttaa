<?php

use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

require_once __DIR__.'/bootstrap.php';

error_reporting(E_ALL); // TODO rm
ini_set("display_errors", 1); // TODO rm

$app->get('/', function () use ($app) {
    switch($_REQUEST['method']){
        case "auth" : auth($app);
//        case "map":   map($_REQUEST['method']);
    }
    return new Response();
});

// ++
//метод auth записывает информацио о пользователе в базу данных при авторизации в приложении, время жизни пользователя - 10 минут
// по завершению работы функция автоматически удалит все записи старше 10 минут
function auth($app){
    $userId = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : false;
    $token  = isset($_REQUEST['token']) ? $_REQUEST['token']: false;
    $crd  = isset($_REQUEST['crd']) ? $_REQUEST['crd']: false;
    //    $correctToken = true; TODO add check token
    if ($userId && $token && $crd && $token == 'veryfuckingsecretstring'){
        $user = $app['db']->fetchAll('SELECT * FROM users where user_id = ?', array($userId));
        if (!$user){
            $app['db']->executeUpdate(
                'INSERT INTO users (user_id, token, crd, last_time) VALUES (?, ?, ?, ?)',
                array($userId,$token,$crd,time())
            );

            echo 'good insert'; // TODO rm
            return true;

        } else {
            $sql = "UPDATE users SET last_time = ? WHERE user_id = ?";
            $app['db']->executeUpdate($sql, array(time(), (int) $userId));

            echo 'good update'; // TODO rm
            return true;
        }
    } else{
        throw new Exception('404');
    }
//    $app['db']->executeUpdate(
//        'DELETE FROM  users where NOW() - last_time > 600  or last_time IS NULL');
}

$app->get('/user_auth/{email}/{secret_token}/{crd}', function ($email, $secret_token, $crd) use ($app) {
    if ($email && $secret_token && $crd){
        // create user model
        echo 'user_auth action';
        $users = $app['db']->fetchAll('SELECT * FROM users');

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
->value('crd', false);

$app->error(function (\Exception $e, $code) use ($app) {
    if ($code == 404 || $e->getMessage() == '404') {
        return new Response( $app['twig']->render('404.twig', array()), 404);
    }
    return new Response('We are sorry, but something went terribly wrong.', $code);

});

$app->run();
