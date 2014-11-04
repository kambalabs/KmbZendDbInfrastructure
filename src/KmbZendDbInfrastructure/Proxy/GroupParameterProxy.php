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

use KmbDomain\Model\GroupParameter;
use KmbDomain\Model\GroupParameterInterface;
use KmbDomain\Model\GroupParameterRepositoryInterface;
use KmbDomain\Model\GroupClassInterface;
use KmbDomain\Model\GroupClassRepositoryInterface;

class GroupParameterProxy implements GroupParameterInterface
{
    /** @var  GroupClassRepositoryInterface */
    protected $groupClassRepository;

    /** @var  GroupParameterRepositoryInterface */
    protected $groupParameterRepository;

    /** @var  GroupParameter */
    protected $aggregateRoot;

    /** @var  GroupClassInterface */
    protected $class;

    /** @var  GroupParameterInterface */
    protected $parent;

    /** @var  GroupParameterInterface[] */
    protected $children;

    /**
     * Set AggregateRoot.
     *
     * @param \GtnPersistBase\Model\AggregateRootInterface $aggregateRoot
     * @return GroupClassProxy
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
     * @return GroupParameterProxy
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
     * @return GroupParameterProxy
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
     * @param GroupClassInterface $class
     * @return GroupParameterProxy
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Get Class.
     *
     * @return GroupClassInterface
     */
    public function getClass()
    {
        if ($this->class === null) {
            $this->setClass($this->groupClassRepository->getByParameter($this));
        }
        return $this->class;
    }

    /**
     * Set Values.
     *
     * @param array $values
     * @return GroupParameterProxy
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
     * @return GroupParameterProxy
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
     * @param GroupParameterInterface $parent
     * @return GroupParameterProxy
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get Parent.
     *
     * @return GroupParameterInterface
     */
    public function getParent()
    {
        if ($this->parent === null) {
            $this->setParent($this->groupParameterRepository->getByChild($this));
        }
        return $this->parent;
    }

    /**
     * Get all ancestors names.
     * It includes the name of the object itself.
     *
     * @return array
     */
    public function getAncestorsNames()
    {
        $names = [];
        if ($this->hasParent()) {
            $names = $this->getParent()->getAncestorsNames();
        }
        $names[] = $this->getName();
        return $names;
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
     * @param GroupParameterInterface[] $children
     * @return GroupParameterProxy
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Add specified child.
     *
     * @param GroupParameterInterface $child
     * @return GroupParameterProxy
     */
    public function addChild($child)
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * Get Children.
     *
     * @return GroupParameterInterface[]
     */
    public function getChildren()
    {
        if ($this->children === null) {
            $this->setChildren($this->groupParameterRepository->getAllByParent($this));
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
     * @return GroupParameterInterface
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
     * @return GroupParameterProxy
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
     * @return GroupParameterProxy
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
     * @return GroupParameterProxy
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

    public function __clone()
    {
        if ($this->hasChildren()) {
            $this->setChildren(array_map(function ($child) {
                return clone $child;
            }, $this->getChildren()));
        }
        $this->class = null;
        $this->parent = null;
        $this->aggregateRoot = clone $this->aggregateRoot;
    }

    /**
     * Set GroupClassRepository.
     *
     * @param GroupClassRepositoryInterface $groupClassRepository
     * @return GroupParameterProxy
     */
    public function setGroupClassRepository($groupClassRepository)
    {
        $this->groupClassRepository = $groupClassRepository;
        return $this;
    }

    /**
     * Get ClassRepository.
     *
     * @return GroupClassRepositoryInterface
     */
    public function getGroupClassRepository()
    {
        return $this->groupClassRepository;
    }

    /**
     * Set GroupParameterRepository.
     *
     * @param GroupParameterRepositoryInterface $groupParameterRepository
     * @return GroupParameterProxy
     */
    public function setGroupParameterRepository($groupParameterRepository)
    {
        $this->groupParameterRepository = $groupParameterRepository;
        return $this;
    }

    /**
     * Get GroupParameterRepository.
     *
     * @return GroupParameterRepositoryInterface
     */
    public function getGroupParameterRepository()
    {
        return $this->groupParameterRepository;
    }
}
