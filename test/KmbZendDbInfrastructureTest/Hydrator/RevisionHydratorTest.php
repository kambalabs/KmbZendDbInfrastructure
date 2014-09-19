<?php
namespace KmbZendDbInfrastructureTest\Hydrator;

use KmbDomain\Model\Environment;
use KmbDomain\Model\Revision;
use KmbZendDbInfrastructure\Hydrator\RevisionHydrator;

class RevisionHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $hydrator = new RevisionHydrator();
        $environment = new Environment();
        $environment->setId(1);
        $revision = new Revision();
        $revision->setId(1);
        $revision->setEnvironment($environment);
        $revision->setUpdatedAt(new \DateTime('2014-09-10 10:32:23'));
        $revision->setUpdatedBy('John DOE');
        $revision->setReleasedAt(new \DateTime('2014-09-10 10:38:15'));
        $revision->setReleasedBy('John DOE');
        $revision->setComment('Init');

        $this->assertEquals([
            'id' => 1,
            'environment_id' => 1,
            'updated_at' => '2014-09-10 10:32:23',
            'updated_by' => 'John DOE',
            'released_at' => '2014-09-10 10:38:15',
            'released_by' => 'John DOE',
            'comment' => 'Init',
        ], $hydrator->extract($revision));
    }

    /** @test */
    public function canExtractNonReleasedRevision()
    {
        $hydrator = new RevisionHydrator();
        $environment = new Environment();
        $environment->setId(1);
        $revision = new Revision();
        $revision->setEnvironment($environment);

        $this->assertEquals([
            'environment_id' => '1',
            'updated_at' => null,
            'updated_by' => null,
            'released_at' => null,
            'released_by' => null,
            'comment' => null,
        ], $hydrator->extract($revision));
    }

    /** @test */
    public function canHydrate()
    {
        $hydrator = new RevisionHydrator();
        $revision = new Revision();

        $hydratedRevision = $hydrator->hydrate([
            'id' => 1,
            'updated_at' => '2014-09-10 10:32:23',
            'updated_by' => 'John DOE',
            'released_at' => '2014-09-10 10:38:15',
            'released_by' => 'John DOE',
            'comment' => 'Init',
        ], $revision);

        $this->assertEquals(1, $hydratedRevision->getId());
        $this->assertEquals(new \DateTime('2014-09-10 10:32:23'), $hydratedRevision->getUpdatedAt());
        $this->assertEquals('John DOE', $hydratedRevision->getUpdatedBy());
        $this->assertEquals(new \DateTime('2014-09-10 10:38:15'), $hydratedRevision->getReleasedAt());
        $this->assertEquals('John DOE', $hydratedRevision->getReleasedBy());
        $this->assertEquals('Init', $hydratedRevision->getComment());
    }

    /** @test */
    public function canHydrateNonReleasedRevision()
    {
        $revision = new Revision();
        $hydrator = new RevisionHydrator();

        $hydratedRevision = $hydrator->hydrate(['id' => 1], $revision);

        $this->assertEquals(1, $hydratedRevision->getId());
        $this->assertNull($hydratedRevision->getUpdatedAt());
        $this->assertNull($hydratedRevision->getUpdatedBy());
        $this->assertNull($hydratedRevision->getReleasedAt());
        $this->assertNull($hydratedRevision->getReleasedBy());
        $this->assertNull($hydratedRevision->getComment());
    }

    /** @test */
    public function canHydrateWithTablePrefix()
    {
        $hydrator = new RevisionHydrator();
        $revision = new Revision();

        $hydratedRevision = $hydrator->hydrate([
            'r.id' => 1,
            'r.updated_at' => '2014-09-10 10:32:23',
            'r.updated_by' => 'John DOE',
            'r.released_at' => '2014-09-10 10:38:15',
            'r.released_by' => 'John DOE',
            'r.comment' => 'Init',
        ], $revision);

        $this->assertEquals(1, $hydratedRevision->getId());
        $this->assertEquals(new \DateTime('2014-09-10 10:32:23'), $hydratedRevision->getUpdatedAt());
        $this->assertEquals('John DOE', $hydratedRevision->getUpdatedBy());
        $this->assertEquals(new \DateTime('2014-09-10 10:38:15'), $hydratedRevision->getReleasedAt());
        $this->assertEquals('John DOE', $hydratedRevision->getReleasedBy());
        $this->assertEquals('Init', $hydratedRevision->getComment());
    }
}
