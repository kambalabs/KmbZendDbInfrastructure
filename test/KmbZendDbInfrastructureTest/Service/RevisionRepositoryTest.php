<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\RevisionRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class RevisionRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

    /** @var \PDO */
    protected static $connection;

    /** @var RevisionRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('RevisionRepository');

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
    public function canGetAllReleasedByEnvironment()
    {
        $environment = Bootstrap::getServiceManager()->get('EnvironmentRepository')->getById(4);

        $revisions = static::$repository->getAllReleasedByEnvironment($environment);

        $this->assertEquals(2, count($revisions));
        $mostRecentRevision = $revisions[0];
        $this->assertInstanceOf('KmbDomain\Model\RevisionInterface', $mostRecentRevision);
        $this->assertEquals('Add first group', $mostRecentRevision->getComment());
    }

    /** @test */
    public function canGetCurrentByEnvironment()
    {
        $environment = Bootstrap::getServiceManager()->get('EnvironmentRepository')->getById(4);

        $revision = static::$repository->getCurrentByEnvironment($environment);

        $this->assertInstanceOf('KmbDomain\Model\RevisionInterface', $revision);
        $this->assertEquals(9, $revision->getId());
    }

    /** @test */
    public function canGetLastReleasedByEnvironment()
    {
        $environment = Bootstrap::getServiceManager()->get('EnvironmentRepository')->getById(4);

        $revision = static::$repository->getLastReleasedByEnvironment($environment);

        $this->assertInstanceOf('KmbDomain\Model\RevisionInterface', $revision);
        $this->assertEquals('Add first group', $revision->getComment());
    }
}
