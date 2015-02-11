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

use Application\DAL\UnidadeDAO;
use Application\Filters\UnidadeFilter;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Description of ManterUnidadesController
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class ManterUnidadesController extends AbstractActionController{
    public function indexAction() {
         $request = $this->getRequest();

        if (!$request->isPost()) {
            $unidadeDAO = new UnidadeDAO($this->getServiceLocator());

            $ormPaginator = new ORMPaginator($unidadeDAO->lerTodos());
            $ormPaginatorIterator = $ormPaginator->getIterator();

            $adapter = new Iterator($ormPaginatorIterator);

            $paginator = new Paginator($adapter);
            $paginator->setDefaultItemCountPerPage(10);
            $page = (int) $this->params()->fromQuery('page');
            if ($page) {
                $paginator->setCurrentPageNumber($page);
            }
            return array(
                'unidades' => $paginator,
                'orderby' => $this->params()->fromQuery('orderby'),
            );
        }
    }
    
    public function adicionarAction() {
        $request = $this->getRequest();
        
        if(!$request->isPost()){
            $viewModel = new ViewModel();
            $viewModel->setTemplate('application/manter-unidades/editar.phtml');
            return $viewModel;
        }
        $descricaoTxt = $request->getPost('descricaoTxt');

        $dadosFiltrados = new UnidadeFilter($descricaoTxt);

        if (!$dadosFiltrados->isValid()) {
            foreach ($dadosFiltrados->getInvalidInput() as $erro) {
                foreach ($erro->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            $this->redirect()->toUrl('/unidades/adicionar');
            return;
        }

        $parametros = new ArrayCollection();
        $parametros->set('descricao', $dadosFiltrados->getValue('descricaoTxt'));

        try {
            $UnidadeDAO = new UnidadeDAO($this->getServiceLocator());
            $UnidadeDAO->salvar($parametros);
            $mensagem = "Unidade adicionada com sucesso.";
            $this->flashMessenger()->addSuccessMessage($mensagem);
        } catch (\Doctrine\DBAL\DBALException $e) {
            if (strpos($e->getMessage(), 'SQLSTATE[23000]') > 0) {
                $mensagem = "Já existe uma unidade cadastrada com este nome";
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
        $this->redirect()->toRoute('unidades');
    }
    
    public function buscarAction() {
        $request = $this->getRequest();
        $busca = $this->params()->fromQuery('busca');
        
        if (($busca == null) || (!$request->isGet())) {
            $this->redirect()->toRoute('unidades');
            return;
        }
        
        $parametrosBusca = new ArrayCollection();
        $parametrosBusca->set('descricao', $busca);

        $unidadeDAO = new UnidadeDAO($this->getServiceLocator());
        $query = $unidadeDAO->busca($parametrosBusca);

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
            $this->flashMessenger()->addErrorMessage("Unidade não encontrada.");
            $this->redirect()->toRoute('unidades');
        }
        
        $viewModel = new ViewModel(array(
            'unidades' => $paginator,
            'orderby' => $this->params()->fromQuery('orderby'),
        ));
        $viewModel->setTemplate('application/manter-unidades/index.phtml');
        return $viewModel;
    }
    
    public function editarAction(){
        $request = $this->getRequest();
        $id = (int) $this->params()->fromRoute('id', 0);

        $unidadeDAO = new UnidadeDAO($this->getServiceLocator());
        $unidade = $unidadeDAO->lerPorId($id);

        if ($unidade == NULL) {
            $this->flashMessenger()->addMessage("Unidade não encotrada.");
            $this->redirect()->toRoute('unidades');
            return;
        }
        
        if (!$request->isPost()) {
            return array(
                'unidade' => $unidade
            );
        }

        $descricaoTxt = $request->getPost('descricaoTxt');

        $dadosFiltrados = new UnidadeFilter($descricaoTxt);

        if (!$dadosFiltrados->isValid()) {
            foreach ($dadosFiltrados->getInvalidInput() as $erro) {
                foreach ($erro->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            $this->redirect()->toRoute('unidades');
            return;
        }

        $parametros = new ArrayCollection();
        $parametros->set('descricao', $dadosFiltrados->getValue('descricaoTxt'));

        try {
            $unidadeDAO->editar($id, $parametros);
            $this->flashMessenger()->addSuccessMessage("Unidade editada com sucesso.");
        } catch (\Doctrine\DBAL\DBALException $e) {
            if (strpos($e->getMessage(), 'SQLSTATE[23000]') > 0) {
                $mensagem = "Já existe uma unidade cadastrada com este nome ";
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
        $this->redirect()->toRoute('unidades');
    }

    public function excluirAction() {
        $id = $this->params()->fromRoute('id');
        if (isset($id)) {
            try {
                $unidadeDAO = new UnidadeDAO($this->getServiceLocator());
                $unidadeDAO->excluir($id);
            } catch (\Exception $e) {
                $mensagem = "Ocorreu um erro na operação, tente novamente ou ";
                $mensagem.= "entre em contato com um administrador do sistema.";
                $this->flashMessenger()->addErrorMessage($mensagem);
                $this->redirect()->toRoute('unidades');
            }
            $this->flashMessenger()->addSuccessMessage("Unidade excluída com sucesso.");
            $this->redirect()->toRoute('unidades');
        }
    }
    
    public function visualizarAction(){
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->flashMessenger()->addMessage("Unidade não encotrada");
            $this->redirect()->toRoute('unidades');
        }
        try {
            $unidadeDAO = new UnidadeDAO($this->getServiceLocator());
            $unidade = $unidadeDAO->lerPorId($id);
        } catch (\Exception $e) {
            $mensagem = 'Ocorreu um erro na operação, tente novamente ou entre ';
            $mensagem.= 'em contato com um administrador do sistema.';
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('unidades');
        }
        if ($unidade != NULL) {
            return array('unidade' => $unidade);
        } else {
            $this->flashMessenger()->addMessage("Unidade não encotrada");
            $this->redirect()->toRoute('unidades');
        }
    }
}
