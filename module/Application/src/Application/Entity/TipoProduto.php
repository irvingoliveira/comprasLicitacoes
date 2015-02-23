<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Description of TipoProduto
 *
 * @author Irving Fernando de Medeiros Oliveira
 * @ORM\Entity
 */
class TipoProduto {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer",nullable=false)
     * @var int
     */
    private $idTipoProduto;
    /**
     * @ORM\Column(type="string", nullable=false, length=255)
     * @var string
     */
    private $descricao;
    /**
     * @ORM\ManyToOne(targetEntity="TipoProduto")
     * @ORM\JoinColumn(name="TipoProduto_idTipoProduto", 
     *                 referencedColumnName="idTipoProduto", 
     *                 nullable=false)
     * @var TipoProduto
     */
    private $tipoPai;
    
    function __construct() {
        
    }
        
    public function __set($atrib, $value){
        $this->$atrib = $value;
    }
 
    public function __get($atrib){
        return $this->$atrib;
    }
    
    public function getIdTipoProduto() {
        return $this->idTipoProduto;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function getTipoPai() {
        return $this->tipoPai;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setTipoPai(TipoProduto $tipoPai) {
        $this->tipoPai = $tipoPai;
    }

    public function __toString() {
        return $this->descricao;
    }
}
