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
 * Description of PrecoFilter
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class PrecoFilter extends InputFilter{
    
    private $valorTxt;
    private $pregaoTxt;
    private $dataPregaoTxt;
    private $produtoSlct;
    
    private $produtoDAO;
    
    public function __construct($valorTxt, $pregaoTxt, $dataPregaoTxt, $produtoSlct, 
                                DAOInterface $produtoDAO) {
        
        $this->valorTxt = $valorTxt;
        $this->pregaoTxt = $pregaoTxt;
        $_dataPregaoTxt = explode("/", $dataPregaoTxt);
        $this->dataPregaoTxt = $_dataPregaoTxt[2].'-'
                              .$_dataPregaoTxt[1].'-'
                              .$_dataPregaoTxt[0];
        $this->produtoSlct = $produtoSlct;
        
        $this->produtoDAO = $produtoDAO;
        
        $this->add($this->getValorTxtInput());
        $this->add($this->getPregaoTxtInput());
        $this->add($this->getDataPregaoTxtInput());
        $this->add($this->getProdutoSlctInput());
        
        $this->preencherDados();
    }
    
    public function getValorTxtInput(){
        $valorInputFilter = new Input('valorTxt');

        $valorNotEmpty = new Validator\NotEmpty();
        $valorNotEmpty->setMessage('O campo valor é obrigatório e não ser vazio.');
        
        $valorInputFilter->getValidatorChain()
                ->attach($valorNotEmpty);
        $valorInputFilter->getFilterChain()
                ->attach(new Filter\HtmlEntities())
                ->attach(new Filter\StringTrim())
                ->attach(new Filter\StripTags());

        return $valorInputFilter;
    }
    
    public function getPregaoTxtInput(){
        $pregaoInputFilter = new Input('pregaoTxt');
        
        $pregaoStringLength = new Validator\StringLength(array('max' => 100, 
                                                                  'min' => 3));
        $pregaoStringLength->setMessages(array(
            Validator\StringLength::TOO_SHORT =>
            'O pregão é muito curto, o valor mínimo é de %min% caracteres.',
            Validator\StringLength::TOO_LONG =>
            'O pregão é muito longo, o valor máximo é de %max% caracteres.'
        ));
        
        $pregaoInputFilter->setRequired(FALSE);
        $pregaoInputFilter->getValidatorChain()
                ->attach($pregaoStringLength);
        $pregaoInputFilter->getFilterChain()
                ->attach(new Filter\HtmlEntities())
                ->attach(new Filter\StringTrim())
                ->attach(new Filter\StripTags());

        return $pregaoInputFilter;
    }
    
    public function getDataPregaoTxtInput(){
        $dataPregaoInputFilter = new Input('dataPregaoTxt');
        
        $dataPregaoNotEmpty = new Validator\NotEmpty();
        $dataPregaoNotEmpty->setMessage('O campo data do pregão é obrigatório e não ser vazio.');
        
        $dataPregaoDate = new Validator\Date();
        
        $dataPregaoInputFilter->setRequired(FALSE);
        $dataPregaoInputFilter->getValidatorChain()
                ->attach($dataPregaoNotEmpty)
                ->attach($dataPregaoDate);

        return $dataPregaoInputFilter;
    }
    
    public function getProdutoSlctInput(){
        $produtos = $this->produtoDAO->lerTodos()->getResult();
        foreach ($produtos as $produto) {
            $idProduto[] = $produto->getIdProduto();
        }

        $produtoHayStack = new Validator\InArray(array('haystack' => $idProduto));
        $produtoHayStack->setMessages(array(
            Validator\InArray::NOT_IN_ARRAY => 'Não foi escolhido produto válido.'
        ));

        $produtoNotEmpty = new Validator\NotEmpty();
        $produtoNotEmpty->setMessage('O campo produto é obrigatório e não ser vazio.');
        
        $produtoInputFilter = new Input('produtoSlct');
        $produtoInputFilter->setRequired(TRUE);
        $produtoInputFilter->getValidatorChain()
                ->attach($produtoHayStack)
                ->attach($produtoNotEmpty);
        return $produtoInputFilter;
    }
    
    public function preencherDados(){
        $this->setData(array(
            'valorTxt' => $this->valorTxt,
            'pregaoTxt' => $this->pregaoTxt,
            'dataPregaoTxt' => $this->dataPregaoTxt,
            'produtoSlct' => $this->produtoSlct
        ));
    }
}
