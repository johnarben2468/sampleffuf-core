<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Validator\Validation;
use xvsys\autoresponse\ResponseCreator;
use xvsys\csrf\CSRFTokenValidator;
use a15l\serialization\deserializer\HTTPQueryStringDeserializer;
use xvsys\validator\Validator;
use yapcdi\controller\resolver\symfony\ReflectionControllerResolver;
use a15l\serialization\request\deserializer\RequestDeserializer;
use a15l\serialization\metadata\loader\LazyMetadataLoader;
use a15l\serialization\metadata\factory\MetadataFactory;

error_reporting(E_ALL);
ini_set('display_errors', 'On');
date_default_timezone_set('UTC');

// di management
$resolver = new yapcdi\resolver\MetadataResolver();
$diContainer = new yapcdi\dic\Container($resolver);

// create reqeust from globals (_get, _post...) and share it for all classes
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$diContainer->addSharedInstance($request);

// DB
require 'config/db-local.php';
$diContainer->addSharedInstance($entityManager);

// Validator
$diContainer->addSharedInstance(function () {
    static $validator;
    if ($validator === null) {
        $builder = Validation::createValidatorBuilder();
        $builder->addXmlMapping('config/validation/validation.xml');
        $validator = new Validator($builder->getValidator());
    }
    return $validator;
}, 'xvsys\validator\Validator');

// Template Engine
$viewEngine = new \phastl\ViewEngine('app' . DIRECTORY_SEPARATOR . 'view' /* , 'tmp' . DIRECTORY_SEPARATOR . 'tpl' */);
$viewEngine->assign('_baseurl', $request->getUriForPath('/'));
$viewEngine->assign('_isXHR', $request->isXmlHttpRequest());
$diContainer->addSharedInstance($viewEngine, '\phastl\ViewEngineInterface');

if (($requestMethodBypass = $request->request->get('http-method')) !== null) {
    $request->setMethod($requestMethodBypass);
}

// Session
$sessionHandler = new NativeFileSessionHandler();
$sessionStorage = new NativeSessionStorage([], $sessionHandler);
$session = new Session($sessionStorage);
$session->setName('application_name');
$session->start();
$request->setSession($session);

if ($request->isXmlHttpRequest() === false) {
    $viewEngine->setDynamicLayout('layout' . DIRECTORY_SEPARATOR . 'main');
} else {
    $viewEngine->setDynamicLayout('layout' . DIRECTORY_SEPARATOR . 'xhr');
}
$eventDispatcher = new EventDispatcher();
// controller resolver implementation (alternatives ReflectionCacheControllerResolver, ConfigControllerResolver)
$conrollerResolver = new ReflectionControllerResolver($diContainer);

// Exceptions handler
$logger = new \Monolog\Logger("syslog");
$logHandler = new Monolog\Handler\NullHandler();
$logHandler->setFormatter(new \Monolog\Formatter\HtmlFormatter());
$logger->pushHandler($logHandler);
$exLogger = new \Monolog\ErrorHandler($logger);

$exHandler = new \abd\app\core\ExceptionHandler($viewEngine, $exLogger, true);
$eventDispatcher->addSubscriber($exHandler);

// Create automatically responses
$responseCreator = new ResponseCreator();
$eventDispatcher->addSubscriber($responseCreator);

// CSRF-Protection
if (null === $session->get('csrftoken')) {
    $session->set('csrftoken', sha1(uniqid('app-name', true)));
}
$viewEngine->assign('_csrfToken', $session->get('csrftoken'));
$csrf = new CSRFTokenValidator($session->get('csrftoken'));
$eventDispatcher->addSubscriber($csrf);

// Request deserializer
$fLoader = new \a15l\serialization\metadata\loader\file\XMLFileLoader('config/metadata');
$mdLoader = new LazyMetadataLoader($fLoader);
$mdFactory = new MetadataFactory($mdLoader, $fLoader->getClassMetadataConfig('default'));
$dispatcher = new \a15l\serialization\events\EventDispatcher();
$dispatcher->addListener(\a15l\serialization\events\EventDispatcherInterface::EVENT_DESERIALIZE, 'escapeHTML',
    function ($v){
        if (is_string($v)) {
            return htmlspecialchars($v, ENT_QUOTES);
        }
        if (is_array($v)) {
            $escaped = null;
            foreach ($v as $k => $val) {
                if (is_array($val) || is_object($val)) {
                    // nested arrays are not supported
                    return $v;
                }
                if (strlen($val) === 0) {
                    $escaped[$k] = null;
                    continue;
                }
                $escaped[$k] = htmlspecialchars($val, ENT_QUOTES);
            }
            return $escaped;
        }
        return $v;
    });
$deserializer = new HTTPQueryStringDeserializer($dispatcher, $mdFactory);
$eventDispatcher->addSubscriber(new RequestDeserializer($deserializer));


// application routes ...
$stack = new \Symfony\Component\HttpFoundation\RequestStack();
$fl = new FileLocator('config');
$loader = new XmlFileLoader($fl);
$collection = $loader->load('routing/routes.xml');
$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($collection, $context);
$rResolver = new RouterListener($matcher, $context, null, $stack);
$eventDispatcher->addSubscriber($rResolver);

// finally create the kernel ...
$app = new HttpKernel($eventDispatcher, $conrollerResolver);
// ... submit the request ...
$response = $app->handle($request);
// ... and return the response to the client
$response->send();
