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
 * Description of Permissao
 *
 * @author Irving Fernando de Medeiros Oliveira
 * @ORM\Entity
 */
class Permissao {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $idPermissao;
    /**
     * @ORM\Column(type="string", length=80, nullable=false)
     * @var string
     */
    private $nome;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    private $permitido;
    /**
     * @ORM\ManyToOne(targetEntity="Recurso", inversedBy="permissoes")
     * @ORM\JoinColumn(name="Recurso_idRecurso",
     *                 referencedColumnName="idRecurso", nullable=false)
     * @var Recurso
     */
    private $recurso;
    /**
     * @ORM\ManyToMany(targetEntity="NivelDeAcesso", mappedBy="listaPermissoes")
     * @var ArrayCollection
     */
    private $listaNiveisDeAcesso;
    
    public function __construct() {
        $this->listaNiveisDeAcesso = new ArrayCollection();
    }
        
    public function __set($atrib, $value){
        $this->$atrib = $value;
    }
 
    public function __get($atrib){
        return $this->$atrib;
    }
    
    public function getIdPermissao() {
        return $this->idPermissao;
    }

    public function getNome() {
        return $this->nome;
    }

    public function isPermitido() {
        return $this->permitido;
    }

    public function getRecurso() {
        return $this->recurso;
    }

    public function setIdPermissao($idPermissao) {
        $this->idPermissao = $idPermissao;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setPermitido($permitido) {
        $this->permitido = $permitido;
    }

    public function setRecurso(Recurso $recurso) {
        $this->recurso = $recurso;
    }
    
    public function addNivelDeAcesso(NivelDeAcesso $nivelDeAcesso){
        if(!$this->listaNiveisDeAcesso->contains($nivelDeAcesso)){
            $this->listaNiveisDeAcesso->set($nivelDeAcesso->getIdNiveldeAcesso(), $nivelDeAcesso);
        }
    }
    
    public function getNiveldeAcesso($key){
        if(!$this->listaNiveisDeAcesso->containsKey($key)){
            return $this->listaNiveisDeAcesso->get($key);
        }
    }
    
    public function removeNiveldeAcesso($key){
        if(!$this->listaNiveisDeAcesso->containsKey($key)){
            return;
        }
        $this->listaNiveisDeAcesso->remove($key);
    }
    
    public function getNiveisDeAcesso(){
        return $this->listaNiveisDeAcesso->toArray();
    }
}
