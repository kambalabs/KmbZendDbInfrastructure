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

use GtnPersistBase\Model\AggregateRootInterface;
use GtnPersistZendDb\Model\AggregateRootProxyInterface;
use KmbDomain\Model\GroupInterface;
use KmbDomain\Model\GroupRepositoryInterface;
use KmbDomain\Model\Revision;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\RevisionLogInterface;

class RevisionProxy implements RevisionInterface, AggregateRootProxyInterface
{
    /** @var Revision */
    protected $aggregateRoot;

    /** @var GroupInterface[] */
    protected $groups;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return AggregateRootProxyInterface
     */
    public function setAggregateRoot(AggregateRootInterface $aggregateRoot)
    {
        $this->aggregateRoot = $aggregateRoot;
        return $this;
    }

    /**
     * return AggregateRootInterface
     */
    public function getAggregateRoot()
    {
        return $this->aggregateRoot;
    }

    /**
     * @param int $id
     * @return RevisionProxy
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
     * Set Environment.
     *
     * @param \KmbDomain\Model\EnvironmentInterface $environment
     * @return RevisionProxy
     */
    public function setEnvironment($environment)
    {
        $this->aggregateRoot->setEnvironment($environment);
        return $this;
    }

    /**
     * Get Environment.
     *
     * @return \KmbDomain\Model\EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->aggregateRoot->getEnvironment();
    }

    /**
     * Set ReleasedAt.
     *
     * @param \DateTime $releasedAt
     * @return RevisionProxy
     */
    public function setReleasedAt($releasedAt)
    {
        $this->aggregateRoot->setReleasedAt($releasedAt);
        return $this;
    }

    /**
     * Get ReleasedAt.
     *
     * @return \DateTime
     */
    public function getReleasedAt()
    {
        return $this->aggregateRoot->getReleasedAt();
    }

    /**
     * @return bool
     */
    public function isReleased()
    {
        return $this->aggregateRoot->isReleased();
    }

    /**
     * Set ReleasedBy.
     *
     * @param string $releasedBy
     * @return RevisionProxy
     */
    public function setReleasedBy($releasedBy)
    {
        $this->aggregateRoot->setReleasedBy($releasedBy);
        return $this;
    }

    /**
     * Get ReleasedBy.
     *
     * @return string
     */
    public function getReleasedBy()
    {
        return $this->aggregateRoot->getReleasedBy();
    }

    /**
     * Set Comment.
     *
     * @param string $comment
     * @return RevisionProxy
     */
    public function setComment($comment)
    {
        $this->aggregateRoot->setComment($comment);
        return $this;
    }

    /**
     * Get Comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->aggregateRoot->getComment();
    }

    /**
     * Set Logs.
     *
     * @param \KmbDomain\Model\RevisionLogInterface[] $logs
     * @return RevisionProxy
     */
    public function setLogs($logs)
    {
        $this->aggregateRoot->setLogs($logs);
        return $this;
    }

    /**
     * Add specified log.
     *
     * @param \KmbDomain\Model\RevisionLogInterface $log
     * @return Revision
     */
    public function addLog($log)
    {
        $this->aggregateRoot->addLog($log);
        return $this;
    }

    /**
     * Get Logs.
     *
     * @return \KmbDomain\Model\RevisionLogInterface[]
     */
    public function getLogs()
    {
        return $this->aggregateRoot->getLogs();
    }

    /**
     * Get most recent log.
     *
     * @return RevisionLogInterface
     */
    public function getLastLog()
    {
        return $this->aggregateRoot->getLastLog();
    }

    /**
     * @return bool
     */
    public function hasLogs()
    {
        return $this->aggregateRoot->hasLogs();
    }

    /**
     * Set Groups.
     *
     * @param \KmbDomain\Model\GroupInterface[] $groups
     * @return RevisionProxy
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * Get Groups.
     *
     * @return \KmbDomain\Model\GroupInterface[]
     */
    public function getGroups()
    {
        if ($this->groups === null) {
            $this->setGroups($this->groupRepository->getAllByRevision($this));
        }
        return $this->groups;
    }

    /**
     * @param string $name
     * @return GroupInterface
     */
    public function getGroupByName($name)
    {
        if ($this->hasGroups()) {
            foreach ($this->groups as $group) {
                if ($group->getName() === $name) {
                    return $group;
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function hasGroups()
    {
        return count($this->getGroups()) > 0;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasGroupWithName($name)
    {
        return $this->getGroupByName($name) !== null;
    }

    /**
     * @param string $hostname
     * @return GroupInterface[]
     */
    public function getGroupsMatchingHostname($hostname)
    {
        $groups = [];
        if ($this->hasGroups()) {
            foreach ($this->getGroups() as $group) {
                if ($group->matchesForHostname($hostname)) {
                    $groups[] = $group;
                }
            }
        }
        return $groups;
    }

    public function __clone()
    {
        if ($this->hasGroups()) {
            $this->setGroups(array_map(function ($group) {
                return clone $group;
            }, $this->getGroups()));
        }
        $this->aggregateRoot = clone $this->aggregateRoot;
    }

    /**
     * Set GroupRepository.
     *
     * @param \KmbDomain\Model\GroupRepositoryInterface $groupRepository
     * @return RevisionProxy
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
