<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\Revision;
use KmbZendDbInfrastructure\Proxy\RevisionProxyFactory;
use KmbZendDbInfrastructureTest\Bootstrap;

class RevisionProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateProxy()
    {
        $factory = new RevisionProxyFactory();
        $factory->setServiceManager(Bootstrap::getServiceManager());
        $factory->setConfig([]);
        $revision = new Revision();
        $revision->setId(1);

        /** @var \KmbZendDbInfrastructure\Proxy\RevisionProxy $proxy */
        $proxy = $factory->createProxy($revision);

        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\RevisionProxy', $proxy);
        $this->assertEquals($revision, $proxy->getAggregateRoot());
        $this->assertInstanceOf('KmbDomain\Service\GroupRepositoryInterface', $proxy->getGroupRepository());
    }
}
