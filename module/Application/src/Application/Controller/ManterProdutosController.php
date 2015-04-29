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

use Application\DAL\ProdutoDAO;
use Application\DAL\TipoProdutoDAO;
use Application\DAL\UnidadeDAO;
use Application\Filters\ProdutoFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of ManterProdutosController
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class ManterProdutosController extends AbstractActionController {

    public function indexAction() {
        $produtoDAO = new ProdutoDAO($this->getServiceLocator());

        $ormPaginator = new ORMPaginator($produtoDAO->lerTodos());
        $ormPaginatorIterator = $ormPaginator->getIterator();

        $adapter = new Iterator($ormPaginatorIterator);

        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);
        $page = (int) $this->params()->fromQuery('page');
        if ($page) {
            $paginator->setCurrentPageNumber($page);
        }
        return array(
            'produtos' => $paginator,
            'orderby' => $this->params()->fromQuery('orderby'),
        );
    }

    public function adicionarAction() {
        $request = $this->getRequest();

        $tipoProdutoDAO = new TipoProdutoDAO($this->getServiceLocator());
        $produtoDAO = new ProdutoDAO($this->getServiceLocator());
        $unidadeDAO = new UnidadeDAO($this->getServiceLocator());

        if ($tipoProdutoDAO->getQtdRegistros() == 0) {
            $mensagem = "Para cadastrar um produto você deve primeiramente cad";
            $mensagem.= "astrar um tipo de produto.";
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('produtos');
            return;
        }

        if ($unidadeDAO->getQtdRegistros() == 0) {
            $mensagem = "Para cadastrar um produto você deve primeiramente cad";
            $mensagem.= "astrar uma unidade.";
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('produtos');
            return;
        }

        if (!$request->isPost()) {
            $tiposProduto = $tipoProdutoDAO->lerRepositorio();
            $unidades = $unidadeDAO->lerRepositorio();

            $viewModel = new ViewModel(array(
                'tiposProduto' => $tiposProduto,
                'unidades' => $unidades,
            ));
            $viewModel->setTemplate('application/manter-produtos/editar.phtml');
            return $viewModel;
        }

        $descricaoTxt = $request->getPost('descricaoTxt');
        $tipoSlct = $request->getPost('tipoSlct');
        $unidadeSlct = $request->getPost('unidadeSlct');

        $dadosFiltrados = new ProdutoFilter($descricaoTxt, $tipoSlct, $unidadeSlct, $tipoProdutoDAO, $unidadeDAO);

        if (!$dadosFiltrados->isValid()) {
            foreach ($dadosFiltrados->getInvalidInput() as $erro) {
                foreach ($erro->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            $this->redirect()->toUrl('/produtos/adicionar');
            return;
        }

        $parametros = new ArrayCollection();
        $parametros->set('descricao', $dadosFiltrados->getValue('descricaoTxt'));
        $parametros->set('tipo', $tipoProdutoDAO->lerPorId(
                        $dadosFiltrados->getValue('tipoSlct')));
        $parametros->set('unidade', $unidadeDAO->lerPorId(
                        $dadosFiltrados->getValue('unidadeSlct')));

        try {
            $produtoDAO->salvar($parametros);
            $mensagem = "Produto adicionado com sucesso.";
            $this->flashMessenger()->addSuccessMessage($mensagem);
        } catch (\Doctrine\DBAL\DBALException $e) {
            if (strpos($e->getMessage(), 'SQLSTATE[23000]') > 0) {
                $mensagem = "Já existe um produto cadastrado com este nome.";
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
        $this->redirect()->toRoute('produtos');
    }

    public function buscarAction() {
        $request = $this->getRequest();
        $busca = $this->params()->fromQuery('busca');

        if (($busca == null) || (!$request->isGet())) {
            $this->redirect()->toRoute('produtos');
            return;
        }

        $parametrosBusca = new ArrayCollection();
        $parametrosBusca->set('descricao', $busca);

        $produtoDAO = new ProdutoDAO($this->getServiceLocator());
        $query = $produtoDAO->busca($parametrosBusca);

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
            $this->flashMessenger()->addErrorMessage("Produto não encontrado.");
            $this->redirect()->toRoute('produtos');
        }

        $viewModel = new ViewModel(array(
            'produtos' => $paginator,
            'orderby' => $this->params()->fromQuery('orderby'),
        ));
        $viewModel->setTemplate('application/manter-produtos/index.phtml');
        return $viewModel;
    }

    public function editarAction() {
        $request = $this->getRequest();
        $id = (int) $this->params()->fromRoute('id', 0);

        $unidadeDAO = new UnidadeDAO($this->getServiceLocator());
        $tipoProdutoDAO = new TipoProdutoDAO($this->getServiceLocator());
        $produtoDAO = new ProdutoDAO($this->getServiceLocator());

        if ($tipoProdutoDAO->getQtdRegistros() == 0) {
            $mensagem = "Para editar um produto você deve primeiramente cad";
            $mensagem.= "astrar um tipo de produto.";
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('produtos');
            return;
        }

        if ($unidadeDAO->getQtdRegistros() == 0) {
            $mensagem = "Para editar um produto você deve primeiramente cad";
            $mensagem.= "astrar uma unidade.";
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('produtos');
            return;
        }

        $produto = $produtoDAO->lerPorId($id);

        if ($produto == NULL) {
            $this->flashMessenger()->addMessage("Produto não encotrado.");
            $this->redirect()->toRoute('produtos');
            return;
        }

        if (!$request->isPost()) {
            $unidades = $unidadeDAO->lerRepositorio();
            $tiposProduto = $tipoProdutoDAO->lerRepositorio();
            return array(
                'unidades' => $unidades,
                'tiposProduto' => $tiposProduto,
                'produto' => $produto
            );
        }

        $descricaoTxt = $request->getPost('descricaoTxt');
        $tipoSlct = $request->getPost('tipoSlct');
        $unidadeSlct = $request->getPost('unidadeSlct');

        $dadosFiltrados = new ProdutoFilter($descricaoTxt, $tipoSlct, $unidadeSlct, $tipoProdutoDAO, $unidadeDAO);

        if (!$dadosFiltrados->isValid()) {
            foreach ($dadosFiltrados->getInvalidInput() as $erro) {
                foreach ($erro->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            $this->redirect()->toRoute('produtos');
            return;
        }

        $parametros = new ArrayCollection();
        $parametros->set('descricao', $dadosFiltrados->getValue('descricaoTxt'));
        $parametros->set('tipo', $tipoProdutoDAO->lerPorId(
                        $dadosFiltrados->getValue('tipoSlct')));
        $parametros->set('unidade', $unidadeDAO->lerPorId(
                        $dadosFiltrados->getValue('unidadeSlct')));

        try {
            $produtoDAO->editar($id, $parametros);
            $this->flashMessenger()->addSuccessMessage("Produto editado com sucesso.");
        } catch (\Doctrine\DBAL\DBALException $e) {
            if (strpos($e->getMessage(), 'SQLSTATE[23000]') > 0) {
                $mensagem = "Já existe um produto cadastrado com este nome.";
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
        $this->redirect()->toRoute('produtos');
    }

    public function excluirAction() {
        $id = $this->params()->fromRoute('id');
        if (isset($id)) {
            try {
                $produtoDAO = new ProdutoDAO($this->getServiceLocator());
                $produtoDAO->excluir($id);
            } catch (\Exception $e) {
                $needle = 'SQLSTATE[23000]: Integrity constraint violation';
                if(strpos($e->getMessage(), $needle)){
                    $mensagem = "Este produto não pode ser excluído pois existe "; 
                    $mensagem.= "um preço cadastrado referente a ele.";
                }else{
                    $mensagem = "Ocorreu um erro na operação, tente novamente ou ";
                    $mensagem.= "entre em contato com um administrador do sistema.";
                }
                $this->flashMessenger()->addErrorMessage($mensagem);
                $this->redirect()->toRoute('produtos');
            }
            $mensagem = "Produto excluído com sucesso.";
            $this->flashMessenger()->addSuccessMessage($mensagem);
            $this->redirect()->toRoute('produtos');
        }
    }

    public function visualizarAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->flashMessenger()->addMessage("Produto não encotrado");
            $this->redirect()->toRoute('produtos');
        }
        try {
            $produtoDAO = new ProdutoDAO($this->getServiceLocator());
            $produto = $produtoDAO->lerPorId($id);
        } catch (\Exception $e) {
            $mensagem = 'Ocorreu um erro na operação, tente novamente ou entre ';
            $mensagem.= 'em contato com um administrador do sistema.';
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('produtos');
        }
        if ($produto != NULL) {
            return array('produto' => $produto);
        } else {
            $this->flashMessenger()->addMessage("Produto não encotrado");
            $this->redirect()->toRoute('produtos');
        }
    }

    public function autoCompleteAction(){
        $termo = $this->params()->fromQuery('term');
        $produtoDAO = new ProdutoDAO($this->getServiceLocator());
        $parametro = new ArrayCollection();
        $parametro->set('descricao', $termo . '%');
        $produtos = $produtoDAO->busca($parametro)->getResult();

        $json = '[';
        foreach ($produtos as $key => $produto) {
            if ($key != 0)
                $json .= ',';
            $json.= '{"value":"' . $produto->getDescricao() . '"}';
        }
        $json.= ']';

        echo $json;
        die();
    }
}
