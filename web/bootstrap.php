<?php
/**
 * Created by JetBrains PhpStorm.
 * User: toha
 * Date: 7/27/14
 * Time: 1:25 AM
 * To change this template use File | Settings | File Templates.
 */

require_once __DIR__.'/../vendor/autoload.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);

$app = new Silex\Application();

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/development.log',
));

// Provides URL generation
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Provides CSRF token generation
$app->register(new Silex\Provider\FormServiceProvider());

// Provides session storage
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.save_path' => __DIR__ . '/cache'
));

// Provides Twig template engine
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views'
));

// redis
$app->register(new Predis\Silex\ClientServiceProvider(), array(
    'predis.parameters' => 'tcp://127.0.0.1:6379/',
    'predis.options' => array(
        'profile' => '2.2',
        'prefix' => 'silex:',
    ),
));

$app->register(new Gigablah\Silex\OAuth\OAuthServiceProvider(), array(
    'oauth.services' => array(
        'facebook' => array(
            'key' => '298809946967213',
            'secret' => 'a35b0f986ac2a63b5e0bf97febbad6e6',
            'scope' => array('email'),
            'user_endpoint' => 'https://graph.facebook.com/me'
        ),
        'twitter' => array(
            'key' => "SyQSJpt83AsZE9GSgYiivqTOV",
            'secret' => "rdkqHX9VcX2LxA2jkOLg1y7Mdq89kAUeAeLUmHBFCp9WVW9AD9",
            'scope' => array(),
            'user_endpoint' => 'https://api.twitter.com/1.1/account/verify_credentials.json'
        ),
    )
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'default' => array(
            'pattern' => '^/',
            'anonymous' => true,
            'oauth' => array(
                'failure_path' => '/',
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
