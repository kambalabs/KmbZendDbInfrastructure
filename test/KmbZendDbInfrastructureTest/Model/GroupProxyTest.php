<?php
namespace KmbZendDbInfrastructureTest\Model;

use KmbDomain\Model\Group;
use KmbZendDbInfrastructure\Model\GroupProxy;

class GroupProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var GroupProxy */
    protected $proxy;

    /** @var Group */
    protected $aggregateRoot;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $puppetClassRepository;

    protected function setUp()
    {
        $this->puppetClassRepository = $this->getMock('KmbDomain\Model\PuppetClassRepositoryInterface');
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

    /**
     * @param $id
     * @return Group
     */
    protected function createAggregateRoot($id = null)
    {
        $aggregateRoot = new Group();
        return $aggregateRoot->setId($id);
    }

    /**
     * @param $id
     * @return GroupProxy
     */
    protected function createProxy($id = null)
    {
        $proxy = new GroupProxy();
        return $proxy->setAggregateRoot($this->createAggregateRoot($id));
    }
}
