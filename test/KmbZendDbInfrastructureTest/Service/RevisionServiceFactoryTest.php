<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\RevisionService;
use KmbZendDbInfrastructureTest\Bootstrap;

class RevisionServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var RevisionService $service */
        $service = Bootstrap::getServiceManager()->get('revisionService');

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\RevisionService', $service);
        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\RevisionRepository', $service->getRevisionRepository());
        $this->assertInstanceOf('KmbBase\DateTimeFactory', $service->getDateTimeFactory());
    }
}
