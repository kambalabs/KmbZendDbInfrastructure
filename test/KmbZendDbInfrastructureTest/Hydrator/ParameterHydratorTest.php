<?php
namespace KmbZendDbInfrastructureTest\Hydrator;

use KmbDomain\Model\Parameter;
use KmbDomain\Model\PuppetClass;
use KmbZendDbInfrastructure\Hydrator\ParameterHydrator;

class ParameterHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $hydrator = new ParameterHydrator();
        $class = new PuppetClass();
        $class->setId(2);
        $object = $this->createObject();
        $object->setClass($class);

        $this->assertEquals($this->getData(), $hydrator->extract($object));
    }

    /** @test */
    public function canExtractWithParent()
    {
        $hydrator = new ParameterHydrator();
        $class = new PuppetClass();
        $class->setId(2);
        $parent = new Parameter();
        $parent->setId(3);
        $object = $this->createObject();
        $object->setClass($class);
        $object->setParent($parent);

        $this->assertEquals($this->getData(3), $hydrator->extract($object));
    }

    /** @test */
    public function canHydrate()
    {
        $hydrator = new ParameterHydrator();

        $this->assertEquals($this->createObject(), $hydrator->hydrate($this->getData(), new Parameter()));
    }

    /** @test */
    public function canHydrateWithTablePrefix()
    {
        $hydrator = new ParameterHydrator();
        $object = new Parameter();

        $hydrator->hydrate([
            'p.id' => 1,
            'p.puppet_class_id' => 2,
            'p.name' => 'nameserver',
        ], $object);

        $this->assertEquals($this->createObject(), $object);
    }

    /**
     * @return Parameter
     */
    protected function createObject()
    {
        $object = new Parameter();
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
            'puppet_class_id' => 2,
            'name' => 'nameserver',
            'parent_id' => $parent_id,
        ];
    }
}
