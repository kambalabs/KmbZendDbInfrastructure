<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\PuppetClass;
use KmbZendDbInfrastructure\Proxy\PuppetClassProxy;

class PuppetClassProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var PuppetClassProxy */
    protected $proxy;

    /** @var PuppetClass */
    protected $aggregateRoot;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $parameterRepository;

    protected function setUp()
    {
        $this->parameterRepository = $this->getMock('KmbDomain\Model\ParameterRepositoryInterface');
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
     * @return PuppetClass
     */
    protected function createAggregateRoot($id = null)
    {
        $aggregateRoot = new PuppetClass();
        return $aggregateRoot->setId($id);
    }

    /**
     * @param $id
     * @return PuppetClassProxy
     */
    protected function createProxy($id = null)
    {
        $proxy = new PuppetClassProxy();
        return $proxy->setAggregateRoot($this->createAggregateRoot($id));
    }
}
