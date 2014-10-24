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
use GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface;
use KmbDomain\Model\GroupRepositoryInterface;
use Zend\ServiceManager\ServiceManager;

class PuppetClassProxyFactory implements AggregateRootProxyFactoryInterface
{
    /** @var array */
    protected $config;

    /** @var ServiceManager */
    protected $serviceManager;

    /**
     * @param AggregateRootInterface $aggregateRoot
     * @return AggregateRootProxyInterface
     */
    public function createProxy(AggregateRootInterface $aggregateRoot)
    {
        $proxy = new PuppetClassProxy();
        $proxy->setAggregateRoot($aggregateRoot);

        /** @var GroupRepositoryInterface $groupRepository */
        $groupRepository = $this->serviceManager->get('GroupRepository');
        $proxy->setGroupRepository($groupRepository);

        return $proxy;
    }

    /**
     * @param $config
     * @return AggregateRootProxyFactoryInterface
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
}
