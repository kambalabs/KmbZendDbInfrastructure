<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\GroupClass;
use KmbZendDbInfrastructure\Proxy\GroupClassProxyFactory;
use KmbZendDbInfrastructureTest\Bootstrap;

class GroupClassProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateProxy()
    {
        $factory = new GroupClassProxyFactory();
        $factory->setServiceManager(Bootstrap::getServiceManager());
        $factory->setConfig([]);
        $aggregateRoot = new GroupClass();
        $aggregateRoot->setId(1);

        /** @var \KmbZendDbInfrastructure\Proxy\GroupClassProxy $proxy */
        $proxy = $factory->createProxy($aggregateRoot);

        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\GroupClassProxy', $proxy);
        $this->assertEquals($aggregateRoot, $proxy->getAggregateRoot());
        $this->assertInstanceOf('KmbDomain\Model\GroupRepositoryInterface', $proxy->getGroupRepository());
    }
}
