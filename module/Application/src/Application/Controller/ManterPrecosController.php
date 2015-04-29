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

use Application\DAL\PrecoDAO;
use Application\DAL\ProdutoDAO;

use Application\Filters\PrecoFilter;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of ManterPrecoController
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class ManterPrecosController extends AbstractActionController {

    public function indexAction() {
        $precoDAO = new PrecoDAO($this->getServiceLocator());

        $ormPaginator = new ORMPaginator($precoDAO->lerTodos());
        $ormPaginatorIterator = $ormPaginator->getIterator();

        $adapter = new Iterator($ormPaginatorIterator);

        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);
        $page = (int) $this->params()->fromQuery('page');
        if ($page) {
            $paginator->setCurrentPageNumber($page);
        }
        return array(
            'precos' => $paginator,
            'orderby' => $this->params()->fromQuery('orderby'),
        );
    }

    public function adicionarAction() {
        $request = $this->getRequest();

        $precoDAO = new PrecoDAO($this->getServiceLocator());
        $produtoDAO = new ProdutoDAO($this->getServiceLocator());

        if ($produtoDAO->getQtdRegistros() == 0) {
            $mensagem = "Para cadastrar um preço você deve primeiramente cad";
            $mensagem.= "astrar um produto.";
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('precos');
            return;
        }

        if (!$request->isPost()) {
            $produtos = $produtoDAO->lerRepositorio();

            $viewModel = new ViewModel(array(
                'produtos' => $produtos
            ));
            $viewModel->setTemplate('application/manter-precos/editar.phtml');
            return $viewModel;
        }

        $valorTxt = $request->getPost('valorTxt');
        $pregaoTxt = $request->getPost('pregaoTxt');
        $dataPregaoTxt = $request->getPost('dataPregaoTxt');
        $produtoSlct = $request->getPost('produtoSlct');

        $dadosFiltrados = new PrecoFilter($valorTxt, $pregaoTxt, $dataPregaoTxt, 
                                          $produtoSlct, $produtoDAO);

        if (!$dadosFiltrados->isValid()) {
            foreach ($dadosFiltrados->getInvalidInput() as $erro) {
                foreach ($erro->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            $this->redirect()->toUrl('/preco/adicionar');
            return;
        }

        $parametros = new ArrayCollection();
        $valor = str_replace(',', '.', 
                str_replace('.', '', $dadosFiltrados->getValue('valorTxt')));
        $parametros->set('valor', $valor);
        if($dadosFiltrados->getValue('pregaoTxt') != '')
            $parametros->set('pregao', $dadosFiltrados->getValue('pregaoTxt'));
        $_dataPregao = explode("-", $dadosFiltrados->getValue('dataPregaoTxt'));
        $dataPregao = new \DateTime();
        $dataPregao->setDate($_dataPregao[0], $_dataPregao[1], $_dataPregao[2]);
        $parametros->set('dataPregao', $dataPregao);
        $parametros->set('produto', $produtoDAO->lerPorId(
                        $dadosFiltrados->getValue('produtoSlct')));

        try {
            $precoDAO->salvar($parametros);
            $mensagem = "Preço adicionado com sucesso.";
            $this->flashMessenger()->addSuccessMessage($mensagem);
        } catch (\Exception $e) {
            echo $e->getMessage();die();
            $mensagem = "Ocorreu um erro na operação, tente novamente ";
            $mensagem .= "ou entre em contato com um administrador ";
            $mensagem .= "do sistema.";
            $this->flashMessenger()->addErrorMessage($mensagem);
        }
        $this->redirect()->toRoute('precos');
    }

    //TODO
    public function buscarAction() {
        $request = $this->getRequest();
        $busca = $this->params()->fromQuery('busca');

        if (($busca == null) || (!$request->isGet())) {
            $this->redirect()->toRoute('precos');
            return;
        }

        $parametrosBuscaProduto = new ArrayCollection();
        $parametrosBuscaProduto->set('descricao', $busca);
        
        $produtoDAO = new ProdutoDAO($this->getServiceLocator());
        $produto = $produtoDAO->buscaPersonalizada($parametrosBuscaProduto)->getResult();
        
        $parametrosBusca = new ArrayCollection();
        $parametrosBusca->set('produto', $produto);

        $precoDAO = new PrecoDAO($this->getServiceLocator());
        $query = $precoDAO->buscaPersonalizada($parametrosBusca);

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
            $this->redirect()->toRoute('precos');
        }

        $viewModel = new ViewModel(array(
            'precos' => $paginator,
            'orderby' => $this->params()->fromQuery('orderby'),
        ));
        $viewModel->setTemplate('application/manter-precos/index.phtml');
        return $viewModel;
    }

    public function editarAction() {
        $request = $this->getRequest();
        $id = (int) $this->params()->fromRoute('id', 0);

        $precoDAO = new PrecoDAO($this->getServiceLocator());
        $produtoDAO = new ProdutoDAO($this->getServiceLocator());

        if ($produtoDAO->getQtdRegistros() == 0) {
            $mensagem = "Para cadastrar um preço você deve primeiramente cad";
            $mensagem.= "astrar um produto.";
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('precos');
            return;
        }

        $preco = $precoDAO->lerPorId($id);

        if ($preco == NULL) {
            $this->flashMessenger()->addMessage("Preço não encotrado.");
            $this->redirect()->toRoute('precos');
            return;
        }

        if (!$request->isPost()) {
            $produtos = $produtoDAO->lerRepositorio();

            $viewModel = new ViewModel(array(
                'produtos' => $produtos,
                'preco' => $preco
            ));
            return $viewModel;
        }
        
        $valorTxt = $request->getPost('valorTxt');
        $pregaoTxt = $request->getPost('pregaoTxt');
        $dataPregaoTxt = $request->getPost('dataPregaoTxt');
        $produtoSlct = $request->getPost('produtoSlct');

        $dadosFiltrados = new PrecoFilter($valorTxt, $pregaoTxt, $dataPregaoTxt, $produtoSlct, $produtoDAO);

        if (!$dadosFiltrados->isValid()) {
            foreach ($dadosFiltrados->getInvalidInput() as $erro) {
                foreach ($erro->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            $this->redirect()->toUrl('/preco/adicionar');
            return;
        }

        $parametros = new ArrayCollection();
        $valor = str_replace(',', '.', 
                str_replace('.', '', $dadosFiltrados->getValue('valorTxt')));
        $parametros->set('valor', $valor);
        $parametros->set('pregao', $dadosFiltrados->getValue('pregaoTxt'));
        $_dataPregao = explode("-", $dadosFiltrados->getValue('dataPregaoTxt'));
        $dataPregao = new \DateTime();
        $dataPregao->setDate($_dataPregao[0], $_dataPregao[1], $_dataPregao[2]);
        $parametros->set('dataPregao', $dataPregao);
        $parametros->set('produto', $produtoDAO->lerPorId(
                        $dadosFiltrados->getValue('produtoSlct')));

        try {
            $precoDAO->editar($id, $parametros);
            $this->flashMessenger()->addSuccessMessage("Preco editado com sucesso.");
        } catch (\Exception $e) {
            $mensagem = "Ocorreu um erro na operação, tente novamente ";
            $mensagem .= "ou entre em contato com um administrador ";
            $mensagem .= "do sistema.";
            $this->flashMessenger()->addErrorMessage($mensagem);
        }
        $this->redirect()->toRoute('precos');
    }

    public function visualizarAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->flashMessenger()->addMessage("Preco não encotrado");
            $this->redirect()->toRoute('precos');
        }
        try {
            $precoDAO = new PrecoDAO($this->getServiceLocator());
            $preco = $precoDAO->lerPorId($id);
        } catch (\Exception $e) {
            $mensagem = 'Ocorreu um erro na operação, tente novamente ou entre ';
            $mensagem.= 'em contato com um administrador do sistema.';
            $this->flashMessenger()->addErrorMessage($mensagem);
            $this->redirect()->toRoute('precos');
        }
        if ($preco != NULL) {
            return array('preco' => $preco);
        } else {
            $this->flashMessenger()->addMessage("Preço não encotrado");
            $this->redirect()->toRoute('precos');
        }
    }
}
