<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\Parameter;
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
//        $parameter = Bootstrap::getServiceManager()->get('ParameterRepository')->getById(3);
        $parameter = new Parameter();
        $parameter->setId(3);

        $class = static::$repository->getByParameter($parameter);

        $this->assertInstanceOf('KmbDomain\Model\PuppetClassInterface', $class);
        $this->assertEquals(4, $class->getId());
    }
}
