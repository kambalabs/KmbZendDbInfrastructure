<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\UserRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use Zend\Db\Adapter\AdapterInterface;

class UserRepositoryTest extends \PHPUnit_Extensions_Database_TestCase
{
    /** @var \PDO */
    protected static $connection;

    /** @var UserRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('UserRepository');

        /** @var $dbAdapter AdapterInterface */
        $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
        static::$connection = $dbAdapter->getDriver()->getConnection()->getResource();

        static::$connection->exec(file_get_contents(Bootstrap::rootPath() . '/data/migrations/sqlite/schema.sql'));
    }

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection(static::$connection);
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(Bootstrap::rootPath() . '/test/data/fixtures.xml');
    }

    /** @test */
    public function canGetByLogin()
    {
        $user = static::$repository->getByLogin('jdoe');

        $this->assertInstanceOf('KmbDomain\Model\UserInterface', $user);
        $this->assertEquals(1, $user->getId());
    }

    /** @test */
    public function canGetAllByEnvironment()
    {
        $environment = Bootstrap::getServiceManager()->get('EnvironmentRepository')->getById(4);

        $users = static::$repository->getAllByEnvironment($environment);

        $this->assertEquals(2, count($users));
        $user = $users[0];
        $this->assertInstanceOf('KmbDomain\Model\UserInterface', $user);
        $this->assertEquals('psmith', $user->getLogin());
    }
}
