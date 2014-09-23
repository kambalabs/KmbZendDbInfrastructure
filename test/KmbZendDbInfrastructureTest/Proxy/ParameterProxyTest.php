<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\Parameter;
use KmbDomain\Model\PuppetClass;
use KmbZendDbInfrastructure\Proxy\ParameterProxy;

class ParameterProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var ParameterProxy */
    protected $proxy;

    /** @var Parameter */
    protected $aggregateRoot;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $classRepository;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $parameterRepository;

    protected function setUp()
    {
        $this->classRepository = $this->getMock('KmbDomain\Model\PuppetClassRepositoryInterface');
        $this->parameterRepository = $this->getMock('KmbDomain\Model\ParameterRepositoryInterface');
        $this->proxy = $this->createProxy(3, 'nameserver');
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
    public function canSetName()
    {
        $this->proxy->setName('new_name');

        $this->assertEquals('new_name', $this->aggregateRoot->getName());
    }

    /** @test */
    public function canGetName()
    {
        $this->assertEquals('nameserver', $this->proxy->getName());
    }

    /** @test */
    public function canGetClassFromRepository()
    {
        $class = new PuppetClass();
        $class->setId(3);
        $this->classRepository->expects($this->any())
            ->method('getByParameter')
            ->with($this->proxy)
            ->will($this->returnValue($class));

        $this->assertEquals($class, $this->proxy->getClass());
    }

    /** @test */
    public function canGetParentFromRepository()
    {
        $parent = new Parameter();
        $parent->setId(3);
        $this->parameterRepository->expects($this->any())
            ->method('getByChild')
            ->with($this->proxy)
            ->will($this->returnValue($parent));

        $this->assertEquals($parent, $this->proxy->getParent());
    }

    /** @test */
    public function canCheckIfHasParentFromRepository()
    {
        $this->parameterRepository->expects($this->any())
            ->method('getByChild')
            ->with($this->proxy)
            ->will($this->returnValue(new Parameter()));

        $this->assertTrue($this->proxy->hasParent());
    }

    /** @test */
    public function canGetChildrenFromRepository()
    {
        $children = [new Parameter(), new Parameter()];
        $this->parameterRepository->expects($this->any())
            ->method('getAllByParent')
            ->with($this->proxy)
            ->will($this->returnValue($children));

        $this->assertEquals($children, $this->proxy->getChildren());
    }

    /** @test */
    public function canCheckIfHasChildrenFromRepository()
    {
        $this->parameterRepository->expects($this->any())
            ->method('getAllByParent')
            ->with($this->proxy)
            ->will($this->returnValue([new Parameter(), new Parameter()]));

        $this->assertTrue($this->proxy->hasChildren());
    }

    /**
     * @param int    $id
     * @param string $name
     * @return Parameter
     */
    protected function createAggregateRoot($id = null, $name = null)
    {
        $aggregateRoot = new Parameter();
        $aggregateRoot->setId($id);
        return $aggregateRoot->setName($name);
    }

    /**
     * @param int    $id
     * @param string $name
     * @return ParameterProxy
     */
    protected function createProxy($id = null, $name = null)
    {
        $proxy = new ParameterProxy();
        $proxy->setClassRepository($this->classRepository);
        $proxy->setParameterRepository($this->parameterRepository);
        return $proxy->setAggregateRoot($this->createAggregateRoot($id, $name));
    }
}
