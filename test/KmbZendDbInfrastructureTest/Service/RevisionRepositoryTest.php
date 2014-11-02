<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\Group;
use KmbDomain\Model\Revision;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\RevisionLog;
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
    public function canAdd()
    {
        $environment = Bootstrap::getServiceManager()->get('EnvironmentRepository')->getById(4);
        $revision = new Revision($environment);
        $revision->setGroups([new Group('default')]);

        static::$repository->add($revision);

        $this->assertEquals(38, $revision->getId());
        $this->assertEquals(8, $revision->getGroupByName('default')->getId());
    }

    /** @test */
    public function canUpdate()
    {
        /** @var RevisionInterface $revision */
        $revision = static::$repository->getById(8);
        $revision->addLog(new RevisionLog(new \DateTime('2014-11-02 01:51:28'), 'John DOE', 'Add group fake'));

        static::$repository->update($revision);

        $this->assertEquals(4, intval(static::$connection->query('SELECT count(*) FROM revisions_logs WHERE revision_id = 8')->fetchColumn()));
    }

    /** @test */
    public function canGetById()
    {
        /** @var RevisionInterface $revision */
        $revision = static::$repository->getById(1);

        $this->assertInstanceOf('KmbDomain\Model\RevisionInterface', $revision);
        $this->assertEquals(1, $revision->getId());
        $this->assertInstanceOf('KmbDomain\Model\EnvironmentInterface', $revision->getEnvironment());
    }

    /** @test */
    public function canGetByIdWithLogs()
    {
        /** @var RevisionInterface $revision */
        $revision = static::$repository->getById(8);

        $this->assertInstanceOf('KmbDomain\Model\RevisionInterface', $revision);
        $logs = $revision->getLogs();
        $this->assertEquals(3, count($logs));
        $this->assertEquals(3, $logs[0]->getId());
        $this->assertEquals('Update group default', $logs[0]->getComment());
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
        $this->assertEquals($environment, $revision->getEnvironment());
    }

    /** @test */
    public function canGetLastReleasedByEnvironment()
    {
        $environment = Bootstrap::getServiceManager()->get('EnvironmentRepository')->getById(4);

        $revision = static::$repository->getLastReleasedByEnvironment($environment);

        $this->assertInstanceOf('KmbDomain\Model\RevisionInterface', $revision);
        $this->assertEquals('Add first group', $revision->getComment());
    }

    /** @test */
    public function canGetByGroup()
    {
        $group = Bootstrap::getServiceManager()->get('GroupRepository')->getById(1);

        $revision = static::$repository->getByGroup($group);

        $this->assertInstanceOf('KmbDomain\Model\RevisionInterface', $revision);
        $this->assertEquals('Add first group', $revision->getComment());
    }
}
