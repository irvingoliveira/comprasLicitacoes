<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
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
    
    public function logoutAction(){
        $authService = $this->getAuthService();
        $authService->clearIdentity();
        $authService->getStorage()->clear();
        $this->redirect()->toRoute('autenticar');
    }
}
