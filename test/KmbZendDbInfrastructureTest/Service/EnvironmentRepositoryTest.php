<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\Revision;
use KmbDomain\Model\User;
use KmbDomain\Model\UserRepositoryInterface;
use KmbZendDbInfrastructure\Service\EnvironmentRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class EnvironmentRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

    /** @var \PDO */
    protected static $connection;

    /** @var EnvironmentRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('EnvironmentRepository');

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
        /** @var EnvironmentInterface $parent */
        $parent = static::$repository->getById(4);
        $environment = new Environment();
        $environment->setCurrentRevision(new Revision());
        $environment->setLastReleasedRevision(new Revision());
        $environment->setName('BETA');
        $environment->setParent($parent);

        static::$repository->add($environment);

        $this->assertEquals(19, intval(static::$connection->query('SELECT count(*) FROM environments')->fetchColumn()));
        $this->assertEquals(
            [
                [19, 19, 0],
                [4, 19, 1],
                [1, 19, 2],
            ],
            static::$connection->query('SELECT * FROM environments_paths WHERE descendant_id = 19 ORDER BY length')->fetchAll(\PDO::FETCH_NUM)
        );
        $this->assertEquals(39, intval(static::$connection->query('SELECT count(*) FROM revisions')->fetchColumn()));
    }

    /** @test */
    public function canAddRoot()
    {
        $environment = new Environment();
        $environment->setName('TESTING');

        static::$repository->add($environment);

        $this->assertEquals(19, intval(static::$connection->query('SELECT count(*) FROM environments')->fetchColumn()));
        $this->assertEquals(45, intval(static::$connection->query('SELECT count(*) FROM environments_paths')->fetchColumn()));
        $this->assertEquals(
            [[20, 20, 0]],
            static::$connection->query('SELECT * FROM environments_paths WHERE descendant_id = 20 ORDER BY length')->fetchAll(\PDO::FETCH_NUM)
        );
    }

    /** @test */
    public function canUpdate()
    {
        /** @var UserRepositoryInterface $userRepository */
        $userRepository = Bootstrap::getServiceManager()->get('UserRepository');
        $mike = $userRepository->getById(4);
        $nick = $userRepository->getById(6);
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = static::$repository->getById(4);
        /** @var EnvironmentInterface $newParent */
        $newParent = static::$repository->getById(2);
        $aggregateRoot->setParent($newParent);
        $aggregateRoot->setName('PF4');
        $aggregateRoot->addUsers([$mike, $nick]);
        $aggregateRoot->setDefault(true);

        static::$repository->update($aggregateRoot);

        $this->assertEquals('PF4', static::$connection->query('SELECT name FROM environments WHERE id = 4')->fetchColumn());
        $this->assertEquals('1', static::$connection->query('SELECT isdefault FROM environments WHERE id = 4')->fetchColumn());
        $this->assertEquals('0', static::$connection->query('SELECT isdefault FROM environments WHERE id = 3')->fetchColumn());
        $this->assertEquals('UNSTABLE', static::$connection->query('select name from environments join environments_paths on id = ancestor_id where length = 1 and descendant_id = 4')->fetchColumn());
        $this->assertEquals('PF4', static::$connection->query('select name from environments join environments_paths on id = ancestor_id where length = 1 and descendant_id = 7')->fetchColumn());
        $this->assertEquals([[3], [4], [6]], static::$connection->query('SELECT user_id FROM environments_users WHERE environment_id = 4')->fetchAll(\PDO::FETCH_NUM));
    }

    /** @test */
    public function canUpdateWithoutResetDefault()
    {
        /** @var EnvironmentInterface $aggregateRoot */
        $aggregateRoot = static::$repository->getById(4);

        static::$repository->update($aggregateRoot);

        $this->assertEquals('1', static::$connection->query('SELECT isdefault FROM environments WHERE id = 3')->fetchColumn());
    }

    /** @test */
    public function canRemove()
    {
        $aggregateRoot = static::$repository->getById(4);

        static::$repository->remove($aggregateRoot);

        $this->assertEquals(17, intval(static::$connection->query('SELECT count(*) FROM environments')->fetchColumn()));
        $this->assertEquals(25, intval(static::$connection->query('SELECT count(*) FROM environments_paths')->fetchColumn()));
    }

    /** @test */
    public function canGetAllRoots()
    {
        $environments = static::$repository->getAllRoots();

        $this->assertEquals(3, count($environments));
        /** @var EnvironmentInterface $firstEnvironment */
        $firstEnvironment = $environments[0];
        $this->assertInstanceOf('KmbDomain\Model\EnvironmentInterface', $firstEnvironment);
        $this->assertEquals('STABLE', $firstEnvironment->getName());
    }

    /** @test */
    public function canGetDefault()
    {
        $defaultEnvironment = static::$repository->getDefault();

        $this->assertInstanceOf('KmbDomain\Model\EnvironmentInterface', $defaultEnvironment);
        $this->assertEquals('DEFAULT', $defaultEnvironment->getName());
    }

    /** @test */
    public function cannotGetRootByUnknownRootName()
    {
        $environment = static::$repository->getRootByName('PF1');

        $this->assertNull($environment);
    }

    /** @test */
    public function canGetRootByName()
    {
        $environment = static::$repository->getRootByName('DEFAULT');

        $this->assertEquals(3, $environment->getId());
        $this->assertTrue($environment->isDefault());
    }

    /** @test */
    public function canGetAllChildren()
    {
        $environment = static::$repository->getById(1);

        $children = static::$repository->getAllChildren($environment);

        $this->assertEquals(3, count($children));
        /** @var EnvironmentInterface $firstChild */
        $firstChild = $children[0];
        $this->assertInstanceOf('KmbDomain\Model\EnvironmentInterface', $firstChild);
        $this->assertEquals('PF1', $firstChild->getName());
    }

    /** @test */
    public function canGetParent()
    {
        $environment = static::$repository->getById(4);

        $parent = static::$repository->getParent($environment);

        $this->assertInstanceOf('KmbDomain\Model\EnvironmentInterface', $parent);
        $this->assertEquals(1, $parent->getId());
    }

    /** @test */
    public function canGetAllForUser()
    {
        $user = new User();
        $user->setId(3);

        $environments = static::$repository->getAllForUser($user);

        $this->assertEquals(4, count($environments));
        /** @var EnvironmentInterface $firstEnvironment */
        $firstEnvironment = $environments[0];
        $this->assertInstanceOf('KmbDomain\Model\EnvironmentInterface', $firstEnvironment);
        $this->assertEquals('PF1', $firstEnvironment->getName());
    }
}
