<?php
namespace KmbZendDbInfrastructureTest\Hydrator;

use KmbDomain\Model\Environment;
use KmbZendDbInfrastructure\Hydrator\EnvironmentHydrator;

class EnvironmentHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $environment = new Environment();
        $environment->setId(1);
        $environment->setName('STABLE');
        $environment->setDefault(true);
        $hydrator = new EnvironmentHydrator();

        $this->assertEquals([
            'id' => 1,
            'name' => 'STABLE',
            'isdefault' => 1
        ], $hydrator->extract($environment));
    }

    /** @test */
    public function canExtractWithNullId()
    {
        $environment = new Environment();
        $environment->setName('STABLE');
        $hydrator = new EnvironmentHydrator();

        $this->assertEquals([
            'name' => 'STABLE',
            'isdefault' => 0,
        ], $hydrator->extract($environment));
    }

    /** @test */
    public function canHydrate()
    {
        $environment = new Environment();
        $hydrator = new \KmbZendDbInfrastructure\Hydrator\EnvironmentHydrator();

        $hydratedEnvironment = $hydrator->hydrate([
            'id' => 1,
            'name' => 'STABLE',
            'isdefault' => 1
        ], $environment);

        $this->assertEquals(1, $hydratedEnvironment->getId());
        $this->assertEquals('STABLE', $hydratedEnvironment->getName());
        $this->assertTrue($hydratedEnvironment->isDefault());
    }

    /** @test */
    public function canHydrateWithTablePrefix()
    {
        $environment = new Environment();
        $hydrator = new \KmbZendDbInfrastructure\Hydrator\EnvironmentHydrator();

        $hydratedEnvironment = $hydrator->hydrate([
            'e.id' => 1,
            'e.name' => 'STABLE',
            'e.isdefault' => 1
        ], $environment);

        $this->assertEquals(1, $hydratedEnvironment->getId());
        $this->assertEquals('STABLE', $hydratedEnvironment->getName());
        $this->assertTrue($hydratedEnvironment->isDefault());
    }
}
