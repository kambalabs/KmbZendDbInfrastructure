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
     * @param array $values
     * @return ParameterProxy
     */
    public function setValues($values)
    {
        $this->aggregateRoot->setValues($values);
        return $this;
    }

    /**
     * Add specified value.
     *
     * @param array $value
     * @return ParameterProxy
     */
    public function addValue($value)
    {
        return $this->aggregateRoot->addValue($value);
    }

    /**
     * Get Values.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->aggregateRoot->getValues();
    }

    /**
     * @return bool
     */
    public function hasValues()
    {
        return $this->aggregateRoot->hasValues();
    }

    /**
     * @param string $value
     * @return bool
     */
    public function hasValue($value)
    {
        return $this->aggregateRoot->hasValue($value);
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
     * Add specified child.
     *
     * @param \KmbDomain\Model\ParameterInterface $child
     * @return ParameterProxy
     */
    public function addChild($child)
    {
        $this->children[] = $child;
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
            $this->setChildren($this->parameterRepository->getAllByParent($this));
        }
        return $this->children;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * @param string $name
     * @return \KmbDomain\Model\ParameterInterface
     */
    public function getChildByName($name)
    {
        if ($this->hasChildren()) {
            foreach ($this->children as $child) {
                if ($child->getName() === $name) {
                    return $child;
                }
            }
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasChildWithName($name)
    {
        return $this->getChildByName($name) !== null;
    }

    /**
     * Set Template.
     *
     * @param \stdClass $template
     * @return ParameterProxy
     */
    public function setTemplate($template)
    {
        $this->aggregateRoot->setTemplate($template);
        return $this;
    }

    /**
     * Get Template.
     *
     * @return \stdClass
     */
    public function getTemplate()
    {
        return $this->aggregateRoot->getTemplate();
    }

    /**
     * Check if template is set.
     *
     * @return bool
     */
    public function hasTemplate()
    {
        return $this->aggregateRoot->hasTemplate();
    }

    /**
     * Set available children.
     *
     * @param \stdClass[] $availableChildren
     * @return ParameterProxy
     */
    public function setAvailableChildren($availableChildren)
    {
        $this->aggregateRoot->setAvailableChildren($availableChildren);
        return $this;
    }

    /**
     * Get available children.
     *
     * @return \stdClass[]
     */
    public function getAvailableChildren()
    {
        return $this->aggregateRoot->getAvailableChildren();
    }

    /**
     * @return bool
     */
    public function hasAvailableChildren()
    {
        return $this->aggregateRoot->hasAvailableChildren();
    }

    /**
     * Set AvailableValues.
     *
     * @param array $availableValues
     * @return ParameterProxy
     */
    public function setAvailableValues($availableValues)
    {
        $this->aggregateRoot->setAvailableValues($availableValues);
        return $this;
    }

    /**
     * Get AvailableValues.
     *
     * @return array
     */
    public function getAvailableValues()
    {
        return $this->aggregateRoot->getAvailableValues();
    }

    /**
     * @return bool
     */
    public function hasAvailableValues()
    {
        return $this->aggregateRoot->hasAvailableValues();
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
