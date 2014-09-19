<?php
namespace KmbZendDbInfrastructureTest\Hydrator;

use KmbDomain\Model\Group;
use KmbDomain\Model\Revision;
use KmbZendDbInfrastructure\Hydrator\GroupHydrator;

class GroupHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $hydrator = new GroupHydrator();
        $revision = new Revision();
        $revision->setId(2);
        $group = $this->createObject();
        $group->setRevision($revision);

        $this->assertEquals($this->getData(), $hydrator->extract($group));
    }

    /** @test */
    public function canHydrate()
    {
        $hydrator = new GroupHydrator();
        $group = new Group();

        $hydrator->hydrate($this->getData(), $group);

        $this->assertEquals($this->createObject(), $group);
    }

    /** @test */
    public function canHydrateWithTablePrefix()
    {
        $hydrator = new GroupHydrator();
        $group = new Group();

        $hydrator->hydrate([
            'g.id' => 1,
            'g.name' => 'default',
            'g.ordering' => 3,
            'g.include_pattern' => '.*',
            'g.exclude_pattern' => 'node1.local',
        ], $group);

        $this->assertEquals($this->createObject(), $group);
    }

    /**
     * @return Group
     */
    protected function createObject()
    {
        $group = new Group('default');
        $group->setId(1);
        $group->setIncludePattern('.*');
        $group->setExcludePattern('node1.local');
        $group->setOrdering(3);

        return $group;
    }

    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'id' => 1,
            'revision_id' => 2,
            'name' => 'default',
            'ordering' => 3,
            'include_pattern' => '.*',
            'exclude_pattern' => 'node1.local',
        ];
    }
}
