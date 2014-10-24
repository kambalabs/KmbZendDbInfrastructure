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

        $service->setGroupClassClass($this->getStrict('group_class_class'));
        $service->setGroupClassTableName($this->getStrict('group_class_table_name'));
        $groupClassHydratorClass = $this->getStrict('group_class_hydrator_class');
        $service->setGroupClassHydrator(new $groupClassHydratorClass);
        $groupClassProxyFactoryClass = $this->getStrict('group_class_proxy_factory');
        /** @var AggregateRootProxyFactoryInterface $groupClassProxyFactory */
        $groupClassProxyFactory = new $groupClassProxyFactoryClass;
        $groupClassProxyFactory->setConfig($this->config);
        $groupClassProxyFactory->setServiceManager($serviceLocator);
        $service->setGroupClassProxyFactory($groupClassProxyFactory);

        $service->setGroupParameterClass($this->getStrict('group_parameter_class'));
        $service->setGroupParameterTableName($this->getStrict('group_parameter_table_name'));
        $groupParameterHydratorClass = $this->getStrict('group_parameter_hydrator_class');
        $service->setGroupParameterHydrator(new $groupParameterHydratorClass);
        $groupParameterProxyFactoryClass = $this->getStrict('group_parameter_proxy_factory');
        /** @var AggregateRootProxyFactoryInterface $groupParameterProxyFactory */
        $groupParameterProxyFactory = new $groupParameterProxyFactoryClass;
        $groupParameterProxyFactory->setConfig($this->config);
        $groupParameterProxyFactory->setServiceManager($serviceLocator);
        $service->setGroupParameterProxyFactory($groupParameterProxyFactory);

        $service->setGroupValueTableName($this->getStrict('group_value_table_name'));

        return $service;
    }
}
