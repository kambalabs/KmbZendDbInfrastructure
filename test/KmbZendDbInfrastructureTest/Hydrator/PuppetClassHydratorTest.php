<?php
namespace KmbZendDbInfrastructureTest\Hydrator;

use KmbDomain\Model\Group;
use KmbDomain\Model\PuppetClass;
use KmbZendDbInfrastructure\Hydrator\PuppetClassHydrator;

class PuppetClassHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $hydrator = new PuppetClassHydrator();
        $group = new Group();
        $group->setId(2);
        $class = $this->createObject();
        $class->setGroup($group);

        $this->assertEquals($this->getData(), $hydrator->extract($class));
    }

    /** @test */
    public function canHydrate()
    {
        $hydrator = new PuppetClassHydrator();

        $this->assertEquals($this->createObject(), $hydrator->hydrate($this->getData(), new PuppetClass()));
    }

    /** @test */
    public function canHydrateWithTablePrefix()
    {
        $hydrator = new PuppetClassHydrator();
        $class = new PuppetClass();

        $hydrator->hydrate([
            'c.id' => 1,
            'c.group_id' => 2,
            'c.name' => 'dns',
        ], $class);

        $this->assertEquals($this->createObject(), $class);
    }

    /**
     * @return PuppetClass
     */
    protected function createObject()
    {
        $class = new PuppetClass();
        $class->setId(1);
        $class->setName('dns');

        return $class;
    }

    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'id' => 1,
            'group_id' => 2,
            'name' => 'dns',
        ];
    }
}
