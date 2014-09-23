<?php
namespace KmbZendDbInfrastructureTest\Hydrator;

use KmbDomain\Model\Parameter;
use KmbDomain\Model\Value;
use KmbZendDbInfrastructure\Hydrator\ParameterHydrator;
use KmbZendDbInfrastructure\Hydrator\ValueHydrator;

class ValueHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $hydrator = new ValueHydrator();
        $parameter = new Parameter();
        $parameter->setId(2);
        $object = $this->createObject();
        $object->setParameter($parameter);

        $this->assertEquals($this->getData(), $hydrator->extract($object));
    }

    /** @test */
    public function canHydrate()
    {
        $hydrator = new ValueHydrator();

        $this->assertEquals($this->createObject(), $hydrator->hydrate($this->getData(), new Value()));
    }

    /** @test */
    public function canHydrateWithTablePrefix()
    {
        $hydrator = new ValueHydrator();
        $object = new Value();

        $hydrator->hydrate([
            'v.id' => 1,
            'v.parameter_id' => 2,
            'v.name' => 'ns1.local',
        ], $object);

        $this->assertEquals($this->createObject(), $object);
    }

    /**
     * @return Value
     */
    protected function createObject()
    {
        $object = new Value();
        $object->setId(1);
        $object->setName('ns1.local');

        return $object;
    }

    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'id' => 1,
            'name' => 'ns1.local',
            'parameter_id' => 2,
        ];
    }
}
