<?php
namespace KmbZendDbInfrastructureTest\Hydrator;

use KmbDomain\Model\GroupParameter;
use KmbDomain\Model\GroupClass;
use KmbZendDbInfrastructure\Hydrator\GroupParameterHydrator;

class GroupParameterHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $hydrator = new GroupParameterHydrator();
        $class = new GroupClass();
        $class->setId(2);
        $object = $this->createObject();
        $object->setClass($class);

        $this->assertEquals($this->getData(), $hydrator->extract($object));
    }

    /** @test */
    public function canExtractWithParent()
    {
        $hydrator = new GroupParameterHydrator();
        $class = new GroupClass();
        $class->setId(2);
        $parent = new GroupParameter();
        $parent->setId(3);
        $object = $this->createObject();
        $object->setClass($class);
        $object->setParent($parent);

        $this->assertEquals($this->getData(3), $hydrator->extract($object));
    }

    /** @test */
    public function canHydrate()
    {
        $hydrator = new GroupParameterHydrator();

        $this->assertEquals($this->createObject(), $hydrator->hydrate($this->getData(), new GroupParameter()));
    }

    /** @test */
    public function canHydrateWithTablePrefix()
    {
        $hydrator = new GroupParameterHydrator();
        $object = new GroupParameter();

        $hydrator->hydrate([
            'p.id' => 1,
            'p.group_class_id' => 2,
            'p.name' => 'nameserver',
        ], $object);

        $this->assertEquals($this->createObject(), $object);
    }

    /**
     * @return GroupParameter
     */
    protected function createObject()
    {
        $object = new GroupParameter();
        $object->setId(1);
        $object->setName('nameserver');

        return $object;
    }

    /**
     * @param int $parent_id
     * @return array
     */
    protected function getData($parent_id = null)
    {
        return [
            'id' => 1,
            'group_class_id' => 2,
            'name' => 'nameserver',
            'parent_id' => $parent_id,
        ];
    }
}
