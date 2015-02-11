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

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator;
use Zend\Filter;
/**
 * Description of UnidadeFilter
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class UnidadeFilter extends InputFilter{
    
    private $descricaoTxt;
    
    public function __construct($descricaoTxt) {
        $this->descricaoTxt = $descricaoTxt;
        
        $this->add($this->getDescricaoTxtInput());
        
        $this->preencherDados();
    }
    
    public function getDescricaoTxtInput(){
        $descricaoFilter = new Input('descricaoTxt');

        $descricaoStringLength = new Validator\StringLength(array('max' => 100, 'min' => 3));
        $descricaoStringLength->setMessages(array(
            Validator\StringLength::TOO_SHORT =>
            'A descrição é muito curta, o valor mínimo é de %min% caracteres.',
            Validator\StringLength::TOO_LONG =>
            'A descrição é muito longa, o valor máximo é de %max% caracteres.'
        ));

        $descricaoNotEmpty = new Validator\NotEmpty();
        $descricaoNotEmpty->setMessage('O campo descrição é obrigatório e não ser vazio.');
        
        $descricaoFilter->getValidatorChain()
                ->attach($descricaoStringLength)
                ->attach($descricaoNotEmpty);
        $descricaoFilter->getFilterChain()
                ->attach(new Filter\HtmlEntities())
                ->attach(new Filter\StringTrim())
                ->attach(new Filter\StripTags());

        return $descricaoFilter;
    }
    
    public function preencherDados(){
        $this->setData(array(
            'descricaoTxt' => $this->descricaoTxt
        ));
    }
}
