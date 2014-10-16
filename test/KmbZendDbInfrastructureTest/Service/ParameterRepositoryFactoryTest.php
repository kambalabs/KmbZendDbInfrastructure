<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\ParameterRepository;
use KmbZendDbInfrastructureTest\Bootstrap;

class ParameterRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var ParameterRepository $service */
        $service = Bootstrap::getServiceManager()->get('ParameterRepository');

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\ParameterRepository', $service);
        $this->assertEquals('values', $service->getValueTableName());
    }
}
