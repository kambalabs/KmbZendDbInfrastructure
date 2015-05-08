<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\GroupParameter;
use KmbZendDbInfrastructure\Proxy\GroupParameterProxyFactory;
use KmbZendDbInfrastructureTest\Bootstrap;

class GroupParameterProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateProxy()
    {
        $factory = new GroupParameterProxyFactory();
        $factory->setServiceManager(Bootstrap::getServiceManager());
        $factory->setConfig([]);
        $aggregateRoot = new GroupParameter();
        $aggregateRoot->setId(1);

        /** @var \KmbZendDbInfrastructure\Proxy\GroupParameterProxy $proxy */
        $proxy = $factory->createProxy($aggregateRoot);

        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\GroupParameterProxy', $proxy);
        $this->assertEquals($aggregateRoot, $proxy->getAggregateRoot());
        $this->assertInstanceOf('KmbDomain\Service\GroupClassRepositoryInterface', $proxy->getGroupClassRepository());
        $this->assertInstanceOf('KmbDomain\Service\GroupParameterRepositoryInterface', $proxy->getGroupParameterRepository());
    }
}
