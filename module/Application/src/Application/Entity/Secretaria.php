<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Secretaria
 *
 * @author Irving Fernando de Medeiros Oliveira
 * @ORM\Entity
 */
class Secretaria {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $idSecretaria;
    /**
     * @ORM\Column(type="string", nullable=false, length=100)
     * @var string
     */
    private $nome;
    
    function __construct() {
        
    }
        
    public function __set($atrib, $value){
        $this->$atrib = $value;
    }
 
    public function __get($atrib){
        return $this->$atrib;
    }
    
    public function getIdSecretaria() {
        return $this->idSecretaria;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }
    
    public function __toString() {
        return $this->nome;
    }

}
