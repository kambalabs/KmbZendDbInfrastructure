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
        $this->assertEquals('KmbDomain\Model\RevisionLog', $service->getRevisionLogClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\RevisionLogHydrator', $service->getRevisionLogHydrator());
        $this->assertEquals('revisions_logs', $service->getRevisionLogTableName());
        $this->assertEquals('revisions_logs_id_seq', $service->getRevisionLogTableSequenceName());
        $this->assertEquals('environments', $service->getEnvironmentTableName());
        $this->assertEquals('KmbDomain\Model\Environment', $service->getEnvironmentClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Proxy\EnvironmentProxyFactory', $service->getEnvironmentProxyFactory());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\EnvironmentHydrator', $service->getEnvironmentHydrator());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\GroupRepository', $service->getGroupRepository());
    }
}
