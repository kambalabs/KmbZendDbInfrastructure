<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\RevisionRepository;
use KmbZendDbInfrastructureTest\Bootstrap;

class RevisionRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var RevisionRepository $service */
        $service = Bootstrap::getServiceManager()->get('RevisionRepository');

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\RevisionRepository', $service);
        $this->assertEquals('environments', $service->getEnvironmentTableName());
        $this->assertEquals('KmbDomain\Model\Environment', $service->getEnvironmentClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\EnvironmentProxyFactory', $service->getEnvironmentProxyFactory());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\EnvironmentHydrator', $service->getEnvironmentHydrator());
    }
}
