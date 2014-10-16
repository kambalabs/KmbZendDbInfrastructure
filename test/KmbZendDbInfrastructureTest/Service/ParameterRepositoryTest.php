<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\Parameter;
use KmbDomain\Model\ParameterInterface;
use KmbZendDbInfrastructure\Service\ParameterRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class ParameterRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

    /** @var \PDO */
    protected static $connection;

    /** @var ParameterRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('ParameterRepository');

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
        /** @var ParameterInterface $parameter */
        $parameter = static::$repository->getById(1);

        $this->assertInstanceOf('KmbDomain\Model\ParameterInterface', $parameter);
        $this->assertEquals('nameserver', $parameter->getName());
        $this->assertEquals(['ns1.local', 'ns2.local'], $parameter->getValues());
    }

    /** @test */
    public function canAddWithValues()
    {
        $class = Bootstrap::getServiceManager()->get('PuppetClassRepository')->getById(4);
        $parameter = new Parameter();
        $parameter->setName('ports');
        $parameter->setClass($class);
        $parameter->setValues(['80', '443']);

        static::$repository->add($parameter);

        $id = $parameter->getId();
        $this->assertNotNull($id);
        $this->assertEquals('ports', static::$connection->query("SELECT name FROM parameters WHERE id = $id")->fetchColumn(0));
        $this->assertEquals(2, static::$connection->query("SELECT count(*) FROM 'values' WHERE parameter_id = $id")->fetchColumn(0));
        $this->assertEquals('80', static::$connection->query("SELECT name FROM 'values' WHERE parameter_id = $id LIMIT 1")->fetchColumn(0));
    }

    /** @test */
    public function canAddWithChildren()
    {
        /** @var ParameterInterface $parent */
        $parent = static::$repository->getById(11);
        $parameter = new Parameter();
        $parameter->setName('host3.local');
        $parameter->setParent($parent);
        $parameter->setClass($parent->getClass());
        $child1 = new Parameter();
        $child1->setName('DocumentRoot');
        $child2 = new Parameter();
        $child2->setName('ServerAdmin');
        $parameter->setChildren([$child1, $child2]);

        static::$repository->add($parameter);

        $id = $parameter->getId();
        $this->assertNotNull($id);
        $this->assertEquals('host3.local', static::$connection->query("SELECT name FROM parameters WHERE id = $id")->fetchColumn(0));
        $this->assertEquals(2, static::$connection->query("SELECT count(*) FROM parameters WHERE parent_id = $id")->fetchColumn(0));
        $this->assertEquals('DocumentRoot', static::$connection->query("SELECT name FROM parameters WHERE parent_id = $id LIMIT 1")->fetchColumn(0));
    }

    /** @test */
    public function canUpdateValues()
    {
        /** @var ParameterInterface $parameter */
        $parameter = static::$repository->getById(17);
        $parameter->setValues(['80', '8080', '9090']);

        static::$repository->update($parameter);

        $this->assertEquals(3, static::$connection->query("SELECT count(*) FROM 'values' WHERE parameter_id = 17")->fetchColumn(0));
        $this->assertEquals(['80', '8080', '9090'], static::$connection->query("SELECT name FROM 'values' WHERE parameter_id = 17")->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** @test */
    public function canGetAllByClass()
    {
        $class = Bootstrap::getServiceManager()->get('PuppetClassRepository')->getById(4);

        $parameters = static::$repository->getAllByClass($class);

        $this->assertEquals(1, count($parameters));
        $parameter = $parameters[0];
        $this->assertInstanceOf('KmbDomain\Model\ParameterInterface', $parameter);
        $this->assertEquals(3, $parameter->getId());
    }

    /** @test */
    public function canGetAllByParent()
    {
        /** @var ParameterInterface $parent */
        $parent = static::$repository->getById(4);

        $parameters = static::$repository->getAllByParent($parent);

        $this->assertEquals(2, count($parameters));
        $parameter = $parameters[0];
        $this->assertInstanceOf('KmbDomain\Model\ParameterInterface', $parameter);
        $this->assertEquals(5, $parameter->getId());
    }

    /** @test */
    public function canGetByChild()
    {
        /** @var ParameterInterface $child */
        $child = static::$repository->getById(7);

        $parameter = static::$repository->getByChild($child);

        $this->assertInstanceOf('KmbDomain\Model\ParameterInterface', $parameter);
        $this->assertEquals(3, $parameter->getId());
    }
}
