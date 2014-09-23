<?php
namespace KmbZendDbInfrastructureTest\Service;

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
