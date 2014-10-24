<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\GroupRepository;
use KmbZendDbInfrastructureTest\Bootstrap;

class GroupRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var GroupRepository $service */
        $service = Bootstrap::getServiceManager()->get('GroupRepository');

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\GroupRepository', $service);
        $this->assertEquals('KmbDomain\Model\Revision', $service->getRevisionClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\RevisionProxyFactory', $service->getRevisionProxyFactory());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\RevisionHydrator', $service->getRevisionHydrator());
        $this->assertEquals('revisions', $service->getRevisionTableName());
        $this->assertEquals('KmbDomain\Model\Environment', $service->getEnvironmentClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\EnvironmentProxyFactory', $service->getEnvironmentProxyFactory());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\EnvironmentHydrator', $service->getEnvironmentHydrator());
        $this->assertEquals('environments', $service->getEnvironmentTableName());
        $this->assertEquals('KmbDomain\Model\GroupClass', $service->getGroupClassClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\GroupClassProxyFactory', $service->getGroupClassProxyFactory());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\GroupClassHydrator', $service->getGroupClassHydrator());
        $this->assertEquals('group_classes', $service->getGroupClassTableName());
        $this->assertEquals('KmbDomain\Model\GroupParameter', $service->getGroupParameterClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\GroupParameterProxyFactory', $service->getGroupParameterProxyFactory());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\GroupParameterHydrator', $service->getGroupParameterHydrator());
        $this->assertEquals('group_parameters', $service->getGroupParameterTableName());
        $this->assertEquals('group_values', $service->getGroupValueTableName());
    }
}
