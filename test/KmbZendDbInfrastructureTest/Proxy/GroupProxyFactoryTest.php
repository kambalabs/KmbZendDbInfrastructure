<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\Group;
use KmbZendDbInfrastructure\Proxy\GroupProxyFactory;
use KmbZendDbInfrastructureTest\Bootstrap;

class GroupProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateProxy()
    {
        $factory = new GroupProxyFactory();
        $factory->setServiceManager(Bootstrap::getServiceManager());
        $factory->setConfig([]);
        $group = new Group();
        $group->setId(1);

        /** @var \KmbZendDbInfrastructure\Proxy\GroupProxy $proxy */
        $proxy = $factory->createProxy($group);

        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\GroupProxy', $proxy);
        $this->assertEquals($group, $proxy->getAggregateRoot());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\RevisionRepository', $proxy->getRevisionRepository());
    }
}
