<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\Environment;
use KmbZendDbInfrastructure\Proxy\EnvironmentProxyFactory;
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

        /** @var \KmbZendDbInfrastructure\Proxy\EnvironmentProxy $proxy */
        $proxy = $factory->createProxy($environment);

        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\EnvironmentProxy', $proxy);
        $this->assertEquals($environment, $proxy->getAggregateRoot());
        $this->assertInstanceOf('KmbDomain\Model\EnvironmentRepositoryInterface', $proxy->getEnvironmentRepository());
        $this->assertInstanceOf('KmbDomain\Model\UserRepositoryInterface', $proxy->getUserRepository());
        $this->assertInstanceOf('KmbDomain\Model\RevisionRepositoryInterface', $proxy->getRevisionRepository());
    }
}
