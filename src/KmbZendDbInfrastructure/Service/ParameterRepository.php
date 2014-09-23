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
use KmbDomain\Model\ParameterInterface;
use KmbDomain\Model\ParameterRepositoryInterface;
use KmbDomain\Model\PuppetClassInterface;
use Zend\Db\Sql\Where;

class ParameterRepository extends Repository implements ParameterRepositoryInterface
{
    /**
     * @param PuppetClassInterface $class
     * @return ParameterInterface[]
     */
    public function getAllByClass($class)
    {
        $criteria = new Where();
        $criteria
            ->equalTo('puppet_class_id', $class->getId())
            ->and
            ->isNull('parent_id');
        return $this->getAllBy($criteria);
    }

    /**
     * @param ParameterInterface $parent
     * @return ParameterInterface[]
     */
    public function getAllByParent($parent)
    {
        $criteria = new Where();
        $criteria->equalTo('parent_id', $parent->getId());
        return $this->getAllBy($criteria);
    }

    /**
     * @param ParameterInterface $child
     * @return ParameterInterface
     */
    public function getByChild($child)
    {
        $criteria = new Where();
        $criteria->equalTo('p.id', $child->getId());

        $select = $this->getSelect()
            ->join(
                ['p' => $this->getTableName()],
                $this->getTableName() . '.id = p.parent_id',
                []
            )
            ->where($criteria);

        $aggregateRoots = $this->hydrateAggregateRootsFromResult($this->performRead($select));
        return empty($aggregateRoots) ? null : $aggregateRoots[0];
    }
}
