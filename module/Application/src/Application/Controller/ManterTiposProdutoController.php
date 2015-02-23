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

use Application\DAL\TipoProdutoDAO;
use Application\Filters\TipoProdutoFilter;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Description of ManterTiposProdutoController
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class ManterTiposProdutoController extends AbstractActionController{
    public function indexAction() {
         $request = $this->getRequest();

        if (!$request->isPost()) {
            $tipoProdutoDAO = new TipoProdutoDAO($this->getServiceLocator());

            $ormPaginator = new ORMPaginator($tipoProdutoDAO->lerTodos());
            $ormPaginatorIterator = $ormPaginator->getIterator();

            $adapter = new Iterator($ormPaginatorIterator);

            $paginator = new Paginator($adapter);
            $paginator->setDefaultItemCountPerPage(10);
            $page = (int) $this->params()->fromQuery('page');
            if ($page) {
                $paginator->setCurrentPageNumber($page);
            }
            return array(
                'tiposProduto' => $paginator,
                'orderby' => $this->params()->fromQuery('orderby'),
            );
        }
    }
    
    public function adicionarAction() {
        $request = $this->getRequest();
        $tipoProdutoDAO = new TipoProdutoDAO($this->getServiceLocator());
        
        if(!$request->isPost()){
            $tiposProduto = $tipoProdutoDAO->lerRepositorio();
            $viewModel = new ViewModel(array(
                'tiposProduto' => $tiposProduto
            ));
            $viewModel->setTemplate('application/manter-tipos-produto/editar.phtml');
            return $viewModel;
        }
        
        $descricaoTxt = $request->getPost('descricaoTxt');
        $tipoPaiSlct = $request->getPost('tipoPaiSlct');

        $dadosFiltrados = new TipoProdutoFilter($descricaoTxt, $tipoPaiSlct, $tipoProdutoDAO);

        if (!$dadosFiltrados->isValid()) {
            foreach ($dadosFiltrados->getInvalidInput() as $erro) {
                foreach ($erro->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            $this->redirect()->toUrl('/tiposproduto/adicionar');
            return;
        }

        $parametros = new ArrayCollection();
        $parametros->set('descricao', $dadosFiltrados->getValue('descricaoTxt'));
        $parametros->set('tipoPai', $tipoProdutoDAO->lerPorId(
                                        $dadosFiltrados->getValue('tipoPaiSlct')));

        try {
            $tipoProdutoDAO->salvar($parametros);
            $mensagem = "Tipo de produto adicionado com sucesso.";
            $this->flashMessenger()->addSuccessMessage($mensagem);
        } catch (\Doctrine\DBAL\DBALException $e) {
            if (strpos($e->getMessage(), 'SQLSTATE[23000]') > 0) {
                $mensagem = "Já existe um tipo de produto cadastrado com este nome.";
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
        $this->redirect()->toRoute('tiposproduto');
    }
    
    public function buscarAction() {
        $request = $this->getRequest();
        $busca = $this->params()->fromQuery('busca');
        
        if (($busca == null) || (!$request->isGet())) {
            $this->redirect()->toRoute('tiposproduto');
            return;
        }
        
        $parametrosBusca = new ArrayCollection();
        $parametrosBusca->set('descricao', $busca);

        $tipoProdutoDAO = new TipoProdutoDAO($this->getServiceLocator());
        $query = $tipoProdutoDAO->busca($parametrosBusca);

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
            $this->flashMessenger()->addErrorMessage("Tipo de produto não encontrado.");
            $this->redirect()->toRoute('tiposproduto');
        }
        
        $viewModel = new ViewModel(array(
            'tiposproduto' => $paginator,
            'orderby' => $this->params()->fromQuery('orderby'),
        ));
        $viewModel->setTemplate('application/manter-tipos-produto/index.phtml');
        return $viewModel;
    }
    
    public function editarAction(){
        $request = $this->getRequest();
        $id = (int) $this->params()->fromRoute('id', 0);

        $tipoProdutoDAO = new TipoProdutoDAO($this->getServiceLocator());
        $tipoProduto = $tipoProdutoDAO->lerPorId($id);

        if ($tipoProduto == NULL) {
            $this->flashMessenger()->addMessage("Tipo de produto não encotrado.");
            $this->redirect()->toRoute('tiposproduto');
            return;
        }
        
        if (!$request->isPost()) {
            $tiposProduto = $tipoProdutoDAO->lerRepositorio();
            return array(
                'tiposProduto' => $tiposProduto,
                'tipoProduto' => $tipoProduto
            );
        }

        $descricaoTxt = $request->getPost('descricaoTxt');
        $tipoPaiSlct = $request->getPost('tipoPaiSlct');

        $dadosFiltrados = new TipoProdutoFilter($descricaoTxt, $tipoPaiSlct, 
                $tipoProdutoDAO);

        if (!$dadosFiltrados->isValid()) {
            foreach ($dadosFiltrados->getInvalidInput() as $erro) {
                foreach ($erro->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            $this->redirect()->toRoute('tiposproduto');
            return;
        }

        $parametros = new ArrayCollection();
        $parametros->set('descricao', $dadosFiltrados->getValue('descricaoTxt'));
        $parametros->set('tipoPai', $tipoProdutoDAO->lerPorId(
                                    $dadosFiltrados->getValue('tipoPaiSlct')));

        try {
            $tipoProdutoDAO->editar($id, $parametros);
            $this->flashMessenger()->addSuccessMessage("Tipo de produto editado com sucesso.");
        } catch (\Doctrine\DBAL\DBALException $e) {
            if (strpos($e->getMessage(), 'SQLSTATE[23000]') > 0) {
                $mensagem = "Já existe um tipo de produto cadastrado com este nome.";
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
        $this->redirect()->toRoute('tiposproduto');
    }

    public function excluirAction() {
        $id = $this->params()->fromRoute('id');
        if (isset($id)) {
            try {
                $tipoProdutoDAO = new TipoProdutoDAO($this->getServiceLocator());
                $tipoProdutoDAO->excluir($id);
            } catch (\Doctrine\DBAL\DBALException $e) {
                $sql_error = 'SQLSTATE[23000]: Integrity constraint violation';
                if (strpos($e->getMessage(), $sql_error) > 0) {
                    $mensagem = "Você não pode excluir este tipo de produto, pois, ";
                    $mensagem.= "existem outros tipos de produtos vinculados a ele.";
                } else {
                    $mensagem = "Ocorreu um erro na operação, tente novamente ";
                    $mensagem.= "ou entre em contato com um administrador ";
                    $mensagem.= "do sistema.";
                }
                $this->flashMessenger()->addErrorMessage($mensagem);
                $this->redirect()->toRoute('tiposproduto');
            }catch (\Exception $e) {
                echo $e;
                $mensagem = "Ocorreu um erro na operação, tente novamente ou ";
                $mensagem.= "entre em contato com um administrador do sistema.";
                $this->flashMessenger()->addErrorMessage($mensagem);
                $this->redirect()->toRoute('tiposproduto');
            }
            $this->flashMessenger()->addSuccessMessage("Tipo de produto excluído com sucesso.");
            $this->redirect()->toRoute('tiposproduto');
        }
    }
    
    public function visualizarAction(){
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->flashMessenger()->addMessage("Tipo produto não encotrado");
            $this->redirect()->toRoute('tiposproduto');
        }
        try {
            $tipoProdutoDAO = new TipoProdutoDAO($this->getServiceLocator());
            $tipoProduto = $tipoProdutoDAO->lerPorId($id);
        } catch (\Exception $e) {
            $mensagem = 'Ocorreu um erro na operação, tente novamente ou entre ';
            $mensagem.= 'em contato com um administrador do sistema.';
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('tiposproduto');
        }
        if ($tipoProduto != NULL) {
            return array('tipoProduto' => $tipoProduto);
        } else {
            $this->flashMessenger()->addMessage("Tipo de produto não encotrado");
            $this->redirect()->toRoute('tiposproduto');
        }
    }
}
