<?php

namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class LogPlugin extends AbstractPlugin{
   
    protected $sessionContainer;
    protected $objectManager;
    
    private function getSessionContainer(){
        if(!$this->sessionContainer){
            $this->sessionContainer = $this->getController()
                                           ->getServiceLocator()
                                           ->get('AuthService')
                                           ->getStorage();
        }
        return $this->sessionContainer;
    }
    
    public function getObjectManager(){
        if(!$this->objectManager){
            $this->objectManager = $this->getController()
                                        ->getServiceLocator()
                                        ->get('ObjectManager');
        }
        return $this->objectManager;
    }

    public function doLog($e){

        $controller = $e->getTarget();
        $controllerClass = get_class($controller);
        $str = explode("Controller", $controllerClass);
        $modulo = substr($str[0], 0,  strlen($str[0])-1);
        $resource = $modulo.'/'.substr($str[1],1);
        
        $action = $this->getController()
                       ->getEvent()
                       ->getRouteMatch()
                       ->getParam('action','index');
        
        $pagina = '/'.$resource.'/'.$action;
        
        $usuario = $this->getSessionContainer()->read();       
        $usuario['ip'] = getenv("REMOTE_ADDR");
        
        $logger = $this->getController()
                       ->getServiceLocator()
                       ->get('logger');
        
        $logger->setTipo('info');
        
        $msg = (isset($usuario['role']))? "O usuário ".$usuario['nome'].' '.$usuario['sobrenome'] : "O usuário visitante";
        $msg.= ", com o endereço de ip ".$usuario['ip'].", visitou a página ".$pagina.".";
        
        $logger->setMensagem($msg);
        
        $objectManager = $this->getObjectManager();
        $objectManager->persist($logger);
        $objectManager->flush();
    }
    
}