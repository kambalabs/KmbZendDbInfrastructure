<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\GroupParameter;
use KmbDomain\Model\GroupParameterInterface;
use KmbZendDbInfrastructure\Service\GroupParameterRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class GroupParameterRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

    /** @var \PDO */
    protected static $connection;

    /** @var GroupParameterRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('GroupParameterRepository');

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
    public function canGetById()
    {
        /** @var GroupParameterInterface $groupParameter */
        $groupParameter = static::$repository->getById(1);

        $this->assertInstanceOf('KmbDomain\Model\GroupParameterInterface', $groupParameter);
        $this->assertEquals('nameserver', $groupParameter->getName());
        $this->assertEquals(['ns1.local', 'ns2.local'], $groupParameter->getValues());
    }

    /** @test */
    public function canAddWithValues()
    {
        $class = Bootstrap::getServiceManager()->get('GroupClassRepository')->getById(4);
        $groupParameter = new GroupParameter();
        $groupParameter->setName('ports');
        $groupParameter->setClass($class);
        $groupParameter->setValues(['80', '443']);

        static::$repository->add($groupParameter);

        $id = $groupParameter->getId();
        $this->assertNotNull($id);
        $this->assertEquals('ports', static::$connection->query("SELECT name FROM group_parameters WHERE id = $id")->fetchColumn(0));
        $this->assertEquals(2, static::$connection->query("SELECT count(*) FROM group_values WHERE group_parameter_id = $id")->fetchColumn(0));
        $this->assertEquals('80', static::$connection->query("SELECT value FROM group_values WHERE group_parameter_id = $id LIMIT 1")->fetchColumn(0));
    }

    /** @test */
    public function canAddWithChildren()
    {
        /** @var GroupParameterInterface $parent */
        $parent = static::$repository->getById(11);
        $groupParameter = new GroupParameter();
        $groupParameter->setName('host3.local');
        $groupParameter->setParent($parent);
        $groupParameter->setClass($parent->getClass());
        $child1 = new GroupParameter();
        $child1->setName('DocumentRoot');
        $child2 = new GroupParameter();
        $child2->setName('ServerAdmin');
        $groupParameter->setChildren([$child1, $child2]);

        static::$repository->add($groupParameter);

        $id = $groupParameter->getId();
        $this->assertNotNull($id);
        $this->assertEquals('host3.local', static::$connection->query("SELECT name FROM group_parameters WHERE id = $id")->fetchColumn(0));
        $this->assertEquals(2, static::$connection->query("SELECT count(*) FROM group_parameters WHERE parent_id = $id")->fetchColumn(0));
        $this->assertEquals('DocumentRoot', static::$connection->query("SELECT name FROM group_parameters WHERE parent_id = $id LIMIT 1")->fetchColumn(0));
    }

    /** @test */
    public function canUpdateValues()
    {
        /** @var GroupParameterInterface $groupParameter */
        $groupParameter = static::$repository->getById(17);
        $groupParameter->setValues(['80', '8080', '9090']);

        static::$repository->update($groupParameter);

        $this->assertEquals(3, static::$connection->query("SELECT count(*) FROM group_values WHERE group_parameter_id = 17")->fetchColumn(0));
        $this->assertEquals(['80', '8080', '9090'], static::$connection->query("SELECT value FROM group_values WHERE group_parameter_id = 17")->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** @test */
    public function canGetAllByClass()
    {
        $groupClass = Bootstrap::getServiceManager()->get('GroupClassRepository')->getById(4);

        $groupParameters = static::$repository->getAllByClass($groupClass);

        $this->assertEquals(1, count($groupParameters));
        $groupParameter = $groupParameters[0];
        $this->assertInstanceOf('KmbDomain\Model\GroupParameterInterface', $groupParameter);
        $this->assertEquals(3, $groupParameter->getId());
    }

    /** @test */
    public function canGetAllByParent()
    {
        /** @var GroupParameterInterface $parent */
        $parent = static::$repository->getById(4);

        $groupParameters = static::$repository->getAllByParent($parent);

        $this->assertEquals(2, count($groupParameters));
        $groupParameter = $groupParameters[0];
        $this->assertInstanceOf('KmbDomain\Model\GroupParameterInterface', $groupParameter);
        $this->assertEquals(5, $groupParameter->getId());
    }

    /** @test */
    public function canGetByChild()
    {
        /** @var GroupParameterInterface $child */
        $child = static::$repository->getById(7);

        $groupParameter = static::$repository->getByChild($child);

        $this->assertInstanceOf('KmbDomain\Model\GroupParameterInterface', $groupParameter);
        $this->assertEquals(3, $groupParameter->getId());
    }
}
