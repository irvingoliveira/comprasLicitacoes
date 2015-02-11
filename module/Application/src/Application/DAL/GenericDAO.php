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

namespace Application\DAL;

use Zend\ServiceManager\ServiceManager;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of GenericDAO
 *
 * @author Irving Fernando de Medeiros Oliveira
 */
abstract class GenericDAO implements DAOInterface {

    private $serviceManager;
    private $objectManager;

    protected function __construct(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        $this->objectManager = $serviceManager->get('ObjectManager');
    }

    /**
     * 
     * @return Doctrine\ORM\EntityManager
     */
    protected function getObjectManager() {
        if (!$this->objectManager->isOpen()) {
            $this->objectManager = $this->objectManager->create(
                    $this->objectManager->getConnection(), $this->objectManager->getConfiguration()
            );
        }
        return $this->objectManager;
    }

    protected function getServiceManager() {
        return $this->serviceManager;
    }

    public function busca(ArrayCollection $parametros) {
        $dql = 'SELECT o FROM Application\Entity\\' . $this->getNomeDaClasse() . ' AS o ';
        $dql.= "WHERE o.".$parametros->key()." LIKE ?1";
        $objectManager = $this->getObjectManager();
        $query = $objectManager->createQuery($dql);
        $query->setParameter(1, '%'.$parametros->current().'%');
        return $query;
    }

    public function buscaExata($parametro) {
        $dql = 'SELECT o FROM Application\Entity\\' . $this->getNomeDaClasse() . ' AS o ';
        $dql.= "WHERE o.nome = ?1";
        $objectManager = $this->getObjectManager();
        $query = $objectManager->createQuery($dql);
        $query->setParameter(1, $parametro);
        return $query->getResult();
    }

    public function buscaPersonalizada(ArrayCollection $params) {
        $objectManager = $this->getObjectManager();
        $i = 1;
        $j = 1;
        $dql = 'SELECT o FROM Application\Entity\\' . $this->getNomeDaClasse() . ' AS o ';
        $dql.= 'WHERE o.' . $params->key() . ' = ?' . $i . ' ';
        while (TRUE) {
            if ($params->next() == NULL)
                break;
            $dql.= 'AND o.' . $params->key() . ' = ?' . ++$i . ' ';
        }
        $query = $objectManager->createQuery($dql);
        $params->first();
        while (TRUE) {
            $query->setParameter($j++, $params->current());
            if ($params->next() == NULL) {
                break;
            }
        }
        return $query;
    }

    public function lerPorId($id) {
        if ($id == NULL)
            return;
        $objectManager = $this->getObjectManager();
        $objetos = $objectManager
                ->getRepository('Application\Entity\\' . $this->getNomeDaClasse());
        $objeto = $objetos->find($id);
        return $objeto;
    }

    private final function getNomeDaClasse(){
        $nomeDaClasseDAO = get_class($this);
        return substr($nomeDaClasseDAO,16,-3);
    }

    public function lerTodos() {
        $dql = 'SELECT o FROM Application\Entity\\' 
                . $this->getNomeDaClasse() . ' AS o';
        $objectManager = $this->getObjectManager();
        $query = $objectManager->createQuery($dql);
        return $query;
    }

    public function getIdHaystack() {
        $dql = 'SELECT o.id' . $this->getNomeDaClasse() . ' '
                . 'FROM Application\Entity\\' . $this->getNomeDaClasse() . ' AS o';
        $objectManager = $this->getObjectManager();
        $query = $objectManager->createQuery($dql);
        return $query->getResult();
    }

    public function salvar(ArrayCollection $params) {
        $reflector = new \ReflectionClass('Application\Entity\\' 
                                        . $this->getNomeDaClasse());
        $objeto = $reflector->newInstance();
        while (TRUE) {
            $objeto->{$params->key()} = $params->current();
            if ($params->next() == NULL) {
                break;
            }
        }
        $objectManager = $this->getObjectManager();
        $objectManager->persist($objeto);
        $objectManager->flush();
        return $objectManager->find('Application\Entity\\' 
                                    . $this->getNomeDaClasse(), 
                                $objeto->{'id' . $this->getNomeDaClasse()});
    }

    public function editar($id, ArrayCollection $params) {
        if ($id == NULL)
            return;
        $objectManager = $this->getObjectManager();
        $objetos = $objectManager->getRepository('Application\Entity\\' 
                                                . $this->getNomeDaClasse());
        $objeto = $objetos->find($id);
        while (TRUE) {
            $objeto->{$params->key()} = $params->current();
            if ($params->next() == NULL) {
                break;
            }
        }
        $objectManager->persist($objeto);
        $objectManager->flush();
        return $objectManager->find('Application\Entity\\' 
                                    . $this->getNomeDaClasse(), 
                                $objeto->{'id' . $this->getNomeDaClasse()});
    }

    public function excluir($id) {
        if ($id == NULL)
            return;
        $objectManager = $this->getObjectManager();
        $objetos = $objectManager
                ->getRepository('Application\Entity\\' . $this->getNomeDaClasse());
        $objeto = $objetos->find($id);
        $objectManager->remove($objeto);
        $objectManager->flush();
    }

    public function getQtdRegistros() {
        $dql = 'SELECT COUNT(o) ';
        $dql.= 'FROM Application\Entity\\' . $this->getNomeDaClasse() . ' o';
        return $this->objectManager->createQuery($dql)->getSingleScalarResult();
    }

    public function lerRepositorio() {
        return $this->objectManager->getRepository('Application\Entity\\' 
                                                    . $this->getNomeDaClasse())
                                   ->findAll();
    }

    public function getRepositorio() {
        return $this->objectManager->getRepository('Application\Entity\\' 
                                                    . $this->getNomeDaClasse());
    }

}
