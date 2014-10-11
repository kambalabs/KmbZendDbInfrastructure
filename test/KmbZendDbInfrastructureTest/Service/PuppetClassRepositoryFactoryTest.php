<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\PuppetClassRepository;
use KmbZendDbInfrastructureTest\Bootstrap;

class PuppetClassRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var PuppetClassRepository $service */
        $service = Bootstrap::getServiceManager()->get('PuppetClassRepository');

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\PuppetClassRepository', $service);
        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\ParameterRepository', $service->getParameterRepository());
    }
}
