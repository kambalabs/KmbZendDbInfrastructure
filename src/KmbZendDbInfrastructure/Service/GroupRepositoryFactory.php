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

use GtnPersistZendDb\Infrastructure\ZendDb;
use GtnPersistZendDb\Service\AggregateRootProxyFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GroupRepositoryFactory extends ZendDb\RepositoryFactory
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var GroupRepository $service */
        $service = parent::createService($serviceLocator);

        $service->setRevisionClass($this->getStrict('revision_class'));
        $service->setRevisionTableName($this->getStrict('revision_table_name'));
        $revisionHydratorClass = $this->getStrict('revision_hydrator_class');
        $service->setRevisionHydrator(new $revisionHydratorClass);
        $revisionProxyFactoryClass = $this->getStrict('revision_proxy_factory');
        /** @var AggregateRootProxyFactoryInterface $revisionProxyFactory */
        $revisionProxyFactory = new $revisionProxyFactoryClass;
        $revisionProxyFactory->setConfig($this->config);
        $revisionProxyFactory->setServiceManager($serviceLocator);
        $service->setRevisionProxyFactory($revisionProxyFactory);

        $service->setEnvironmentClass($this->getStrict('environment_class'));
        $service->setEnvironmentTableName($this->getStrict('environment_table_name'));
        $environmentHydratorClass = $this->getStrict('environment_hydrator_class');
        $service->setEnvironmentHydrator(new $environmentHydratorClass);
        $environmentProxyFactoryClass = $this->getStrict('environment_proxy_factory');
        /** @var AggregateRootProxyFactoryInterface $environmentProxyFactory */
        $environmentProxyFactory = new $environmentProxyFactoryClass;
        $environmentProxyFactory->setConfig($this->config);
        $environmentProxyFactory->setServiceManager($serviceLocator);
        $service->setEnvironmentProxyFactory($environmentProxyFactory);

        /** @var GroupClassRepository $groupClassRepository */
        $groupClassRepository = $serviceLocator->get('GroupClassRepository');
        $service->setGroupClassRepository($groupClassRepository);

        /** @var GroupParameterRepository $groupParameterRepository */
        $groupParameterRepository = $serviceLocator->get('GroupParameterRepository');
        $service->setGroupParameterRepository($groupParameterRepository);

        return $service;
    }
}
