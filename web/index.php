<?php

use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\Validator\Constraints as Assert;

require_once __DIR__ . '/bootstrap.php';

$app->get('/', function () use ($app) {
    if (isset($_REQUEST['method'])) {
        switch ($_REQUEST['method']) {
            case "auth" :
                auth($app);
                break;
            case "get_coordinates":
                getCoordinates($app);
                break;
            case "get_event":
                getEvent($app);
                break;
            case "set_event":
                setEvent($app);
                break;
            case "set_fake":
                setFake($app);
                break;
            default:
                echo "unknown func!";
        }
    } else {
        return new Response($app['twig']->render('index.twig', array()), 404);
    }

    return new Response();
});

function getRequestData(){
    // TODO:validate request
    $userId = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : false;
    $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : false;
    $crd = isset($_REQUEST['crd']) ? $_REQUEST['crd'] : false;
    return ['user_id'=> $userId,'token' => $token, 'crd' => $crd];
}

//метод auth записывает информацио о пользователе в базу данных при авторизации в приложении, время жизни пользователя - 10 минут
// по завершению работы функция автоматически удалит все записи старше 10 минут
function auth($app){
    $request = getRequestData();
    //    $correctToken = true; TODO add check token

    if ($request['user_id'] && $request['token'] && $request['crd'] && $request['token'] == 'veryfuckingsecretstring') {
        $user = $app['db']->fetchAll('SELECT * FROM users where user_id = ?', array($request['userId']));
        if (!$user) {
            $app['db']->executeUpdate(
                'INSERT INTO users (user_id, token, crd, last_time) VALUES (?, ?, ?, ?)',
                array($request['user_id'], $request['token'], $request['crd'], time())
            );

            return true;
        } else {
            $app['db']->executeUpdate("
                UPDATE users SET last_time = ? WHERE user_id = ?",
                array(time(), (int)$request['user_id'])
            );

            return true;
        }
    } else {
        throw new Exception('404');
    }
}

function getCoordinates($app){
    $request = getRequestData();
    $app['db']->executeUpdate('DELETE FROM events where (NOW() - time) > 600  or time IS NULL');

    if ($request['user_id'] && $request['token'] && $request['token'] == 'veryfuckingsecretstring') {
        if ($request['crd']) {
            $sql = "UPDATE users SET last_time = ? , crd = ? WHERE user_id = ?";
            $app['db']->executeUpdate($sql, array(time(), $request['crd'], (int)$request['user_id']));
        }
        $events = $app['db']->fetchAll('SELECT * FROM events');

        $result = array();
        foreach ($events as $event) {
            $result[] = $event['crd'];
        }

        echo json_encode($result);
    } else {
        throw new Exception('404');
    }
}

function getEvent($app){
    $request = getRequestData();

    if ($request['user_id'] && $request['token'] && $request['crd'] && $request['token'] == 'veryfuckingsecretstring') {
        $event = $app['db']->fetchAll('SELECT * FROM events where crd = ?', array($request['crd']));
        echo json_encode(array('crd' => $event[0]['crd']));
    } else {
        throw new Exception('404');
    }
}

function setEvent($app){
    $request = getRequestData();
    if ($request['user_id'] && $request['token'] && $request['crd'] && $request['token'] == 'veryfuckingsecretstring') {
        $event = $app['db']->fetchAll('SELECT * FROM events WHERE user_id=? ', array($request['user_id']));
        if (!$event) {
            $app['db']->executeUpdate(
                "INSERT INTO events (user_id,crd,time) VALUES (?,?,?)",
                array($request['user_id'], $request['crd'], time())
            );
        } else {
            $app['db']->executeUpdate(
                'UPDATE events SET crd = ?, time = ? WHERE user_id = ?',
                array($request['crd'], time(), $request['user_id']
                ));
        }
        $event = $app['db']->fetchAll('SELECT * FROM events ORDER BY id DESC LIMIT 1 ');

        echo json_encode(array('event' => $event[0]));
    } else {
        throw new Exception('404');
    }
}

function setFake($app){
    $request = getRequestData();
    $crd = $request['crd'];
    if ($request['user_id'] && $request['token'] && $request['crd'] && $request['token'] == 'veryfuckingsecretstring') {
        $event = $app['db']->fetchAll('SELECT * FROM events WHERE crd = ? ', array($crd));
        if (!$event) {
            throw new Exception('404');
        } else {
            if ($event[0]['carma'] > 0) {
                $app['db']->executeUpdate("UPDATE events SET carma = carma - 1 WHERE crd = ?", array($crd));
                $app['db']->executeUpdate("UPDATE users SET carma = carma - 1 WHERE user_id = ?", array($event[0]['user_id']));
                $user = $app['db']->fetchAll('SELECT * FROM users WHERE user_id = ? ', array((int)$event[0]['user_id']));

                if ($user[0]['carma'] == 0) {
                    $app['db']->executeUpdate("UPDATE users SET block = 1 WHERE user_id = ?", array($event[0]['user_id']));
                    $app['db']->executeUpdate("DELETE FROM events WHERE crd = ?", array($crd));
                }
            } else {
                $app['db']->executeUpdate("DELETE FROM events WHERE crd = ?", array($crd));
            }
        }
    }
}

$app->error(function (\Exception $e, $code) use ($app) {
    if ($code == 404 || $e->getMessage() == '404') {
        return new Response($app['twig']->render('404.twig', array()), 404);
    }
    return new Response('We are sorry, but something went terribly wrong.', $code);

});

$app->run();
