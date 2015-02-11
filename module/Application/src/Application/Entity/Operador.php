<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Operador
 *
 * @author Irving Fernando de Medeiros Oliveira
 * @ORM\Entity
 */
class Operador {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $idOperador;
    /**
     * @ORM\Column(type="string", nullable=false, length=255)
     * @var string
     */
    private $nome;
     /**
     * @ORM\Column(type="string", length=100, nullable=false, unique=true)
     * @var string
     */
    private $email;
    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @var string
     */
    private $senha;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    private $ativo;
    /**
     * @ORM\ManyToOne(targetEntity="Secretaria")
     * @ORM\JoinColumn(name="Secretaria_idSecretaria", 
     *                 referencedColumnName="idSecretaria", 
     *                 nullable=false)
     * @var Secretaria
     */
    private $secretaria;
    /**
     * @ORM\ManyToOne(targetEntity="NivelDeAcesso")
     * @ORM\JoinColumn(name="NivelDeAcesso_idNivelDeAcesso", 
     *                 referencedColumnName="idNivelDeAcesso", 
     *                 nullable=false)
     * @var NivelDeAcesso
     */
    private $nivelDeAcesso;
    
    function __construct() {
        
    }
        
    public function __set($atrib, $value){
        $this->$atrib = $value;
    }
 
    public function __get($atrib){
        return $this->$atrib;
    }
    
    public function getIdOperador() {
        return $this->idOperador;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getSecretaria() {
        return $this->secretaria;
    }

    public function getNivelDeAcesso() {
        return $this->nivelDeAcesso;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setSecretaria(Secretaria $secretaria) {
        $this->secretaria = $secretaria;
    }

    public function setNivelDeAcesso(NivelDeAcesso $nivelDeAcesso) {
        $this->nivelDeAcesso = $nivelDeAcesso;
    }
    
    public function getEmail() {
        return $this->email;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }
    
    public function isAtivo() {
        return $this->ativo;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }
}