<?php
namespace KmbZendDbInfrastructureTest\Model;

use KmbDomain\Model\Environment;
use KmbZendDbInfrastructure\Model\EnvironmentProxy;
use KmbDomain\Model\User;
use Zend\Stdlib\ArrayUtils;

class EnvironmentProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var EnvironmentProxy */
    protected $grandpa;

    /** @var EnvironmentProxy */
    protected $parent;

    /** @var EnvironmentProxy */
    protected $proxy;

    /** @var Environment */
    protected $aggregateRoot;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $environmentRepository;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $userRepository;

    protected function setUp()
    {
        $this->environmentRepository = $this->getMock('KmbDomain\Model\EnvironmentRepositoryInterface');
        $this->userRepository = $this->getMock('KmbDomain\Model\UserRepositoryInterface');
        $this->grandpa = $this->createProxy(1, 'ROOT');
        $this->parent = $this->createProxy(2, 'STABLE');
        $this->proxy = $this->createProxy(3, 'PF1');
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
        $this->proxy->setName('PF2');

        $this->assertEquals('PF2', $this->aggregateRoot->getName());
    }

    /** @test */
    public function canGetName()
    {
        $this->assertEquals('PF1', $this->proxy->getName());
    }

    /** @test */
    public function canGetAncestorsNames()
    {
        $this->parent->setParent($this->grandpa);
        $this->proxy->setParent($this->parent);

        $this->assertEquals(['ROOT', 'STABLE', 'PF1'], $this->proxy->getAncestorsNames());
    }

    /** @test */
    public function canGetNormalizedName()
    {
        $this->proxy->setParent($this->parent);

        $this->assertEquals('STABLE_PF1', $this->proxy->getNormalizedName());
    }

    /** @test */
    public function canGetParentFromRepository()
    {
        $this->environmentRepository->expects($this->any())
            ->method('getParent')
            ->with($this->equalTo($this->proxy))
            ->will($this->returnValue($this->parent));

        $this->assertEquals($this->parent, $this->proxy->getParent());
    }

    /** @test */
    public function canGetChildrenFromRepository()
    {
        $children = $this->getChildren();
        $this->environmentRepository->expects($this->any())
            ->method('getAllChildren')
            ->with($this->equalTo($this->proxy))
            ->will($this->returnValue($children));

        $this->assertEquals($children, $this->proxy->getChildren());
    }

    /** @test */
    public function canCheckIfIsNotAncestorOf()
    {
        $this->assertFalse($this->proxy->isAncestorOf($this->grandpa));
    }

    /** @test */
    public function canCheckIfIsAncestorOf()
    {
        $this->parent->setParent($this->grandpa);
        $this->proxy->setParent($this->parent);

        $this->assertTrue($this->grandpa->isAncestorOf($this->proxy));
    }

    /** @test */
    public function canCheckIfHasNotChildWithName()
    {
        $this->assertFalse($this->proxy->hasChildWithName('ITG'));
    }

    /** @test */
    public function canCheckIfHasChildWithName()
    {
        $children = $this->getChildren();
        $this->environmentRepository->expects($this->any())
            ->method('getAllChildren')
            ->with($this->equalTo($this->proxy))
            ->will($this->returnValue($children));

        $this->assertTrue($this->proxy->hasChildWithName('PROD'));
    }

    /** @test */
    public function canGetUsersFromRepository()
    {
        $users = [new User('jdoe'), new User('jmiller')];
        $this->userRepository->expects($this->any())
            ->method('getAllByEnvironment')
            ->with($this->equalTo($this->proxy))
            ->will($this->returnValue($users));

        $this->assertEquals($users, $this->proxy->getUsers());
    }

    /** @test */
    public function canAddUsers()
    {
        $users = [
            (new User('jdoe'))->setId(1),
            (new User('jmiller'))->setId(2)
        ];
        $this->userRepository->expects($this->any())
            ->method('getAllByEnvironment')
            ->with($this->equalTo($this->proxy))
            ->will($this->returnValue($users));

        $this->proxy->addUsers([
            (new User('jmiller'))->setId(2),
            (new User('psmith'))->setId(3),
            (new User('mcooper'))->setId(4)
        ]);

        $users = $this->proxy->getUsers();
        $this->assertEquals(4, count($users));
        $this->assertEquals('psmith', $users[2]->getLogin());
    }

    /** @test */
    public function canRemoveUserById()
    {
        $user = (new User('jmiller'))->setId(2);
        $users = [(new User('jdoe'))->setId(1), $user];
        $this->userRepository->expects($this->any())
            ->method('getAllByEnvironment')
            ->with($this->equalTo($this->proxy))
            ->will($this->returnValue($users));

        $this->proxy->removeUserById(2);

        $this->assertEquals(1, count($this->proxy->getUsers()));
    }

    /** @test */
    public function canCheckIfNotHasUsers()
    {
        $this->assertFalse($this->proxy->hasUsers());
    }

    /** @test */
    public function canCheckIfHasUsers()
    {
        $users = [new User('jdoe'), new User('jmiller')];
        $this->userRepository->expects($this->any())
            ->method('getAllByEnvironment')
            ->with($this->equalTo($this->proxy))
            ->will($this->returnValue($users));

        $this->assertTrue($this->proxy->hasUsers());
    }

    /** @test */
    public function canCheckIfNotHasUser()
    {
        $this->assertFalse($this->proxy->hasUser(new User('jdoe')));
    }

    /** @test */
    public function canCheckIfHasUser()
    {
        $jane = new User('jmiller');
        $users = [new User('jdoe'), $jane];
        $this->userRepository->expects($this->any())
            ->method('getAllByEnvironment')
            ->with($this->equalTo($this->proxy))
            ->will($this->returnValue($users));

        $this->assertTrue($this->proxy->hasUser($jane));
    }

    /** @test */
    public function canSetDefault()
    {
        $this->proxy->setDefault(true);

        $this->assertTrue($this->aggregateRoot->isDefault());
    }

    /** @test */
    public function canGetDefault()
    {
        $this->proxy->setDefault(true);

        $this->assertTrue($this->proxy->isDefault());
    }

    /** @test */
    public function canGetDescendants()
    {
        $children = $this->getChildren();
        $this->proxy->setChildren($children);
        $this->parent->setChildren([$this->proxy]);
        $this->environmentRepository->expects($this->any())
            ->method('getAllChildren')
            ->with($this->equalTo($this->grandpa))
            ->will($this->returnValue([$this->parent]));

        $descendants = $this->grandpa->getDescendants();

        $this->assertEquals(ArrayUtils::merge([$this->parent, $this->proxy], $children), $descendants);
    }

    /**
     * @return array
     */
    protected function getChildren()
    {
        $child1 = $this->createProxy(4, 'PRP');
        $child1->setParent($this->proxy);
        $child1->setChildren([]);

        $child2 = $this->createProxy(5, 'PROD');
        $child2->setParent($this->proxy);
        $child2->setChildren([]);

        return [$child1, $child2];
    }

    /**
     * @param $id
     * @param $name
     * @return Environment
     */
    protected function createEnvironment($id = null, $name = null)
    {
        $environment = new Environment();
        return $environment->setId($id)->setName($name)->setDefault(false);
    }

    /**
     * @param $id
     * @param $name
     * @return EnvironmentProxy
     */
    protected function createProxy($id = null, $name = null)
    {
        $proxy = new EnvironmentProxy();
        $proxy->setEnvironmentRepository($this->environmentRepository);
        $proxy->setUserRepository($this->userRepository);
        return $proxy->setAggregateRoot($this->createEnvironment($id, $name));
    }
}
