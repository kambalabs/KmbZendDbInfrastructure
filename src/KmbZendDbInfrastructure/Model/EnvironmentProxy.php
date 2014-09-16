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
namespace KmbZendDbInfrastructure\Model;

use GtnPersistBase\Model\AggregateRootInterface;
use GtnPersistZendDb\Model\AggregateRootProxyInterface;
use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\EnvironmentRepositoryInterface;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\RevisionRepositoryInterface;
use KmbDomain\Model\UserInterface;
use KmbDomain\Model\UserRepositoryInterface;
use Zend\Stdlib\ArrayUtils;

class EnvironmentProxy implements EnvironmentInterface, AggregateRootProxyInterface
{
    /** @var Environment */
    protected $aggregateRoot;

    /** @var EnvironmentRepositoryInterface */
    protected $environmentRepository;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var RevisionRepositoryInterface */
    protected $revisionRepository;

    /** @var EnvironmentProxy */
    protected $parent;

    /** @var EnvironmentInterface[] */
    protected $children;

    /** @var UserInterface[] */
    protected $users;

    /** @var RevisionInterface */
    protected $currentRevision;

    /** @var RevisionInterface */
    protected $lastReleasedRevision;

    /** @var RevisionInterface[] */
    protected $releasedRevisions;

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
     * Set EnvironmentRepository.
     *
     * @param EnvironmentRepositoryInterface $environmentRepository
     * @return EnvironmentProxy
     */
    public function setEnvironmentRepository($environmentRepository)
    {
        $this->environmentRepository = $environmentRepository;
        return $this;
    }

    /**
     * Get EnvironmentRepository.
     *
     * @return EnvironmentRepositoryInterface
     */
    public function getEnvironmentRepository()
    {
        return $this->environmentRepository;
    }

    /**
     * Set UserRepository.
     *
     * @param UserRepositoryInterface $userRepository
     * @return EnvironmentProxy
     */
    public function setUserRepository($userRepository)
    {
        $this->userRepository = $userRepository;
        return $this;
    }

    /**
     * Get UserRepository.
     *
     * @return UserRepositoryInterface
     */
    public function getUserRepository()
    {
        return $this->userRepository;
    }

    /**
     * Set RevisionRepository.
     *
     * @param \KmbDomain\Model\RevisionRepositoryInterface $revisionRepository
     * @return EnvironmentProxy
     */
    public function setRevisionRepository($revisionRepository)
    {
        $this->revisionRepository = $revisionRepository;
        return $this;
    }

    /**
     * Get RevisionRepository.
     *
     * @return \KmbDomain\Model\RevisionRepositoryInterface
     */
    public function getRevisionRepository()
    {
        return $this->revisionRepository;
    }

    /**
     * @param int $id
     * @return EnvironmentProxy
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
     * @return EnvironmentProxy
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
     * Get NormalizedName.
     *
     * @return string
     */
    public function getNormalizedName()
    {
        return implode('_', $this->getAncestorsNames());
    }

    /**
     * Set Parent.
     *
     * @param EnvironmentInterface $parent
     * @return EnvironmentProxy
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get Parent.
     *
     * @return EnvironmentInterface
     */
    public function getParent()
    {
        if ($this->parent === null) {
            $this->setParent($this->environmentRepository->getParent($this));
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
     * @param EnvironmentInterface $environment
     * @return bool
     */
    public function isAncestorOf($environment)
    {
        return $this->aggregateRoot->isAncestorOf($environment);
    }

    /**
     * Get all descendants.
     *
     * @return EnvironmentInterface[]
     */
    public function getDescendants()
    {
        $descendants = [];
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $child) {
                $childDescendants = $child->hasChildren() ? $child->getDescendants() : [];
                $descendants = ArrayUtils::merge($descendants, ArrayUtils::merge([$child], $childDescendants));
            }
        }
        return $descendants;
    }

