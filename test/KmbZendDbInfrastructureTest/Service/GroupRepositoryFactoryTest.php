<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\GroupRepository;
use KmbZendDbInfrastructureTest\Bootstrap;

class GroupRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var GroupRepository $service */
        $service = Bootstrap::getServiceManager()->get('GroupRepository');

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\GroupRepository', $service);
        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\GroupClassRepository', $service->getGroupClassRepository());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\GroupParameterRepository', $service->getGroupParameterRepository());
    }
}
