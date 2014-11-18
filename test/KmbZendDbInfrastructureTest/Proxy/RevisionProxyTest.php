<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Group;
use KmbDomain\Model\Revision;
use KmbZendDbInfrastructure\Proxy\RevisionProxy;

class RevisionProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var RevisionProxy */
    protected $proxy;

    /** @var Revision */
    protected $aggregateRoot;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $groupRepository;

    protected function setUp()
    {
        $this->groupRepository = $this->getMock('KmbDomain\Model\GroupRepositoryInterface');
        $this->proxy = $this->createProxy(3);
        $this->aggregateRoot = $this->proxy->getAggregateRoot();
    }

    /** @test */
    public function canSetId()
    {
        $this->proxy->setId(4);

        $this->assertEquals(4, $this->aggregateRoot->getId());
    }

    /** @test */
    public function canGetId()
    {
        $this->assertEquals(3, $this->proxy->getId());
    }

    /** @test */
    public function canSetEnvironment()
    {
        $environment = new Environment();
        $environment->setId(1);
        $environment->setName('STABLE');
        $this->proxy->setEnvironment($environment);

        $this->assertEquals($environment, $this->aggregateRoot->getEnvironment());
    }

    /** @test */
    public function canGetEnvironment()
    {
        $environment = new Environment();
        $environment->setId(1);
        $environment->setName('STABLE');
        $this->aggregateRoot->setEnvironment($environment);

        $this->assertEquals($environment, $this->proxy->getEnvironment());
    }

    /** @test */
    public function canGetGroupsFromRepository()
    {
        $groups = [new Group('default'), new Group('web')];
        $this->groupRepository->expects($this->any())
            ->method('getAllByRevision')
            ->with($this->proxy)
            ->will($this->returnValue($groups));

        $this->assertEquals($groups, $this->proxy->getGroups());
    }

    /** @test */
    public function canCheckIfHasGroupsFromRepository()
    {
        $groups = [new Group('default'), new Group('web')];
        $this->groupRepository->expects($this->any())
            ->method('getAllByRevision')
            ->with($this->proxy)
            ->will($this->returnValue($groups));

        $this->assertTrue($this->proxy->hasGroups());
    }

    /** @test */
    public function canCheckIfHasGroupWithNameFromRepository()
    {
        $groups = [new Group('default'), new Group('web')];
        $this->groupRepository->expects($this->any())
            ->method('getAllByRevision')
            ->with($this->proxy)
            ->will($this->returnValue($groups));

        $this->assertTrue($this->proxy->hasGroupWithName('default'));
    }

    /**
     * @param $id
     * @return Revision
     */
    protected function createRevision($id = null)
    {
        $revision = new Revision();
        return $revision->setId($id);
    }

    /**
     * @param $id
     * @return RevisionProxy
     */
    protected function createProxy($id = null)
    {
        $proxy = new RevisionProxy();
        $proxy->setGroupRepository($this->groupRepository);
        return $proxy->setAggregateRoot($this->createRevision($id));
    }
}
