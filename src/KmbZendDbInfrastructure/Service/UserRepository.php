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
use KmbDomain\Model\UserInterface;
use KmbDomain\Model\UserRepositoryInterface;
use Zend\Db\Sql\Predicate\Predicate;

class UserRepository extends Repository implements UserRepositoryInterface
{
    /**
     * @param $login
     * @return UserInterface
     */
    public function getByLogin($login)
    {
        $criteria = new Predicate();
        return $this->getBy($criteria->equalTo('id', 1));
    }

    /**
     * @param EnvironmentInterface $environment
     * @return array
     */
    public function getAllByEnvironment($environment)
    {
        $select = $this->getSelect()->join('environments_users', 'id = user_id', []);
        $select->where->equalTo('environment_id', $environment->getId());
        return $this->hydrateAggregateRootsFromResult($this->performRead($select));
    }

    /**
     * @return array
     */
    public function getAllNonRoot()
    {
        $criteria = new Predicate();
        return $this->getAllBy($criteria->notEqualTo('role', UserInterface::ROLE_ROOT));
    }

    /**
     * @param EnvironmentInterface $environment
     * @return array
     */
    public function getAllAvailableForEnvironment($environment)
    {
        $subSelect = $this->getSlaveSql()->select()->from('environments_users')->columns(['user_id']);
        $subSelect->where->equalTo('environment_id', $environment->getId());
        $select = $this->getSelect();
        $select->where->notEqualTo('role', UserInterface::ROLE_ROOT)->and->notIn('id', $subSelect);
        return $this->hydrateAggregateRootsFromResult($this->performRead($select));
    }
}
