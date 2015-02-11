<?php

/*
 * Copyright (C) 2014 Irving Fernando de Medeiros Oliveira
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

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Description of Recurso
 *
 * @author Irving Fernando de Medeiros Oliveira
 * @ORM\Entity
 */
class Recurso {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $idRecurso;
    /**
     * @ORM\Column(type="string", length=80, nullable=false)
     * @var string
     */
    private $nome;
    /**
     * @ORM\OneToMany(targetEntity="Permissao", mappedBy="recurso")
     * @var ArrayCollection
     */
    private $permissoes;
    
    public function __construct() {
        $this->permissoes = new ArrayCollection();
    }
        
    public function __set($atrib, $value){
        $this->$atrib = $value;
    }
 
    public function __get($atrib){
        return $this->$atrib;
    }
    
    public function getIdRecurso() {
        return $this->idRecurso;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setIdRecurso($idRecurso) {
        $this->idRecurso = $idRecurso;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function addPermissao(Permissao $permissao){
        if(!$this->permissoes->contains($permissao)){
            $this->permissoes->set($permissao->getIdPermissao(), $permissao);
        }  
    }
    
    public function getPermissao($key){
        if($this->permissoes->containsKey($key)){
            return $this->permissoes->get($key);
        }
    }
    
    public function removePermissao($key){
        if(!$this->permissoes->containsKey($key)){
            return;
        }
        $this->permissoes->remove($key);
    }
    
    public function getPermissoes(){
        return $this->permissoes->toArray();
    }

}
