<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\Session\SessionManager;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\Session\Container;

class Module implements ServiceProviderInterface, 
                        AutoloaderProviderInterface, 
                        ViewHelperProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getServiceManager()
                            ->get('viewhelpermanager')
                            ->setFactory('controllerName', function($sm) use ($e) {
            $viewHelper = new View\Helper\ControllerName($e->getRouteMatch());
            return $viewHelper;
        });
        $eventManager        = $e->getApplication()->getEventManager();
        
        $eventManager->attach('route',array($this,'loadConfiguration'),2);
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getServiceConfig() {
        return array(
            'factories' => array(
                'ObjectManager' => function($sm) {
                    $objectManager = $sm->get('Doctrine\ORM\EntityManager');
                    return $objectManager;
                },
                'Zend\Authentication\AuthenticationService' => function($serviceManager) {
                    return $serviceManager->get('doctrine.authenticationservice.orm_default');

                },
                'AuthService' => function($sm) {
                    $adapterService = $sm->get('Zend\Authentication\AuthenticationService');
                    $adapter  = $adapterService->getAdapter();
             
                    $authService = new AuthenticationService();
                    $authService->setAdapter($adapter);
                    $authService->setStorage(new Session('operador'));
              
                    return $authService;
                },
                'Zend\Session\SessionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }

                        $sessionStorage = null;
                        if (isset($session['storage'])) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }

                        $sessionSaveHandler = null;
                        if (isset($session['save_handler'])) {
                            // class should be fetched from service manager since it will require constructor arguments
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }

                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                        if (isset($session['validator'])) {
                            $chain = $sessionManager->getValidatorChain();
                            foreach ($session['validator'] as $validator) {
                                $validator = new $validator();
                                $chain->attach('session.validate', array($validator, 'isValid'));

                            }
                        }
                    } else {
                        $sessionManager = new SessionManager();
                    }
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
            )
        );
    }
    
    public function bootstrapSession($e){
        $session = $e->getApplication()
                     ->getServiceManager()
                     ->get('Zend\Session\SessionManager');
        $session->start();
        
        $container = new Container();
        if(!isset($container->init)){
            $session->regenerateId(TRUE);
            $container->init = 1;
        }
    }
    
    public function loadConfiguration(MvcEvent $e)
    {
        $application = $e->getApplication();
        $sm = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();
        
        $router = $sm->get('router');
        $request = $sm->get('request');
        $matchedRouter = $router->match($request);
        
        if(null !== $matchedRouter){
            $sharedManager->attach('Zend\Mvc\Controller\AbstractActionController', 
                                    'dispatch', function ($e) use ($sm){
                                        
                                        $sm->get('ControllerPluginManager')
                                           ->get('AclPlugin')
                                           ->doAuthorization($e);
                                    },2);
//            $sharedManager->attach('Zend\Mvc\Controller\AbstractActionController', 
//                                    'dispatch', function ($e) use ($sm){
//                                        
//                                        $sm->get('ControllerPluginManager')
//                                           ->get('LogPlugin')
//                                           ->doLog($e);
//                                    },2);
        }
    }
    
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'menuAtivo'  => function($sm) {
                    return new \Application\View\Helper\MenuAtivo(
                            $sm->getServiceLocator()
                               ->get('Request'));
                },
                'message' => function($sm) {
                    return new \Application\View\Helper\Messenger(
                            $sm->getServiceLocator()
                               ->get('ControllerPluginManager')
                               ->get('flashmessenger'));
                },
            )
        );
    }
}
