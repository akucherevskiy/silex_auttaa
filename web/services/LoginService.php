<?php
/**
 * Created by JetBrains PhpStorm.
 * User: toha
 * Date: 7/28/14
 * Time: 11:59 PM
 * To change this template use File | Settings | File Templates.
 */
use Gigablah\Silex\OAuth\Security\User\StubUser;
use Silex\Application;

class loginService {

    /* @var StubUser $user */
    private $user;
    private $app;

    public function __construct($app){
        $this->user = $app['user'];
        $this->app = $app;
    }

    public function login(){
        /* @var StubUser $user */
        if ($this->user){
            $jsonUser = [$this->user->getEmail(), $this->user->getUsername()];
            return new \Symfony\Component\BrowserKit\Response($this->app->json($jsonUser), 200, array('Content-Type' => 'application/json'));
        }
        $services = array_keys($this->app['oauth.services']);

        return $this->app['twig']->render('index.twig', array(
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

    }
}