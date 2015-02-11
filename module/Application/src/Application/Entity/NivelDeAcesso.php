<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Description of NivelDeAcesso
 *
 * @author Irving Fernando de Medeiros Oliveira
 * @ORM\Entity
 */
class NivelDeAcesso {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $idNivelDeAcesso;
    /**
     * @ORM\Column(type="string", nullable=false, length=100)
     * @var string
     */
    private $nome;
    /**
     * @ORM\ManyToMany(targetEntity="Permissao", inversedBy="listaNiveisDeAcesso")
     * @ORM\JoinTable(name="NivelDeAcesso_has_Permissao",
     *                joinColumns={@ORM\JoinColumn(name="NivelDeAcesso_idNivelDeAcesso",
     *                                             referencedColumnName="idNivelDeAcesso",
     *                                             nullable=false)},
     *                inverseJoinColumns={@ORM\JoinColumn(name="Permissao_idPermissao",
     *                                                    referencedColumnName="idPermissao",
     *                                                    nullable=false)})
     * @var ArrayCollection
     */
    private $listaPermissoes;
    
    function __construct() {
        $this->listaPermissoes = new ArrayCollection();
    }
   
    public function __set($atrib, $value){
        $this->$atrib = $value;
    }
 
    public function __get($atrib){
        return $this->$atrib;
    }
    
    public function getIdNivelDeAcesso() {
        return $this->idNivelDeAcesso;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }
    
    public function addPermissao(Permissao $permissao){
        if(!$this->listaPermissoes->contains($permissao)){
            $this->listaPermissoes->set($permissao->getIdPermissao(), $permissao);
        }
    }
    
    public function getPermissao($key){
        if($this->listaPermissoes->containsKey($key)){
            return $this->listaPermissoes->get($key);
        }
        return;
    }
    
    public function removePermissao($key){
        if(!$this->listaPermissoes->containsKey($key)){
            return;
        }
        $this->listaPermissoes->remove($key);
    }
    
    public function getPermissoes(){
        return $this->listaPermissoes->toArray();
    }
    
    public function __toString() {
        return $this->nome;
    }
}
