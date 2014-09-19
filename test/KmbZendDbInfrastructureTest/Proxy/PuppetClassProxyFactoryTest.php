<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\PuppetClass;
use KmbZendDbInfrastructure\Proxy\PuppetClassProxyFactory;
use KmbZendDbInfrastructureTest\Bootstrap;

class PuppetClassProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateProxy()
    {
        $factory = new PuppetClassProxyFactory();
        $factory->setServiceManager(Bootstrap::getServiceManager());
        $factory->setConfig([]);
        $aggregateRoot = new PuppetClass();
        $aggregateRoot->setId(1);

        /** @var \KmbZendDbInfrastructure\Proxy\GroupProxy $proxy */
        $proxy = $factory->createProxy($aggregateRoot);

        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\PuppetClassProxy', $proxy);
        $this->assertEquals($aggregateRoot, $proxy->getAggregateRoot());
    }
}
