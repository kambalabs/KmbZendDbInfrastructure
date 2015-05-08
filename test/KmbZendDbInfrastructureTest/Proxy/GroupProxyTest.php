<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Group;
use KmbDomain\Model\Revision;
use KmbZendDbInfrastructure\Proxy\GroupProxy;

class GroupProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var GroupProxy */
    protected $proxy;

    /** @var Group */
    protected $aggregateRoot;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $revisionRepository;

    protected function setUp()
    {
        $this->revisionRepository = $this->getMock('KmbDomain\Service\RevisionRepositoryInterface');
        $this->proxy = $this->createProxy(3);
        $this->proxy->setRevisionRepository($this->revisionRepository);
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
    public function canGetRevisionFromRepository()
    {
        $revision = new Revision();
        $revision->setId(1);
        $this->revisionRepository->expects($this->any())
            ->method('getByGroup')
            ->with($this->proxy)
            ->will($this->returnValue($revision));

        $this->assertEquals($revision, $this->proxy->getRevision());
    }

    /** @test */
    public function canGetEnvironmentFromRevision()
    {
        $environment = new Environment('STABLE');
        $revision = new Revision($environment);
        $revision->setId(1);
        $this->revisionRepository->expects($this->any())
            ->method('getByGroup')
            ->with($this->proxy)
            ->will($this->returnValue($revision));

        $this->assertEquals($environment, $this->proxy->getEnvironment());
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
