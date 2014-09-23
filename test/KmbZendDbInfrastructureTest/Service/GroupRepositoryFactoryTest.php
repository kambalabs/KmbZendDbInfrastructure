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
        $this->assertEquals('KmbDomain\Model\PuppetClass', $service->getPuppetClassClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\PuppetClassProxyFactory', $service->getPuppetClassProxyFactory());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\PuppetClassHydrator', $service->getPuppetClassHydrator());
        $this->assertEquals('puppet_classes', $service->getPuppetClassTableName());
        $this->assertEquals('KmbDomain\Model\Parameter', $service->getParameterClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\ParameterProxyFactory', $service->getParameterProxyFactory());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\ParameterHydrator', $service->getParameterHydrator());
        $this->assertEquals('parameters', $service->getParameterTableName());
        $this->assertEquals('KmbDomain\Model\Value', $service->getValueClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\ValueHydrator', $service->getValueHydrator());
        $this->assertEquals('values', $service->getValueTableName());
    }
}
