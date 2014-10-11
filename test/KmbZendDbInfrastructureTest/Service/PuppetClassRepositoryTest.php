<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\Parameter;
use KmbDomain\Model\PuppetClass;
use KmbDomain\Model\Value;
use KmbZendDbInfrastructure\Service\PuppetClassRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class PuppetClassRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

    /** @var \PDO */
    protected static $connection;

    /** @var PuppetClassRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('PuppetClassRepository');

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
    public function canGetByParameter()
    {
        $parameter = Bootstrap::getServiceManager()->get('ParameterRepository')->getById(3);

        $class = static::$repository->getByParameter($parameter);

        $this->assertInstanceOf('KmbDomain\Model\PuppetClassInterface', $class);
        $this->assertEquals(4, $class->getId());
    }

    /** @test */
    public function canAdd()
    {
        $parameter = new Parameter();
        $parameter->setName('port');
        $parameter->setValues([new Value('80')]);
        $class = new PuppetClass();
        $class->setName('varnish');
        $class->setParameters([$parameter]);
        $class->setGroup(Bootstrap::getServiceManager()->get('GroupRepository')->getById(1));

        static::$repository->add($class);

        $id = $class->getId();
        $this->assertNotNull($id);
        $this->assertEquals(
            [['port', '80']],
            static::$connection->query("SELECT p.name, v.name FROM parameters p LEFT JOIN 'values' v ON v.parameter_id = p.id WHERE p.puppet_class_id = $id")->fetchAll(\PDO::FETCH_NUM)
        );
    }
}
