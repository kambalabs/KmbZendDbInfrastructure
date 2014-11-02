<?php
namespace KmbZendDbInfrastructureTest\Hydrator;

use KmbDomain\Model\Revision;
use KmbDomain\Model\RevisionLog;
use KmbZendDbInfrastructure\Hydrator\RevisionLogHydrator;

class RevisionLogHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $hydrator = new RevisionLogHydrator();
        $revision = new Revision();
        $revision->setId(1);
        $revisionLog = new RevisionLog(new \DateTime('2014-09-10 10:38:15'), 'John DOE', 'Add group default');
        $revisionLog->setId(1);
        $revisionLog->setRevision($revision);

        $this->assertEquals([
            'id' => 1,
            'revision_id' => 1,
            'created_at' => '2014-09-10 10:38:15',
            'created_by' => 'John DOE',
            'comment' => 'Add group default',
        ], $hydrator->extract($revisionLog));
    }

    /** @test */
    public function canHydrate()
    {
        $hydrator = new RevisionLogHydrator();
        $revisionLog = new RevisionLog();

        $hydratedRevisionLog = $hydrator->hydrate([
            'id' => 1,
            'created_at' => '2014-09-10 10:38:15',
            'created_by' => 'John DOE',
            'comment' => 'Add group default',
        ], $revisionLog);

        $this->assertEquals(1, $hydratedRevisionLog->getId());
        $this->assertEquals(new \DateTime('2014-09-10 10:38:15'), $hydratedRevisionLog->getCreatedAt());
        $this->assertEquals('John DOE', $hydratedRevisionLog->getCreatedBy());
        $this->assertEquals('Add group default', $hydratedRevisionLog->getComment());
    }

    /** @test */
    public function canHydrateWithTablePrefix()
    {
        $hydrator = new RevisionLogHydrator();
        $revisionLog = new RevisionLog();

        $hydratedRevision = $hydrator->hydrate([
            'rl.id' => 1,
            'rl.created_at' => '2014-09-10 10:38:15',
            'rl.created_by' => 'John DOE',
            'rl.comment' => 'Add group default',
        ], $revisionLog);

        $this->assertEquals(1, $hydratedRevision->getId());
        $this->assertEquals(new \DateTime('2014-09-10 10:38:15'), $hydratedRevision->getCreatedAt());
        $this->assertEquals('John DOE', $hydratedRevision->getCreatedBy());
        $this->assertEquals('Add group default', $hydratedRevision->getComment());
    }
}
