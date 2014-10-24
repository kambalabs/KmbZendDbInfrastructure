<?php
namespace KmbZendDbInfrastructureTest\Proxy;

use KmbDomain\Model\GroupParameter;
use KmbDomain\Model\GroupClass;
use KmbZendDbInfrastructure\Proxy\GroupParameterProxy;

class GroupParameterProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var GroupParameterProxy */
    protected $proxy;

    /** @var GroupParameter */
    protected $aggregateRoot;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $groupClassRepository;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $groupParameterRepository;

    protected function setUp()
    {
        $this->groupClassRepository = $this->getMock('KmbDomain\Model\GroupClassRepositoryInterface');
        $this->groupParameterRepository = $this->getMock('KmbDomain\Model\GroupParameterRepositoryInterface');
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
        $class = new GroupClass();
        $class->setId(3);
        $this->groupClassRepository->expects($this->any())
            ->method('getByParameter')
            ->with($this->proxy)
            ->will($this->returnValue($class));

        $this->assertEquals($class, $this->proxy->getClass());
    }

    /** @test */
    public function canGetParentFromRepository()
    {
        $parent = new GroupParameter();
        $parent->setId(3);
        $this->groupParameterRepository->expects($this->any())
            ->method('getByChild')
            ->with($this->proxy)
            ->will($this->returnValue($parent));

        $this->assertEquals($parent, $this->proxy->getParent());
    }

    /** @test */
    public function canGetAncestorsNames()
    {
        $parent = new GroupParameter();
        $parent->setName('parent');
        $this->groupParameterRepository->expects($this->any())
            ->method('getByChild')
            ->with($this->proxy)
            ->will($this->returnValue($parent));

        $this->assertEquals(['parent', 'nameserver'], $this->proxy->getAncestorsNames());
    }

    /** @test */
    public function canCheckIfHasParentFromRepository()
    {
        $this->groupParameterRepository->expects($this->any())
            ->method('getByChild')
            ->with($this->proxy)
            ->will($this->returnValue(new GroupParameter()));

        $this->assertTrue($this->proxy->hasParent());
    }

    /** @test */
    public function canGetChildrenFromRepository()
    {
        $children = [new GroupParameter(), new GroupParameter()];
        $this->groupParameterRepository->expects($this->any())
            ->method('getAllByParent')
            ->with($this->proxy)
            ->will($this->returnValue($children));

        $this->assertEquals($children, $this->proxy->getChildren());
    }

    /** @test */
    public function canCheckIfHasChildrenFromRepository()
    {
        $this->groupParameterRepository->expects($this->any())
            ->method('getAllByParent')
            ->with($this->proxy)
            ->will($this->returnValue([new GroupParameter(), new GroupParameter()]));

        $this->assertTrue($this->proxy->hasChildren());
    }

    /** @test */
    public function canGetChildByNameFromRepository()
    {
        $child = new GroupParameter();
        $child->setName('DocumentRoot');
        $this->groupParameterRepository->expects($this->any())
            ->method('getAllByParent')
            ->with($this->proxy)
            ->will($this->returnValue([new GroupParameter(), $child]));

        $this->assertEquals($child, $this->proxy->getChildByName('DocumentRoot'));
    }

    /**
     * @param int    $id
     * @param string $name
     * @return GroupParameter
     */
    protected function createAggregateRoot($id = null, $name = null)
    {
        $aggregateRoot = new GroupParameter();
        $aggregateRoot->setId($id);
        return $aggregateRoot->setName($name);
    }

    /**
     * @param int    $id
     * @param string $name
     * @return GroupParameterProxy
     */
    protected function createProxy($id = null, $name = null)
    {
        $proxy = new GroupParameterProxy();
        $proxy->setGroupClassRepository($this->groupClassRepository);
        $proxy->setGroupParameterRepository($this->groupParameterRepository);
        return $proxy->setAggregateRoot($this->createAggregateRoot($id, $name));
    }
}
