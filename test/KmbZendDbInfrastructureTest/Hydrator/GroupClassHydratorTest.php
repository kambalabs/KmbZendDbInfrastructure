<?php
namespace KmbZendDbInfrastructureTest\Hydrator;

use KmbDomain\Model\Group;
use KmbDomain\Model\GroupClass;
use KmbZendDbInfrastructure\Hydrator\GroupClassHydrator;

class GroupClassHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $hydrator = new GroupClassHydrator();
        $group = new Group();
        $group->setId(2);
        $class = $this->createObject();
        $class->setGroup($group);

        $this->assertEquals($this->getData(), $hydrator->extract($class));
    }

    /** @test */
    public function canHydrate()
    {
        $hydrator = new GroupClassHydrator();

        $this->assertEquals($this->createObject(), $hydrator->hydrate($this->getData(), new GroupClass()));
    }

    /** @test */
    public function canHydrateWithTablePrefix()
    {
        $hydrator = new GroupClassHydrator();
        $class = new GroupClass();

        $hydrator->hydrate([
            'c.id' => 1,
            'c.group_id' => 2,
            'c.name' => 'dns',
        ], $class);

        $this->assertEquals($this->createObject(), $class);
    }

    /**
     * @return GroupClass
     */
    protected function createObject()
    {
        $class = new GroupClass();
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
