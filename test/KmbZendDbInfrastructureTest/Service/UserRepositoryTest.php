<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\UserInterface;
use KmbZendDbInfrastructure\Service\UserRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class UserRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

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

        static::initSchema(static::$connection);
    }

    protected function setUp()
    {
        static::initFixtures(static::$connection);
    }

    /** @test */
    public function canGetByLogin()
    {
        $user = static::$repository->getByLogin('jdoe');

        $this->assertInstanceOf('KmbDomain\Model\UserInterface', $user);
        $this->assertEquals(1, $user->getId());
    }

    /** @test */
    public function canGetAllNonRoot()
    {
        $users = static::$repository->getAllNonRoot();

        $this->assertEquals(5, count($users));
        /** @var UserInterface $user */
        $user = $users[0];
        $this->assertInstanceOf('KmbDomain\Model\UserInterface', $user);
        $this->assertEquals('psmith', $user->getLogin());
    }

    /** @test */
    public function canGetAllByEnvironment()
    {
        $environment = Bootstrap::getServiceManager()->get('EnvironmentRepository')->getById(4);

        $users = static::$repository->getAllByEnvironment($environment);

        $this->assertEquals(2, count($users));
        /** @var UserInterface $user */
        $user = $users[0];
        $this->assertInstanceOf('KmbDomain\Model\UserInterface', $user);
        $this->assertEquals('psmith', $user->getLogin());
    }

    /** @test */
    public function canGetAllAvailableForEnvironment()
    {
        $environment = Bootstrap::getServiceManager()->get('EnvironmentRepository')->getById(4);

        $users = static::$repository->getAllAvailableForEnvironment($environment);

        $this->assertEquals(3, count($users));
        /** @var UserInterface $user */
        $user = $users[0];
        $this->assertInstanceOf('KmbDomain\Model\UserInterface', $user);
        $this->assertEquals('madams', $user->getLogin());
    }
}
