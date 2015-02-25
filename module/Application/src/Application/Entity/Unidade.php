<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Description of Unidade
 *
 * @author Irving Fernando de Medeiros Oliveira
 * @ORM\Entity
 */
class Unidade {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $idUnidade;
    /**
     * @ORM\Column(type="string", nullable=false, length=100)
     * @var string
     */
    private $descricao;
    
    function __construct() {
        
    }
        
    public function __set($atrib, $value){
        $this->$atrib = $value;
    }
 
    public function __get($atrib){
        return $this->$atrib;
    }
    
    public function getIdUnidade() {
        return $this->idUnidade;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }
    
    public function __toString() {
        return $this->descricao;
    }

}
