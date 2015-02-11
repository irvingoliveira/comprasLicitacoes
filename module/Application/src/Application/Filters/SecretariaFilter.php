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
 * Description of SecretariaFilter
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
class SecretariaFilter extends InputFilter{
    
    private $nomeTxt;
    
    public function __construct($nomeTxt) {
        $this->nomeTxt = $nomeTxt;
        
        $this->add($this->getNomeTxtInput());
        
        $this->preencherDados();
    }
    
    public function getNomeTxtInput(){
        $nomeFilter = new Input('nomeTxt');

        $nomeStringLength = new Validator\StringLength(array('max' => 150, 'min' => 3));
        $nomeStringLength->setMessages(array(
            Validator\StringLength::TOO_SHORT =>
            'O nome é muito curto, o valor mínimo é de %min% caracteres.',
            Validator\StringLength::TOO_LONG =>
            'O nome é muito longo, o valor máximo é de %max% caracteres.'
        ));

        $nomeNotEmpty = new Validator\NotEmpty();
        $nomeNotEmpty->setMessage('O campo nome é obrigatório e não ser vazio.');
        
        $nomeFilter->getValidatorChain()
                ->attach($nomeStringLength)
                ->attach($nomeNotEmpty);
        $nomeFilter->getFilterChain()
                ->attach(new Filter\HtmlEntities())
                ->attach(new Filter\StringTrim())
                ->attach(new Filter\StripTags());

        return $nomeFilter;
    }
    
    public function preencherDados(){
        $this->setData(array(
            'nomeTxt' => $this->nomeTxt
        ));
    }
}
