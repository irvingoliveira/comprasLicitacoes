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
 * Description of Produto
 *
 * @author Irving Fernando de Medeiros Oliveira
 * @ORM\Entity
 */
class Produto {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $idProduto;
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
    private $tipo;
    /**
     * @ORM\ManyToOne(targetEntity="Unidade")
     * @ORM\JoinColumn(name="Unidade_idUnidade", 
     *                 referencedColumnName="idUnidade", 
     *                 nullable=false)
     * @var Unidade 
     */
    private $unidade;
    /**
     * @ORM\OneToMany(targetEntity="Preco", mappedBy="produto")
     * @var ArrayCollection
     */
    private $listaPrecos;
    
    function __construct() {
        $this->listaPrecos = new ArrayCollection();
    }    
        
    public function __set($atrib, $value){
        $this->$atrib = $value;
    }
 
    public function __get($atrib){
        return $this->$atrib;
    }
    
    public function getIdProduto() {
        return $this->idProduto;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getUnidade() {
        return $this->unidade;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setTipo(TipoProduto $tipo) {
        $this->tipo = $tipo;
    }

    public function setUnidade(Unidade $unidade) {
        $this->unidade = $unidade;
    }
    
    public function addPreco(Preco $preco){
        if(!$this->listaPrecos->contains($preco)){
            $this->listaPrecos->set($preco->getIdParecer(), $preco);
        }        
    }
    
    public function getPreco($key){
        if(!$this->listaPrecos->containsKey($key)){
            return null;
        }
        return $this->listaPrecos->get($key);
    }
    
    public function removePreco($key){
        if(!$this->listaPrecos->containsKey($key)){
            return;
        }
        $this->listaPrecos->remove($key);
    }
    
    public function getPrecos(){
        return $this->listaPrecos->toArray();
    }
}
