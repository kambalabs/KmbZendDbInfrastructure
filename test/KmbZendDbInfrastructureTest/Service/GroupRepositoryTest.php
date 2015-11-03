<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\Group;
use KmbDomain\Model\GroupClass;
use KmbDomain\Model\GroupInterface;
use KmbZendDbInfrastructure\Service\GroupRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class GroupRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

    /** @var \PDO */
    protected static $connection;

    /** @var GroupRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('GroupRepository');

        /** @var $dbAdapter AdapterInterface */
        $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
        static::$connection = $dbAdapter->getDriver()->getConnection()->getResource();

        static::initSchema(static::$connection);
    }

    protected function setUp()
    {
        static::initFixtures(static::$connection);
    }

    /** @test */
    public function canAdd()
    {
        $revision = Bootstrap::getServiceManager()->get('RevisionRepository')->getById(8);
        $group = new Group('new group');
        $group->setRevision($revision);
        $group->setClasses([new GroupClass('dns'), new GroupClass('ntp')]);

        static::$repository->add($group);

        $this->assertEquals(8, $group->getId());
        $this->assertEquals(3, $group->getOrdering());
        $this->assertEquals(12, $group->getClassByName('dns')->getId());
    }

    /** @test */
    public function canAddWithOrdering()
    {
        $revision = Bootstrap::getServiceManager()->get('RevisionRepository')->getById(8);
        $group = new Group('new group');
        $group->setOrdering(1);
        $group->setRevision($revision);

        static::$repository->add($group);

        $this->assertEquals(9, $group->getId()); // Sequence is not reinit during test setup
        $this->assertEquals(1, intval(static::$connection->query('SELECT ordering FROM groups WHERE id = 9')->fetchColumn()));
        $this->assertEquals(0, intval(static::$connection->query('SELECT ordering FROM groups WHERE id = 1')->fetchColumn()));
        $this->assertEquals(3, intval(static::$connection->query('SELECT ordering FROM groups WHERE id = 2')->fetchColumn()));
        $this->assertEquals(2, intval(static::$connection->query('SELECT ordering FROM groups WHERE id = 3')->fetchColumn()));
    }

    /** @test */
    public function canGetById()
    {
        /** @var GroupInterface $group */
        $group = static::$repository->getById(6);

        $this->assertInstanceOf('KmbDomain\Model\GroupInterface', $group);
        $this->assertEquals(6, $group->getId());
        $classes = $group->getClasses();
        $this->assertEquals(3, count($classes));
        $this->assertEquals('apache::vhost', $classes[1]->getName());
        $parameters = $classes[1]->getParameters();
        $this->assertEquals(2, count($parameters));
        $this->assertEquals('ports', $parameters[1]->getName());
        $this->assertEquals(['80', '443'], $parameters[1]->getValues());
    }

    /** @test */
    public function canGetAllByIds()
    {
        /** @var GroupInterface[] $groups */
        $groups = static::$repository->getAllByIds([1, 3, 6, 999]);

        $this->assertEquals(3, count($groups));
        $group = $groups[0];
        $this->assertInstanceOf('KmbDomain\Model\GroupInterface', $group);
        $this->assertEquals('default', $group->getName());
    }

    /** @test */
    public function canGetAllByRevision()
    {
        $revision = Bootstrap::getServiceManager()->get('RevisionRepository')->getById(8);

        $groups = static::$repository->getAllByRevision($revision);

        $this->assertEquals(3, count($groups));
        $secondGroup = $groups[1];
        $this->assertInstanceOf('KmbDomain\Model\GroupInterface', $secondGroup);
        $this->assertEquals(3, $secondGroup->getId());
    }

    /** @test */
    public function cannotGetUnknownByNameAndRevision()
    {
        $revision = Bootstrap::getServiceManager()->get('RevisionRepository')->getById(8);

        $group = static::$repository->getByNameAndRevision('sql', $revision);

        $this->assertNull($group);
    }

    /** @test */
    public function canGetByNameAndRevision()
    {
        $revision = Bootstrap::getServiceManager()->get('RevisionRepository')->getById(9);

        $group = static::$repository->getByNameAndRevision('sql', $revision);

        $this->assertEquals(7, $group->getId());
    }

    /** @test */
    public function canGetByClass()
    {
        $class = Bootstrap::getServiceManager()->get('GroupClassRepository')->getById(5);

        $group = static::$repository->getByClass($class);

        $this->assertEquals(4, $group->getId());
    }
}
