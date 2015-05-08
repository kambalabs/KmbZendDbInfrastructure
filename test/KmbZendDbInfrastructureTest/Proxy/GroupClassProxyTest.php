<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\Group;
use KmbDomain\Model\GroupClass;
use KmbZendDbInfrastructure\Proxy\GroupClassProxy;

class GroupClassProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var GroupClassProxy */
    protected $proxy;

    /** @var GroupClass */
    protected $aggregateRoot;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $groupRepository;

    protected function setUp()
    {
        $this->groupRepository = $this->getMock('KmbDomain\Service\GroupRepositoryInterface');
        $this->proxy = $this->createProxy(3);
        $this->proxy->setGroupRepository($this->groupRepository);
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
    public function canGetGroupFromRepository()
    {
        $group = new Group();
        $group->setId(3);
        $this->groupRepository->expects($this->any())
            ->method('getByClass')
            ->with($this->proxy)
            ->will($this->returnValue($group));

        $this->assertEquals($group, $this->proxy->getGroup());
    }

    /**
     * @param $id
     * @return GroupClass
     */
    protected function createAggregateRoot($id = null)
    {
        $aggregateRoot = new GroupClass();
        return $aggregateRoot->setId($id);
    }

    /**
     * @param $id
     * @return GroupClassProxy
     */
    protected function createProxy($id = null)
    {
        $proxy = new GroupClassProxy();
        return $proxy->setAggregateRoot($this->createAggregateRoot($id));
    }
}
