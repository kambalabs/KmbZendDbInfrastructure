<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\Parameter;
use KmbZendDbInfrastructure\Proxy\ParameterProxyFactory;
use KmbZendDbInfrastructureTest\Bootstrap;

class ParameterProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateProxy()
    {
        $factory = new ParameterProxyFactory();
        $factory->setServiceManager(Bootstrap::getServiceManager());
        $factory->setConfig([]);
        $aggregateRoot = new Parameter();
        $aggregateRoot->setId(1);

        /** @var \KmbZendDbInfrastructure\Proxy\ParameterProxy $proxy */
        $proxy = $factory->createProxy($aggregateRoot);

        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\ParameterProxy', $proxy);
        $this->assertEquals($aggregateRoot, $proxy->getAggregateRoot());
    }
}
