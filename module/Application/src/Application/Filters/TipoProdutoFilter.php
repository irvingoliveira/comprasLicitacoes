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
 * Description of TipoProdutoFilter
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class TipoProdutoFilter extends InputFilter{
    
    private $descricaoTxt;
    private $tipoPaiSlct;
    
    private $tipoProdutoDAO;
    
    public function __construct($descricaoTxt, $tipoPaiSlct, DAOInterface $tipoProdutoDAO) {
        $this->descricaoTxt = $descricaoTxt;
        if(is_numeric($tipoPaiSlct))
            $this->tipoPaiSlct = $tipoPaiSlct;
        
        $this->tipoProdutoDAO = $tipoProdutoDAO;
        
        //var_dump($this->tipoPaiSlct);die();
        
        $this->add($this->getDescricaoTxtInput());
        $this->add($this->getTipoPaiSlctInput());
        
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
    
    public function getTipoPaiSlctInput(){
        $tiposProduto = $this->tipoProdutoDAO->lerTodos()->getResult();
        foreach ($tiposProduto as $tipoProduto) {
            $idTipoProduto[] = $tipoProduto->getIdTipoProduto();
        }
        $qtdTiposProduto = count($idTipoProduto);
        if(($qtdTiposProduto > 0) or (!is_null($this->tipoPaiSlct))){
            $tipoProdutoHayStack = new Validator\InArray(array('haystack' => $idTipoProduto));
            $tipoProdutoHayStack->setMessages(array(
                Validator\InArray::NOT_IN_ARRAY => 'Não foi escolhido um tipo de produto válido.'
            ));
        }

        $tipoPaiInputFilter = new Input('tipoPaiSlct');
        $tipoPaiInputFilter->setRequired(FALSE);
        if(($qtdTiposProduto > 0) or (!is_null($this->tipoPaiSlct)))
            $tipoPaiInputFilter->getValidatorChain()->attach($tipoProdutoHayStack);
        return $tipoPaiInputFilter;
    }

    public function preencherDados(){
        $this->setData(array(
            'descricaoTxt' => $this->descricaoTxt,
            'tipoPaiSlct' => $this->tipoPaiSlct,
        ));
    }
}
