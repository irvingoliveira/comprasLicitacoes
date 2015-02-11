<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Description of Preco
 *
 * @author Irving Fernando de Medeiros Oliveira
 * @ORM\Entity
 */
class Preco {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer",nullable=false)
     * @var integer
     */
    private $idPreco;
    /**
     * @ORM\Column(type="double",nullable=false)
     * @var double
     */
    private $valor;
    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     * @var string
     */
    private $pregao;
    /**
     * @ORM\Column(type="date", nullable=false)
     * @var \DateTime
     */
    private $dataPregao;
    /**
     * @ORM\ManyToOne(targetEntity="Produto", inversedBy="listaPrecos")
     * @ORM\JoinColumn(name="Produto_idProduto", 
     *                 referencedColumnName="idProduto", 
     *                 nullable=false)
     * @var Produto
     */
    private $produto;
    
    function __construct() {
        
    }
        
    public function __set($atrib, $value){
        $this->$atrib = $value;
    }
 
    public function __get($atrib){
        return $this->$atrib;
    }
    
    public function getIdPreco() {
        return $this->idPreco;
    }

    public function getValor() {
        return $this->valor;
    }

    public function getPregao() {
        return $this->pregao;
    }

    public function getDataPregao() {
        return $this->dataPregao;
    }

    public function getProduto() {
        return $this->produto;
    }

    public function setValor($valor) {
        $this->valor = $valor;
    }

    public function setPregao($pregao) {
        $this->pregao = $pregao;
    }

    public function setDataPregao(\DateTime $dataPregao) {
        $this->dataPregao = $dataPregao;
    }

    public function setProduto(Produto $produto) {
        $this->produto = $produto;
    }

}