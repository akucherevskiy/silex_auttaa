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

// Provides URL generation
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Provides CSRF token generation
$app->register(new Silex\Provider\FormServiceProvider());

// Provides session storage
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.save_path' => __DIR__.'/cache'
));

// Provides Twig template engine
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views'
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
//        'google' => array(
//            'key' => "973244655377-7gmbn4clhumb17nmsqes0p2u2udvf1um.apps.googleusercontent.com",
//            'secret' => "rtr9dDnzUDznaT6lygkex-tD",
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
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'default' => array(
            'pattern' => '^/',
            'anonymous' => true,
            'oauth' => array(
                //'login_path' => '/auth/{service}',
                //'callback_path' => '/auth/{service}/callback',
//                'check_path' => '/login/{service}/check',
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

$app->before(function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
    $token = $app['security']->getToken();
    $app['user'] = null;

    if ($token && !$app['security.trust_resolver']->isAnonymous($token)) {
        $app['user'] = $token->getUser();
    }
});