    /**
     * Set Children.
     *
     * @param EnvironmentInterface[] $children
     * @return EnvironmentProxy
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @param EnvironmentInterface $child
     * @return EnvironmentProxy
     */
    public function addChild($child)
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * Get Children.
     *
     * @return EnvironmentInterface[]
     */
    public function getChildren()
    {
        if ($this->children === null) {
            $this->setChildren($this->environmentRepository->getAllChildren($this));
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
     * @param $name
     * @return bool
     */
    public function hasChildWithName($name)
    {
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $child) {
                /** @var EnvironmentInterface $child */
                if ($child->getName() === $name) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set Users.
     *
     * @param UserInterface[] $users
     * @return EnvironmentProxy
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @param UserInterface[] $users
     * @return EnvironmentProxy
     */
    public function addUsers($users)
    {
        $this->users = ArrayUtils::merge($this->getUsers(), $users);
        $this->users = array_values(array_unique($this->users));
        return $this;
    }

    /**
     * @param int $userId
     * @return EnvironmentInterface
     */
    public function removeUserById($userId)
    {
        if ($this->hasUsers()) {
            foreach ($this->users as $index => $currentUser) {
                /** @var UserInterface $currentUser */
                if ($currentUser->getId() === $userId) {
                    unset($this->users[$index]);
                    $this->users = array_values($this->users);
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Get Users.
     *
     * @return UserInterface[]
     */
    public function getUsers()
    {
        if ($this->users === null) {
            $this->setUsers($this->userRepository->getAllByEnvironment($this));
        }
        return $this->users;
    }

    /**
     * @return bool
     */
    public function hasUsers()
    {
        return count($this->getUsers()) > 0;
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function hasUser($user)
    {
        if ($this->hasUsers()) {
            foreach ($this->users as $currentUser) {
                /** @var UserInterface $currentUser */
                if ($currentUser->getId() === $user->getId()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param bool $default
     * @return EnvironmentInterface
     */
    public function setDefault($default)
    {
        $this->aggregateRoot->setDefault($default);
        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->aggregateRoot->isDefault();
    }

    /**
     * Set CurrentRevision.
     *
     * @param \KmbDomain\Model\RevisionInterface $currentRevision
     * @return EnvironmentProxy
     */
    public function setCurrentRevision($currentRevision)
    {
        $this->currentRevision = $currentRevision;
        return $this;
    }

    /**
     * Get CurrentRevision.
     *
     * @return \KmbDomain\Model\RevisionInterface
     */
    public function getCurrentRevision()
    {
        if ($this->currentRevision === null) {
            $this->setCurrentRevision($this->revisionRepository->getCurrentByEnvironment($this));
        }
        return $this->currentRevision;
    }

    /**
     * Set LastReleasedRevision.
     *
     * @param \KmbDomain\Model\RevisionInterface $lastReleasedRevision
     * @return EnvironmentProxy
     */
    public function setLastReleasedRevision($lastReleasedRevision)
    {
        $this->lastReleasedRevision = $lastReleasedRevision;
        return $this;
    }

    /**
     * Get LastReleasedRevision.
     *
     * @return \KmbDomain\Model\RevisionInterface
     */
    public function getLastReleasedRevision()
    {
        if ($this->lastReleasedRevision === null) {
            $this->setLastReleasedRevision($this->revisionRepository->getLastReleasedByEnvironment($this));
        }
        return $this->lastReleasedRevision;
    }

    /**
     * Set ReleasedRevisions.
     *
     * @param \KmbDomain\Model\RevisionInterface[] $releasedRevisions
     * @return EnvironmentProxy
     */
    public function setReleasedRevisions($releasedRevisions)
    {
        $this->releasedRevisions = $releasedRevisions;
        return $this;
    }

    /**
     * Get ReleasedRevisions.
     *
     * @return \KmbDomain\Model\RevisionInterface[]
     */
    public function getReleasedRevisions()
    {
        if ($this->releasedRevisions === null) {
            $this->setReleasedRevisions($this->revisionRepository->getAllReleasedByEnvironment($this));
        }
        return $this->releasedRevisions;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getNormalizedName();
    }
}
