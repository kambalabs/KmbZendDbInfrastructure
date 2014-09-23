<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/kambalabs for the sources repositories
 *
 * This file is part of Kamba.
 *
 * Kamba is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Kamba is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kamba.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbZendDbInfrastructure\Proxy;

use KmbDomain\Model\Parameter;
use KmbDomain\Model\ParameterInterface;
use KmbDomain\Model\ParameterRepositoryInterface;
use KmbDomain\Model\PuppetClassInterface;
use KmbDomain\Model\PuppetClassRepositoryInterface;

class ParameterProxy implements ParameterInterface
{
    /** @var  PuppetClassRepositoryInterface */
    protected $classRepository;

    /** @var  ParameterRepositoryInterface */
    protected $parameterRepository;

    /** @var  Parameter */
    protected $aggregateRoot;

    /** @var  PuppetClassInterface */
    protected $class;

    /** @var  ParameterInterface */
    protected $parent;

    /** @var  ParameterInterface[] */
    protected $children;

    /**
     * Set AggregateRoot.
     *
     * @param \GtnPersistBase\Model\AggregateRootInterface $aggregateRoot
     * @return PuppetClassProxy
     */
    public function setAggregateRoot($aggregateRoot)
    {
        $this->aggregateRoot = $aggregateRoot;
        return $this;
    }

    /**
     * Get AggregateRoot.
     *
     * @return \GtnPersistBase\Model\AggregateRootInterface
     */
    public function getAggregateRoot()
    {
        return $this->aggregateRoot;
    }

    /**
     * @param int $id
     * @return ParameterProxy
     */
    public function setId($id)
    {
        $this->aggregateRoot->setId($id);
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->aggregateRoot->getId();
    }

    /**
     * Set Name.
     *
     * @param string $name
     * @return ParameterProxy
     */
    public function setName($name)
    {
        $this->aggregateRoot->setName($name);
        return $this;
    }

    /**
     * Get Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->aggregateRoot->getName();
    }

    /**
     * Set Class.
     *
     * @param \KmbDomain\Model\PuppetClassInterface $class
     * @return ParameterProxy
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Get Class.
     *
     * @return \KmbDomain\Model\PuppetClassInterface
     */
    public function getClass()
    {
        if ($this->class === null) {
            $this->setClass($this->classRepository->getByParameter($this));
        }
        return $this->class;
    }

    /**
     * Set Values.
     *
     * @param \KmbDomain\Model\ValueInterface[] $values
     * @return ParameterProxy
     */
    public function setValues($values)
    {
        $this->aggregateRoot->setValues($values);
        return $this;
    }

    /**
     * Get Values.
     *
     * @return \KmbDomain\Model\ValueInterface[]
     */
    public function getValues()
    {
        return $this->aggregateRoot->getValues();
    }

    /**
     * Set Parent.
     *
     * @param \KmbDomain\Model\ParameterInterface $parent
     * @return ParameterProxy
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get Parent.
     *
     * @return \KmbDomain\Model\ParameterInterface
     */
    public function getParent()
    {
        if ($this->parent === null) {
            $this->setParent($this->parameterRepository->getByChild($this));
        }
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function hasParent()
    {
        return $this->getParent() !== null;
    }

    /**
     * Set Children.
     *
     * @param \KmbDomain\Model\ParameterInterface[] $children
     * @return ParameterProxy
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Get Children.
     *
     * @return \KmbDomain\Model\ParameterInterface[]
     */
    public function getChildren()
    {
        if ($this->children === null) {
            return $this->parameterRepository->getAllByParent($this);
        }
        return $this->children;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        $children = $this->getChildren();
        return !empty($children);
    }

    /**
     * Set ClassRepository.
     *
     * @param \KmbDomain\Model\PuppetClassRepositoryInterface $classRepository
     * @return ParameterProxy
     */
    public function setClassRepository($classRepository)
    {
        $this->classRepository = $classRepository;
        return $this;
    }

    /**
     * Get ClassRepository.
     *
     * @return \KmbDomain\Model\PuppetClassRepositoryInterface
     */
    public function getClassRepository()
    {
        return $this->classRepository;
    }

    /**
     * Set ParameterRepository.
     *
     * @param \KmbDomain\Model\ParameterRepositoryInterface $parameterRepository
     * @return ParameterProxy
     */
    public function setParameterRepository($parameterRepository)
    {
        $this->parameterRepository = $parameterRepository;
        return $this;
    }

    /**
     * Get ParameterRepository.
     *
     * @return \KmbDomain\Model\ParameterRepositoryInterface
     */
    public function getParameterRepository()
    {
        return $this->parameterRepository;
    }
}
