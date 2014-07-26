<?php
namespace KmbZendDbInfrastructureTest\Model;

use KmbDomain\Model\Environment;
use KmbZendDbInfrastructure\Model\EnvironmentHydrator;

class EnvironmentHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $environment = new Environment();
        $environment->setId(1);
        $environment->setName('STABLE');
        $hydrator = new EnvironmentHydrator();

        $this->assertEquals([
            'id' => 1,
            'name' => 'STABLE'
        ], $hydrator->extract($environment));
    }

    /** @test */
    public function canExtractWithNullId()
    {
        $environment = new Environment();
        $environment->setName('STABLE');
        $hydrator = new EnvironmentHydrator();

        $this->assertEquals([
            'name' => 'STABLE'
        ], $hydrator->extract($environment));
    }

    /** @test */
    public function canHydrate()
    {
        $environment = new Environment();
        $hydrator = new EnvironmentHydrator();

        $hydratedEnvironment = $hydrator->hydrate([
            'id' => 1,
            'name' => 'STABLE',
        ], $environment);

        $this->assertEquals(1, $hydratedEnvironment->getId());
        $this->assertEquals('STABLE', $hydratedEnvironment->getName());
    }
}
