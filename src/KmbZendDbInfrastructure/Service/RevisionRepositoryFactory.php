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

class RevisionRepositoryFactory extends ZendDb\RepositoryFactory
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var RevisionRepository $service */
        $service = parent::createService($serviceLocator);

        $service->setRevisionLogClass($this->getStrict('revision_log_class'));
        $service->setRevisionLogTableName($this->getStrict('revision_log_table_name'));
        $service->setRevisionLogTableSequenceName($this->getStrict('revision_log_table_sequence_name'));
        $revisionLogHydratorClass = $this->getStrict('revision_log_hydrator_class');
        $service->setRevisionLogHydrator(new $revisionLogHydratorClass);

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

        /** @var GroupRepository $groupRepository */
        $groupRepository = $serviceLocator->get('GroupRepository');
        $service->setGroupRepository($groupRepository);

        return $service;
    }
}
