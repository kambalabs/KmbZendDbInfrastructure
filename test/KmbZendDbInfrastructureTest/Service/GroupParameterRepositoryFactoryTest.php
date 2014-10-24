<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\GroupParameterRepository;
use KmbZendDbInfrastructureTest\Bootstrap;

class GroupParameterRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var GroupParameterRepository $service */
        $service = Bootstrap::getServiceManager()->get('GroupParameterRepository');

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\GroupParameterRepository', $service);
        $this->assertEquals('group_values', $service->getGroupValueTableName());
    }
}
