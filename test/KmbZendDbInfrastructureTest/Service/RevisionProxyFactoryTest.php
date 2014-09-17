<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\Revision;
use KmbZendDbInfrastructure\Service\RevisionProxyFactory;
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

        /** @var \KmbZendDbInfrastructure\Model\RevisionProxy $proxy */
        $proxy = $factory->createProxy($revision);

        $this->assertInstanceOf('KmbZendDbInfrastructure\Model\RevisionProxy', $proxy);
        $this->assertEquals($revision, $proxy->getAggregateRoot());
    }
}
