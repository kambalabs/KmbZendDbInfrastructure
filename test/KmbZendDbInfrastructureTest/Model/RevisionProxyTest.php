<?php
namespace KmbZendDbInfrastructureTest\Model;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Revision;
use KmbZendDbInfrastructure\Model\RevisionProxy;

class RevisionProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var RevisionProxy */
    protected $proxy;

    /** @var Revision */
    protected $aggregateRoot;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $environmentRepository;

    protected function setUp()
    {
        $this->environmentRepository = $this->getMock('KmbDomain\Model\EnvironmentRepositoryInterface');
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
        return $proxy->setAggregateRoot($this->createRevision($id));
    }
}
