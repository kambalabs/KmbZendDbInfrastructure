<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbBase\FakeDateTimeFactory;
use KmbDomain\Model\RevisionInterface;
use KmbDomain\Model\User;
use KmbZendDbInfrastructure\Service\RevisionRepository;
use KmbZendDbInfrastructure\Service\RevisionService;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class RevisionServiceTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

    /** @var \PDO */
    protected static $connection;

    /** @var RevisionRepository */
    protected static $repository;

    /** @var  RevisionService */
    protected $revisionService;

    /** @var  \DateTime */
    protected $fakeDateTime;

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
        $this->revisionService = new RevisionService();
        $this->revisionService->setRevisionRepository(static::$repository);
        $this->fakeDateTime = new \DateTime('2014-10-04 19:04:00');
        $this->revisionService->setDateTimeFactory(new FakeDateTimeFactory($this->fakeDateTime));
    }

    /** @test */
    public function canReleaseCurrent()
    {
        /** @var RevisionInterface $revision */
        $revision = static::$repository->getById(9);

        $this->revisionService->release($revision, new User('jdoe', 'John DOE'), 'Test release');

        $this->assertEquals([['2014-10-04 19:04:00', 'John DOE', 'Test release']], static::$connection->query('SELECT released_at, released_by, comment FROM revisions WHERE id = 9')->fetchAll(\PDO::FETCH_NUM));
        $this->assertEquals([[38, 4, null, null, null]], static::$connection->query('SELECT * FROM revisions WHERE id = 38')->fetchAll(\PDO::FETCH_NUM));
    }

    /** @test */
    public function canReleaseAlreadyReleasedRevision()
    {
        /** @var RevisionInterface $revision */
        $revision = static::$repository->getById(8);

        $this->revisionService->release($revision, new User('jdoe', 'Jane DOE'), 'Test release');

        $this->assertEmpty(static::$connection->query('SELECT * FROM revisions WHERE id = 9')->fetchAll(\PDO::FETCH_NUM));
        $this->assertEquals([['2014-10-04 19:04:00', 'Jane DOE', 'Test release']], static::$connection->query('SELECT released_at, released_by, comment FROM revisions WHERE id = 39')->fetchAll(\PDO::FETCH_NUM));
        $this->assertEquals([[40, 4, null, null, null]], static::$connection->query('SELECT * FROM revisions WHERE id = 40')->fetchAll(\PDO::FETCH_NUM));
    }

    /** @test */
    public function canRemove()
    {
        /** @var RevisionInterface $revision */
        $revision = static::$repository->getById(1);

        $this->revisionService->remove($revision);

        $this->assertEmpty(static::$connection->query('SELECT * FROM revisions WHERE id = 1')->fetchAll(\PDO::FETCH_NUM));
    }

    /** @test */
    public function canRemoveCurrent()
    {
        /** @var RevisionInterface $revision */
        $revision = static::$repository->getById(9);

        $this->revisionService->remove($revision);

        $this->assertEmpty(static::$connection->query('SELECT * FROM revisions WHERE id = 9')->fetchAll(\PDO::FETCH_NUM));
        $this->assertEquals([[41, 4, null, null, null]], static::$connection->query('SELECT * FROM revisions WHERE id = 41')->fetchAll(\PDO::FETCH_NUM));
        $this->assertEquals(3, static::$connection->query('SELECT COUNT(*) FROM groups WHERE revision_id = 41')->fetchColumn());
    }
}
