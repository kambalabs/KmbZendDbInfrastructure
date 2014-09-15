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

use GtnPersistZendDb\Infrastructure\ZendDb\Repository;
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\RevisionRepositoryInterface;
use Zend\Db\Sql\Where;

class RevisionRepository extends Repository implements RevisionRepositoryInterface
{
    /**
     * @param EnvironmentInterface $environment
     * @return RevisionInterface[]
     */
    public function getAllReleasedByEnvironment(EnvironmentInterface $environment)
    {
        $where = new Where();
        $where
            ->equalTo('environment_id', $environment->getId())
            ->and
            ->isNotNull('released_at');
        $select = $this->getSelect()->where($where)->order('released_at DESC');
        return $this->hydrateAggregateRootsFromResult($this->performRead($select));
    }

    /**
     * @param EnvironmentInterface $environment
     * @return RevisionInterface
     */
    public function getCurrentByEnvironment(EnvironmentInterface $environment)
    {
        $where = new Where();
        $where
            ->equalTo('environment_id', $environment->getId())
            ->and
            ->isNull('released_at');
        return $this->getBy($where);
    }

    /**
     * @param EnvironmentInterface $environment
     * @return RevisionInterface
     */
    public function getLastReleasedByEnvironment(EnvironmentInterface $environment)
    {
        $revisions = $this->getAllReleasedByEnvironment($environment);
        return empty($revisions) ? null : $revisions[0];
    }
}
