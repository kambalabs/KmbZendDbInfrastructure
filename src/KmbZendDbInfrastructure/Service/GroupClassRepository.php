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

use GtnPersistBase\Model\AggregateRootInterface;
use GtnPersistZendDb\Infrastructure\ZendDb\Repository;
use KmbDomain\Model\GroupParameterInterface;
use KmbDomain\Model\GroupClassInterface;
use KmbDomain\Service\GroupClassRepositoryInterface;
use Zend\Db\Sql\Where;

class GroupClassRepository extends Repository implements GroupClassRepositoryInterface
{
    /** @var  GroupParameterRepository */
    protected $groupParameterRepository;

    public function add(AggregateRootInterface $aggregateRoot)
    {
        parent::add($aggregateRoot);

        /** @var GroupClassInterface $aggregateRoot */
        if ($aggregateRoot->hasParameters()) {
            foreach ($aggregateRoot->getParameters() as $groupParameter) {
                $groupParameter->setClass($aggregateRoot);
                $this->groupParameterRepository->add($groupParameter);
            }
        }
        return $this;
    }

    /**
     * @param GroupParameterInterface $groupParameter
     * @return GroupClassInterface
     */
    public function getByParameter($groupParameter)
    {
        $criteria = new Where();
        $criteria->equalTo('p.id', $groupParameter->getId());

        $select = $this->getSelect()
            ->join(
                ['p' => $this->groupParameterRepository->getTableName()],
                $this->getTableName() . '.id = p.group_class_id',
                [
                    'p.id' => 'id',
                    'p.name' => 'name',
                    'p.parent_id' => 'parent_id',
                    'p.group_class_id' => 'group_class_id',
                ]
            )
            ->where($criteria);

        $aggregateRoots = $this->hydrateAggregateRootsFromResult($this->performRead($select));
        return empty($aggregateRoots) ? null : $aggregateRoots[0];
    }

    /**
     * Set GroupParameter Repository.
     *
     * @param string $groupParameterRepository
     * @return GroupClassRepository
     */
    public function setGroupParameterRepository($groupParameterRepository)
    {
        $this->groupParameterRepository = $groupParameterRepository;
        return $this;
    }

    /**
     * Get GroupParameter Repository.
     *
     * @return GroupParameterRepository
     */
    public function getGroupParameterRepository()
    {
        return $this->groupParameterRepository;
    }
}
