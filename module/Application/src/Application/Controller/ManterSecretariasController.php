<?php

/*
 * Copyright (C) 2015 Irving Fernando de Medeiros Oliveira
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Application\Controller;

use Application\DAL\SecretariaDAO;
use Application\Filters\SecretariaFilter;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Description of ManterSecretariasController
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class ManterSecretariasController extends AbstractActionController{
    public function indexAction() {
         $request = $this->getRequest();

        if (!$request->isPost()) {
            $secretariaDAO = new SecretariaDAO($this->getServiceLocator());

            $ormPaginator = new ORMPaginator($secretariaDAO->lerTodos());
            $ormPaginatorIterator = $ormPaginator->getIterator();

            $adapter = new Iterator($ormPaginatorIterator);

            $paginator = new Paginator($adapter);
            $paginator->setDefaultItemCountPerPage(10);
            $page = (int) $this->params()->fromQuery('page');
            if ($page) {
                $paginator->setCurrentPageNumber($page);
            }
            return array(
                'secretarias' => $paginator,
                'orderby' => $this->params()->fromQuery('orderby'),
            );
        }
    }
    
    public function adicionarAction() {
        $request = $this->getRequest();
        
        if(!$request->isPost()){
            $viewModel = new ViewModel();
            $viewModel->setTemplate('application/manter-secretarias/editar.phtml');
            return $viewModel;
        }
        $nomeTxt = $request->getPost('nomeTxt');

        $dadosFiltrados = new SecretariaFilter($nomeTxt);

        if (!$dadosFiltrados->isValid()) {
            foreach ($dadosFiltrados->getInvalidInput() as $erro) {
                foreach ($erro->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            $this->redirect()->toUrl('/secretarias/adicionar');
            return;
        }

        $parametros = new ArrayCollection();
        $parametros->set('nome', $dadosFiltrados->getValue('nomeTxt'));

        try {
            $SecretariaDAO = new SecretariaDAO($this->getServiceLocator());
            $SecretariaDAO->salvar($parametros);
            $mensagem = "Secretaria adicionada com sucesso.";
            $this->flashMessenger()->addSuccessMessage($mensagem);
        } catch (\Doctrine\DBAL\DBALException $e) {
            if (strpos($e->getMessage(), 'SQLSTATE[23000]') > 0) {
                $mensagem = "Já existe uma secretaria cadastrada com este nome";
            } else {
                $mensagem = "Ocorreu um erro na operação, tente novamente ";
                $mensagem .= "ou entre em contato com um administrador ";
                $mensagem .= "do sistema.";
            }
            $this->flashMessenger()->addErrorMessage($mensagem);
        } catch (\Exception $e) {
            $mensagem = "Ocorreu um erro na operação, tente novamente ";
            $mensagem .= "ou entre em contato com um administrador ";
            $mensagem .= "do sistema.";
            $this->flashMessenger()->addErrorMessage($mensagem);
        }
        $this->redirect()->toRoute('secretarias');
    }
    
    public function buscarAction() {
        $request = $this->getRequest();
        $busca = $this->params()->fromQuery('busca');
        
        if (($busca == null) || (!$request->isGet())) {
            $this->redirect()->toRoute('secretarias');
            return;
        }
        
        $parametrosBusca = new ArrayCollection();
        $parametrosBusca->set('nome', $busca);

        $secretariaDAO = new SecretariaDAO($this->getServiceLocator());
        $query = $secretariaDAO->busca($parametrosBusca);

        $ormPaginator = new ORMPaginator($query);
        $ormPaginatorIterator = $ormPaginator->getIterator();

        $adapter = new Iterator($ormPaginatorIterator);

        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);
        $page = (int) $this->params()->fromQuery('page');
        
        if ($page)
            $paginator->setCurrentPageNumber($page);

        $qtdResultados = $paginator->count();

        if ($qtdResultados == 0) {
            $this->flashMessenger()->addErrorMessage("Secretaria não encontrada.");
            $this->redirect()->toRoute('secretarias');
        }
        
        $viewModel = new ViewModel(array(
            'secretarias' => $paginator,
            'orderby' => $this->params()->fromQuery('orderby'),
        ));
        $viewModel->setTemplate('application/manter-secretarias/index.phtml');
        return $viewModel;
    }
    
    public function editarAction(){
        $request = $this->getRequest();
        $id = (int) $this->params()->fromRoute('id', 0);

        $secretariaDAO = new SecretariaDAO($this->getServiceLocator());
        $secretaria = $secretariaDAO->lerPorId($id);

        if ($secretaria == NULL) {
            $this->flashMessenger()->addMessage("Secretaria não encotrada.");
            $this->redirect()->toRoute('secretarias');
            return;
        }
        
        if (!$request->isPost()) {
            return array(
                'secretaria' => $secretaria
            );
        }

        $nomeTxt = $request->getPost('nomeTxt');

        $dadosFiltrados = new SecretariaFilter($nomeTxt);

        if (!$dadosFiltrados->isValid()) {
            foreach ($dadosFiltrados->getInvalidInput() as $erro) {
                foreach ($erro->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            $this->redirect()->toRoute('secretarias');
            return;
        }

        $parametros = new ArrayCollection();
        $parametros->set('nome', $dadosFiltrados->getValue('nomeTxt'));

        try {
            $secretariaDAO->editar($id, $parametros);
            $this->flashMessenger()->addSuccessMessage("Secretaria editada com sucesso.");
        } catch (\Doctrine\DBAL\DBALException $e) {
            if (strpos($e->getMessage(), 'SQLSTATE[23000]') > 0) {
                $mensagem = "Já existe uma secretaria cadastrada com este nome.";
            } else {
                $mensagem = "Ocorreu um erro na operação, tente novamente ";
                $mensagem .= "ou entre em contato com um administrador ";
                $mensagem .= "do sistema.";
            }
            $this->flashMessenger()->addErrorMessage($mensagem);
        } catch (\Exception $e) {
            $mensagem = "Ocorreu um erro na operação, tente novamente ";
            $mensagem .= "ou entre em contato com um administrador ";
            $mensagem .= "do sistema.";
            $this->flashMessenger()->addErrorMessage($mensagem);
        }
        $this->redirect()->toRoute('secretarias');
    }

    public function excluirAction() {
        $id = $this->params()->fromRoute('id');
        if (isset($id)) {
            try {
                $secretariaDAO = new SecretariaDAO($this->getServiceLocator());
                $secretariaDAO->excluir($id);
            } catch (\Exception $e) {
                $mensagem = "Ocorreu um erro na operação, tente novamente ou ";
                $mensagem.= "entre em contato com um administrador do sistema.";
                $this->flashMessenger()->addErrorMessage($mensagem);
                $this->redirect()->toRoute('secretarias');
            }
            $this->flashMessenger()->addSuccessMessage("Secretaria excluída com sucesso.");
            $this->redirect()->toRoute('secretarias');
        }
    }
    
    public function visualizarAction(){
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->flashMessenger()->addMessage("Secretaria não encotrada");
            $this->redirect()->toRoute('secretarias');
        }
        try {
            $secretariaDAO = new SecretariaDAO($this->getServiceLocator());
            $secretaria = $secretariaDAO->lerPorId($id);
        } catch (\Exception $e) {
            $mensagem = 'Ocorreu um erro na operação, tente novamente ou entre ';
            $mensagem.= 'em contato com um administrador do sistema.';
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('secretarias');
        }
        if ($secretaria != NULL) {
            return array('secretaria' => $secretaria);
        } else {
            $this->flashMessenger()->addMessage("Secretaria não encotrada");
            $this->redirect()->toRoute('secretarias');
        }
    }
}
