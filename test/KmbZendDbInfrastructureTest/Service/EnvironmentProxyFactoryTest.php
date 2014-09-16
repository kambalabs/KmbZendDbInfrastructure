<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\Environment;
use KmbZendDbInfrastructure\Service\EnvironmentProxyFactory;
use KmbZendDbInfrastructureTest\Bootstrap;

class EnvironmentProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateProxy()
    {
        $factory = new EnvironmentProxyFactory();
        $factory->setServiceManager(Bootstrap::getServiceManager());
        $factory->setConfig([]);
        $environment = new Environment();
        $environment->setId(1);
        $environment->setName('STABLE');

        /** @var \KmbZendDbInfrastructure\Model\EnvironmentProxy $proxy */
        $proxy = $factory->createProxy($environment);

        $this->assertInstanceOf('KmbZendDbInfrastructure\Model\EnvironmentProxy', $proxy);
        $this->assertEquals($environment, $proxy->getAggregateRoot());
        $this->assertInstanceOf('KmbDomain\Model\EnvironmentRepositoryInterface', $proxy->getEnvironmentRepository());
        $this->assertInstanceOf('KmbDomain\Model\UserRepositoryInterface', $proxy->getUserRepository());
    }
}
