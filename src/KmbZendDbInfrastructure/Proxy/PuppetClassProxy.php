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

use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\GroupRepositoryInterface;
use KmbDomain\Model\PuppetClass;
use KmbDomain\Model\PuppetClassInterface;

class PuppetClassProxy implements PuppetClassInterface
{
    /** @var  GroupRepositoryInterface */
    protected $groupRepository;

    /** @var PuppetClass */
    protected $aggregateRoot;

    /** @var  GroupInterface */
    protected $group;

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
     * @return PuppetClassProxy
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
     * @return PuppetClassProxy
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
     * Set Group.
     *
     * @param \KmbDomain\Model\GroupInterface $group
     * @return PuppetClassProxy
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Get Group.
     *
     * @return \KmbDomain\Model\GroupInterface
     */
    public function getGroup()
    {
        if ($this->group === null) {
            $this->setGroup($this->groupRepository->getByClass($this));
        }
        return $this->group;
    }

    /**
     * Set Parameters.
     *
     * @param \KmbDomain\Model\ParameterInterface[] $parameters
     * @return PuppetClassProxy
     */
    public function setParameters($parameters)
    {
        $this->aggregateRoot->setParameters($parameters);
        return $this;
    }

    /**
     * Add specified parameter.
     *
     * @param \KmbDomain\Model\ParameterInterface
     * @return PuppetClassProxy
     */
    public function addParameter($parameter)
    {
        $this->aggregateRoot->addParameter($parameter);
        return $this;
    }

    /**
     * Get Parameters.
     *
     * @return \KmbDomain\Model\ParameterInterface[]
     */
    public function getParameters()
    {
        return $this->aggregateRoot->getParameters();
    }

    /**
     * @return bool
     */
    public function hasParameters()
    {
        return $this->aggregateRoot->hasParameters();
    }

    /**
     * @param string $name
     * @return \KmbDomain\Model\ParameterInterface
     */
    public function getParameterByName($name)
    {
        return $this->aggregateRoot->getParameterByName($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasParameterWithName($name)
    {
        return $this->aggregateRoot->hasParameterWithName($name);
    }

    /**
     * Set AvailableParameters.
     *
     * @param \stdClass[] $availableParameters
     * @return PuppetClassProxy
     */
    public function setAvailableParameters($availableParameters)
    {
        $this->aggregateRoot->setAvailableParameters($availableParameters);
        return $this;
    }

    /**
     * Get AvailableParameters.
     *
     * @return \stdClass[]
     */
    public function getAvailableParameters()
    {
        return $this->aggregateRoot->getAvailableParameters();
    }

    /**
     * @return bool
     */
    public function hasAvailableParameters()
    {
        return $this->aggregateRoot->hasAvailableParameters();
    }

    /**
     * Set GroupRepository.
     *
     * @param \KmbDomain\Model\GroupRepositoryInterface $groupRepository
     * @return PuppetClassProxy
     */
    public function setGroupRepository($groupRepository)
    {
        $this->groupRepository = $groupRepository;
        return $this;
    }

    /**
     * Get GroupRepository.
     *
     * @return \KmbDomain\Model\GroupRepositoryInterface
     */
    public function getGroupRepository()
    {
        return $this->groupRepository;
    }
}
