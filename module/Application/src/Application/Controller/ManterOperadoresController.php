<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\Common\Collections\ArrayCollection;

use Application\DAL\OperadorDAO;
/**
 * Description of ManterOperadoresController
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class ManterOperadoresController extends AbstractActionController {
    private $authService;
    
    public function getAuthService(){
        if($this->authService == NULL){
            $this->authService = $this->getServiceLocator()->get('AuthService');
        }
        return $this->authService;
    }
    
    public function autenticarAction() {
        $request = $this->getRequest();
        $authService = $this->getAuthService();
        if($authService->getIdentity())
            $this->redirect()->toRoute('home');
        if ($request->isPost()) {
            $email = $request->getPost('emailTxt');
            $senha = $request->getPost('senhaPwd');
            $adapter = $authService->getAdapter();
            $adapter->setIdentityValue($email);
            $adapter->setCredentialValue($senha);
            $result = $authService->authenticate($adapter);
            if ($result->isValid()) {
                $identity = $result->getIdentity();
                if ($identity->isAtivo()) {
                    try {
                        $storage = $authService->getStorage();
                        $storage->write(array(
                            'id' => $identity->getIdOperador(),
                            'nome' => $identity->getNome(),
                            'email' => $identity->getEmail(),
                            'nivel' => $identity->getNivelDeAcesso()
                        ));
                        $this->redirect()->toRoute('home');
                    } catch (Exception $e) {
                        $authService = $this->getAuthService();
                        $authService->clearIdentity();
                        $authService->getStorage()->clear();
                        $this->redirect()->toRoute('autenticar');
                    }
                } else {
                    $authService->clearIdentity();
                    $authService->getStorage()->clear();
                    $mensagem = 'Não foi possível autenticar com estas informações ';
                    $mensagem.= 'de login. Tente novamente.';
                    $this->flashMessenger()->addErrorMessage($mensagem);
                    $this->redirect()->toRoute('autenticar');
                }
            } else {
                $authService->clearIdentity();
                $authService->getStorage()->clear();
                $mensagem = 'Não foi possível autenticar com estas informações ';
                $mensagem.= 'de login. Tente novamente.';
                $this->flashMessenger()->addErrorMessage($mensagem);
                $this->redirect()->toRoute('autenticar');
             }
        }
    }
    
    public function indexAction() {
         $request = $this->getRequest();

        if (!$request->isPost()) {
            $operadorDAO = new OperadorDAO($this->getServiceLocator());

            $ormPaginator = new ORMPaginator($operadorDAO->lerTodos());
            $ormPaginatorIterator = $ormPaginator->getIterator();

            $adapter = new Iterator($ormPaginatorIterator);

            $paginator = new Paginator($adapter);
            $paginator->setDefaultItemCountPerPage(10);
            $page = (int) $this->params()->fromQuery('page');
            if ($page) {
                $paginator->setCurrentPageNumber($page);
            }
            return array(
                'usuarios' => $paginator,
                'orderby' => $this->params()->fromQuery('orderby'),
            );
        }
    }
    
    public function logoutAction(){
        $authService = $this->getAuthService();
        $authService->clearIdentity();
        $authService->getStorage()->clear();
        $this->redirect()->toRoute('autenticar');
    }
}
