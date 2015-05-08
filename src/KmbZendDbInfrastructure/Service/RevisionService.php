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
namespace KmbZendDbInfrastructure\Service;

use KmbBase\DateTimeFactoryInterface;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Service\RevisionServiceInterface;
use KmbDomain\Model\UserInterface;
use Zend\Db\Exception\ExceptionInterface;

class RevisionService implements RevisionServiceInterface
{
    /** @var  RevisionRepository */
    protected $revisionRepository;

    /** @var  DateTimeFactoryInterface */
    protected $dateTimeFactory;

    /**
     * Release specified revision and create a current revision from it.
     * If the revision had already been released, the current revision should be removed first.
     *
     * @param RevisionInterface $revision
     * @param UserInterface     $user
     * @param string            $comment
     * @throws \Zend\Db\Exception\ExceptionInterface
     * @return RevisionService
     */
    public function release(RevisionInterface $revision, UserInterface $user, $comment)
    {
        $connection = $this->revisionRepository->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        try {
            if ($revision->getReleasedAt() != null) {
                $this->revisionRepository->remove($revision->getEnvironment()->getCurrentRevision());
                $revision = clone $revision;
                $revision->setReleasedAt($this->dateTimeFactory->now());
                $revision->setReleasedBy($user->getName());
                $revision->setComment($comment);
                $this->revisionRepository->add($revision);
            } else {
                $revision->setReleasedAt($this->dateTimeFactory->now());
                $revision->setReleasedBy($user->getName());
                $revision->setComment($comment);
                $this->revisionRepository->update($revision);
            }
            $newCurrentRevision = clone $revision;
            $this->revisionRepository->add($newCurrentRevision);
        } catch (ExceptionInterface $e) {
            $connection->rollback();
            throw $e;
        }
        $connection->commit();
        return $this;
    }

    /**
     * Remove specified revision.
     * If it's the current revision, another current revision should be recreate from last released revision.
     *
     * @param RevisionInterface $revision
     * @throws \Zend\Db\Exception\ExceptionInterface
     * @return RevisionService
     */
    public function remove(RevisionInterface $revision)
    {
        $connection = $this->revisionRepository->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        try {
            $this->revisionRepository->remove($revision);
            if ($revision->getReleasedAt() == null) {
                $lastReleasedRevision = $revision->getEnvironment()->getLastReleasedRevision();
                $this->revisionRepository->add(clone $lastReleasedRevision);
            }
        } catch (ExceptionInterface $e) {
            $connection->rollback();
            throw $e;
        }
        $connection->commit();
        return $this;
    }

    /**
     * Set RevisionRepository.
     *
     * @param \KmbDomain\Service\RevisionRepositoryInterface $revisionRepository
     * @return RevisionService
     */
    public function setRevisionRepository($revisionRepository)
    {
        $this->revisionRepository = $revisionRepository;
        return $this;
    }

    /**
     * Get RevisionRepository.
     *
     * @return \KmbDomain\Service\RevisionRepositoryInterface
     */
    public function getRevisionRepository()
    {
        return $this->revisionRepository;
    }

    /**
     * Set DateTimeFactory.
     *
     * @param \KmbBase\DateTimeFactoryInterface $dateTimeFactory
     * @return RevisionService
     */
    public function setDateTimeFactory($dateTimeFactory)
    {
        $this->dateTimeFactory = $dateTimeFactory;
        return $this;
    }

    /**
     * Get DateTimeFactory.
     *
     * @return \KmbBase\DateTimeFactoryInterface
     */
    public function getDateTimeFactory()
    {
        return $this->dateTimeFactory;
    }
}
