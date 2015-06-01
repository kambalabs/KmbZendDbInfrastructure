<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\LogInterface;
use KmbZendDbInfrastructure\Service\LogRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class LogRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

    /** @var \PDO */
    protected static $connection;

    /** @var LogRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('LogRepository');

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
    public function canGetById()
    {
        /** @var LogInterface $log */
        $log = static::$repository->getById(1);

        $this->assertInstanceOf('KmbDomain\Model\LogInterface', $log);
        $this->assertEquals(1, $log->getId());
        $this->assertEquals(new \DateTime('2014-09-10 10:02:41'), $log->getCreatedAt());
        $this->assertEquals('Paul MATTHEWS', $log->getCreatedBy());
        $this->assertEquals('Add module apache on STABLE_PF1_ITG', $log->getComment());
    }

    /** @test */
    public function canGetAllPaginated()
    {
        /** @var LogInterface[] $logs */
        list($logs, $filteredCount) = static::$repository->getAllPaginated('Add module', 1, 2, [['column' => 'comment', 'dir' => 'ASC']]);

        $this->assertEquals(4, $filteredCount);
        $this->assertEquals(2, count($logs));
        $firstLog = $logs[0];
        $this->assertInstanceOf('KmbDomain\Model\LogInterface', $firstLog);
        $this->assertEquals(5, $firstLog->getId());
    }
}
