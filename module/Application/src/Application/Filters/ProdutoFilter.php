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
namespace Application\Filters;

use Application\DAL\DAOInterface;

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator;
use Zend\Filter;
/**
 * Description of ProdutoFilter
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class ProdutoFilter extends InputFilter{
    
    private $descricaoTxt;
    private $tipoSlct;
    private $unidadeSlct;
    
    private $tipoProdutoDAO;
    private $unidadeDAO;
    
    public function __construct($descricaoTxt, $tipoSlct, $unidadeSlct,
            DAOInterface $tipoProdutoDAO, DAOInterface $unidadeDAO) {
        
        $this->descricaoTxt = $descricaoTxt;
        $this->tipoSlct = $tipoSlct;
        $this->unidadeSlct = $unidadeSlct;
        
        $this->tipoProdutoDAO = $tipoProdutoDAO;
        $this->unidadeDAO = $unidadeDAO;
        
        $this->add($this->getDescricaoTxtInput());
        $this->add($this->getTipoSlctInput());
        $this->add($this->getUnidadeSlctInput());
        
        $this->preencherDados();
    }
    
    public function getDescricaoTxtInput(){
        $descricaoInputFilter = new Input('descricaoTxt');

        $descricaoStringLength = new Validator\StringLength(array('max' => 255, 
                                                                  'min' => 3));
        $descricaoStringLength->setMessages(array(
            Validator\StringLength::TOO_SHORT =>
            'A descrição é muito curta, o valor mínimo é de %min% caracteres.',
            Validator\StringLength::TOO_LONG =>
            'A descrição é muito longa, o valor máximo é de %max% caracteres.'
        ));

        $descricaoNotEmpty = new Validator\NotEmpty();
        $descricaoNotEmpty->setMessage('O campo descrição é obrigatório e não ser vazio.');
        
        $descricaoInputFilter->getValidatorChain()
                ->attach($descricaoStringLength)
                ->attach($descricaoNotEmpty);
        $descricaoInputFilter->getFilterChain()
                ->attach(new Filter\HtmlEntities())
                ->attach(new Filter\StringTrim())
                ->attach(new Filter\StripTags());

        return $descricaoInputFilter;
    }
    
    public function getTipoSlctInput(){
        $tiposProduto = $this->tipoProdutoDAO->lerTodos()->getResult();
        foreach ($tiposProduto as $tipoProduto) {
            $idTipoProduto[] = $tipoProduto->getIdTipoProduto();
        }

        $tipoProdutoHayStack = new Validator\InArray(array('haystack' => $idTipoProduto));
        $tipoProdutoHayStack->setMessages(array(
            Validator\InArray::NOT_IN_ARRAY => 'Não foi escolhido um tipo de produto válido.'
        ));

        $tipoNotEmpty = new Validator\NotEmpty();
        $tipoNotEmpty->setMessage('O campo tipo é obrigatório e não ser vazio.');
        
        $tipoInputFilter = new Input('tipoSlct');
        $tipoInputFilter->setRequired(TRUE);
        $tipoInputFilter->getValidatorChain()
                ->attach($tipoProdutoHayStack)
                ->attach($tipoNotEmpty);
        return $tipoInputFilter;
    }
    
    public function getUnidadeSlctInput(){
        $unidades = $this->unidadeDAO->lerTodos()->getResult();
        foreach ($unidades as $unidade) {
            $idUnidade[] = $unidade->getIdUnidade();
        }
        $unidadeHayStack = new Validator\InArray(array('haystack' => $idUnidade));
        $unidadeHayStack->setMessages(array(
            Validator\InArray::NOT_IN_ARRAY => 'Não foi escolhida uma unidade válida.'
        ));

        $unidadeNotEmpty = new Validator\NotEmpty();
        $unidadeNotEmpty->setMessage('O campo unidade é obrigatório e não ser vazio.');
        
        $unidadeInputFilter = new Input('unidadeSlct');
        $unidadeInputFilter->setRequired(TRUE);
        $unidadeInputFilter->getValidatorChain()
                ->attach($unidadeHayStack)
                ->attach($unidadeNotEmpty);
        return $unidadeInputFilter;
    }

    public function preencherDados(){
        $this->setData(array(
            'descricaoTxt' => $this->descricaoTxt,
            'tipoSlct' => $this->tipoSlct,
            'unidadeSlct' => $this->unidadeSlct,
        ));
    }
}
