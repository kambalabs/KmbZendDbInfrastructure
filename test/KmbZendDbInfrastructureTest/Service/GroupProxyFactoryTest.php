<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\Group;
use KmbZendDbInfrastructure\Service\GroupProxyFactory;
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

        /** @var \KmbZendDbInfrastructure\Model\GroupProxy $proxy */
        $proxy = $factory->createProxy($group);

        $this->assertInstanceOf('KmbZendDbInfrastructure\Model\GroupProxy', $proxy);
        $this->assertEquals($group, $proxy->getAggregateRoot());
    }
}
