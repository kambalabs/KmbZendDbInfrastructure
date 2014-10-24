<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\GroupParameter;
use KmbDomain\Model\GroupClass;
use KmbZendDbInfrastructure\Service\GroupClassRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class GroupClassRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

    /** @var \PDO */
    protected static $connection;

    /** @var GroupClassRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('GroupClassRepository');

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
        $groupParameter = Bootstrap::getServiceManager()->get('GroupParameterRepository')->getById(3);

        $class = static::$repository->getByParameter($groupParameter);

        $this->assertInstanceOf('KmbDomain\Model\GroupClassInterface', $class);
        $this->assertEquals(4, $class->getId());
    }

    /** @test */
    public function canAdd()
    {
        $groupParameter = new GroupParameter();
        $groupParameter->setName('port');
        $groupParameter->setValues(['80']);
        $groupClass = new GroupClass();
        $groupClass->setName('varnish');
        $groupClass->setParameters([$groupParameter]);
        $groupClass->setGroup(Bootstrap::getServiceManager()->get('GroupRepository')->getById(1));

        static::$repository->add($groupClass);

        $id = $groupClass->getId();
        $this->assertNotNull($id);
        $this->assertEquals(
            [['port', '80']],
            static::$connection->query("SELECT p.name, v.value FROM group_parameters p LEFT JOIN group_values v ON v.group_parameter_id = p.id WHERE p.group_class_id = $id")->fetchAll(\PDO::FETCH_NUM)
        );
    }
}
