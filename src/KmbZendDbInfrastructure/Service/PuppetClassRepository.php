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
use KmbDomain\Model\ParameterInterface;
use KmbDomain\Model\PuppetClassInterface;
use KmbDomain\Model\PuppetClassRepositoryInterface;
use Zend\Db\Sql\Where;

class PuppetClassRepository extends Repository implements PuppetClassRepositoryInterface
{
    /** @var  ParameterRepository */
    protected $parameterRepository;

    public function add(AggregateRootInterface $aggregateRoot)
    {
        parent::add($aggregateRoot);

        /** @var PuppetClassInterface $aggregateRoot */
        if ($aggregateRoot->hasParameters()) {
            foreach ($aggregateRoot->getParameters() as $parameter) {
                $parameter->setClass($aggregateRoot);
                $this->parameterRepository->add($parameter);
            }
        }
        return $this;
    }

    /**
     * @param ParameterInterface $parameter
     * @return PuppetClassInterface
     */
    public function getByParameter($parameter)
    {
        $criteria = new Where();
        $criteria->equalTo('p.id', $parameter->getId());

        $select = $this->getSelect()
            ->join(
                ['p' => $this->parameterRepository->getTableName()],
                $this->getTableName() . '.id = p.puppet_class_id',
                [
                    'p.id' => 'id',
                    'p.name' => 'name',
                    'p.parent_id' => 'parent_id',
                    'p.puppet_class_id' => 'puppet_class_id',
                ]
            )
            ->where($criteria);

        $aggregateRoots = $this->hydrateAggregateRootsFromResult($this->performRead($select));
        return empty($aggregateRoots) ? null : $aggregateRoots[0];
    }

    /**
     * Set Parameter Repository.
     *
     * @param string $parameterRepository
     * @return PuppetClassRepository
     */
    public function setParameterRepository($parameterRepository)
    {
        $this->parameterRepository = $parameterRepository;
        return $this;
    }

    /**
     * Get Parameter Repository.
     *
     * @return ParameterRepository
     */
    public function getParameterRepository()
    {
        return $this->parameterRepository;
    }
}
