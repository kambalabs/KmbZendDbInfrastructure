<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbDomain\Model\GroupInterface;
use KmbZendDbInfrastructure\Service\GroupRepository;
use KmbZendDbInfrastructureTest\Bootstrap;
use KmbZendDbInfrastructureTest\DatabaseInitTrait;
use Zend\Db\Adapter\AdapterInterface;

class GroupRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseInitTrait;

    /** @var \PDO */
    protected static $connection;

    /** @var GroupRepository */
    protected static $repository;

    public static function setUpBeforeClass()
    {
        $serviceManager = Bootstrap::getServiceManager();
        static::$repository = $serviceManager->get('GroupRepository');

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
        /** @var GroupInterface $group */
        $group = static::$repository->getById(6);

        $this->assertInstanceOf('KmbDomain\Model\GroupInterface', $group);
        $this->assertEquals(6, $group->getId());
        $this->assertEquals(9, $group->getRevision()->getId());
        $this->assertEquals(4, $group->getEnvironment()->getId());
        $this->assertEquals(4, $group->getRevision()->getEnvironment()->getId());
    }

    /** @test */
    public function canGetAllByRevision()
    {
        $revision = Bootstrap::getServiceManager()->get('RevisionRepository')->getById(8);

        $groups = static::$repository->getAllByRevision($revision);

        $this->assertEquals(3, count($groups));
        $secondGroup = $groups[1];
        $this->assertInstanceOf('KmbDomain\Model\GroupInterface', $secondGroup);
        $this->assertEquals(3, $secondGroup->getId());
    }

    /** @test */
    public function canGetFirstByRevision()
    {
        $revision = Bootstrap::getServiceManager()->get('RevisionRepository')->getById(9);

        $group = static::$repository->getFirstByRevision($revision);

        $this->assertEquals(4, $group->getId());
    }

    /** @test */
    public function cannotGetUnknownByNameAndRevision()
    {
        $revision = Bootstrap::getServiceManager()->get('RevisionRepository')->getById(8);

        $group = static::$repository->getByNameAndRevision('sql', $revision);

        $this->assertNull($group);
    }

    /** @test */
    public function canGetByNameAndRevision()
    {
        $revision = Bootstrap::getServiceManager()->get('RevisionRepository')->getById(9);

        $group = static::$repository->getByNameAndRevision('sql', $revision);

        $this->assertEquals(7, $group->getId());
    }
}
