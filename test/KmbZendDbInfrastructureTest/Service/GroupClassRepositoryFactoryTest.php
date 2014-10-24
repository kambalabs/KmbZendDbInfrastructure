<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\GroupClassRepository;
use KmbZendDbInfrastructureTest\Bootstrap;

class GroupClassRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var GroupClassRepository $service */
        $service = Bootstrap::getServiceManager()->get('GroupClassRepository');

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\GroupClassRepository', $service);
        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\GroupParameterRepository', $service->getGroupParameterRepository());
    }
}
