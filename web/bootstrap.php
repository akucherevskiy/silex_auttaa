<?php
/**
 * Created by JetBrains PhpStorm.
 * User: toha
 * Date: 7/27/14
 * Time: 1:25 AM
 * To change this template use File | Settings | File Templates.
 */

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/development.log',
));

$app->register(new Gigablah\Silex\OAuth\OAuthServiceProvider(), array(
    'oauth.services' => array(
        'facebook' => array(
            'key' => '298809946967213',
            'secret' => 'a35b0f986ac2a63b5e0bf97febbad6e6',
            'scope' => array('email'),
//            'user_endpoint' => 'https://graph.facebook.com/me'
            'user_endpoint' => 'http://localhost:8000/web/index.php/login'
        ),
//        'twitter' => array(
//            'key' => TWITTER_API_KEY,
//            'secret' => TWITTER_API_SECRET,
//            'scope' => array(),
//            'user_endpoint' => 'https://api.twitter.com/1.1/account/verify_credentials.json'
//        ),
//        'google' => array(
//            'key' => GOOGLE_API_KEY,
//            'secret' => GOOGLE_API_SECRET,
//            'scope' => array(
//                'https://www.googleapis.com/auth/userinfo.email',
//                'https://www.googleapis.com/auth/userinfo.profile'
//            ),
//            'user_endpoint' => 'https://www.googleapis.com/oauth2/v1/userinfo'
//        ),
//        'github' => array(
//            'key' => GITHUB_API_KEY,
//            'secret' => GITHUB_API_SECRET,
//            'scope' => array('user:email'),
//            'user_endpoint' => 'https://api.github.com/user'
//        )
    )
));

// Provides URL generation
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Provides CSRF token generation
$app->register(new Silex\Provider\FormServiceProvider());

// Provides session storage
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.save_path' => '/sessions'
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'default' => array(
            'pattern' => '^/',
            'anonymous' => true,
            'oauth' => array(
                //'login_path' => '/auth/{service}',
                //'callback_path' => '/auth/{service}/callback',
                'check_path' => '/hello/user',
                'failure_path' => '/login',
                'with_csrf' => true
            ),
            'logout' => array(
                'logout_path' => '/logout',
                'with_csrf' => true
            ),
            'users' => new Gigablah\Silex\OAuth\Security\User\Provider\OAuthInMemoryUserProvider()
        )
    ),
    'security.access_rules' => array(
        array('^/auth', 'ROLE_USER')
    )
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views'
));