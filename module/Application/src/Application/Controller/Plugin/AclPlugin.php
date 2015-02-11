<?php

namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class AclPlugin extends AbstractPlugin{
    private $sessionContainer;
    private $objectManager;

    private function getSessionContainer(){
        if($this->sessionContainer == NULL){
            $this->sessionContainer = $this->getController()
                                           ->getServiceLocator()
                                           ->get('AuthService')
                                           ->getStorage();
        }
        return $this->sessionContainer;
    }
    
    private function getObjectManager(){
        if($this->objectManager == NULL){
            $this->objectManager = $this->getController()
                                        ->getServiceLocator()
                                        ->get('\Doctrine\ORM\EntityManager');
        }
        return $this->objectManager;
    }

    public function doAuthorization($e){
        $acl = new Acl();
        $objectManager = $this->getObjectManager();
        $niveis = $objectManager->getRepository('Application\Entity\NivelDeAcesso')
                               ->findAll();
        foreach ($niveis as $nivel){
                $acl->addRole(new Role($nivel->getNome()));
        }
        
        $recursos = $objectManager->getRepository('Application\Entity\Recurso')
                               ->findAll();   
        
        foreach ($recursos as $recurso){
            $acl->addResource(new Resource($recurso->getNome()));
        }
        
        $permissoes = $objectManager->getRepository('Application\Entity\Permissao')
                               ->findBy(array(),array('permitido' => 'ASC'));
        
        foreach ($permissoes as $permissao){
            $pRoles = $permissao->getNiveisDeAcesso();
            $pResource = $permissao->getRecurso();            
            if($permissao->isPermitido()){
                foreach ($pRoles as $pRole){
                    $acl->allow($pRole->getNome(), 
                                $pResource->getNome(), 
                                $permissao->getNome());
                }
            }else{
                foreach ($pRoles as $pRole){
                    $acl->deny($pRole->getNome(), 
                                $pResource->getNome(), 
                                $permissao->getNome());
                }
            }
        }
        $acl->allow('root');
        
        $controller = $e->getTarget();
        $controllerClass = get_class($controller);
        $str = explode("Controller", $controllerClass);
        $modulo = substr($str[0], 0,  strlen($str[0])-1);
        $recurso = $modulo.'/'.substr($str[1],1);
        
        $action = $this->getController()
                       ->getEvent()
                       ->getRouteMatch()
                       ->getParam('action','index');

        $session = $this->getSessionContainer()->read();       
        $nivel = (!$session)? 'guest' : $session['nivel'];

        
        if(!$acl->isAllowed($nivel, $recurso, $action)){
            $router = $e->getRouter();
            $url    = $router->assemble(array(),array(
                                        'name' => 'autenticar'
                                       ));
            $response = $e->getResponse();
            $response->setStatusCode(302);
            $response->getHeaders()->addHeaderLine('Location',$url);
            $e->stopPropagation();
        }
        $e->getViewModel()->usuarioNivel = $nivel; 
        $e->getViewModel()->usuarioNome = $session['nome']; 
    }   
}