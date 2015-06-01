<?php
namespace KmbZendDbInfrastructureTest\Hydrator;

use KmbDomain\Model\Log;
use KmbZendDbInfrastructure\Hydrator\LogHydrator;

class LogHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canExtract()
    {
        $hydrator = new LogHydrator();
        $log = new Log(new \DateTime('2015-05-27 16:45:32'), 'John DOE', 'Fake log');
        $log->setId(1);

        $this->assertEquals([
            'id' => 1,
            'created_at' => '2015-05-27 16:45:32',
            'created_by' => 'John DOE',
            'comment' => 'Fake log'
        ], $hydrator->extract($log));
    }

    /** @test */
    public function canExtractWithoutId()
    {
        $hydrator = new LogHydrator();
        $log = new Log(new \DateTime('2015-05-27 16:45:32'), 'John DOE', 'Fake log');

        $this->assertEquals([
            'created_at' => '2015-05-27 16:45:32',
            'created_by' => 'John DOE',
            'comment' => 'Fake log'
        ], $hydrator->extract($log));
    }

    /** @test */
    public function canHydrate()
    {
        $hydrator = new LogHydrator();
        $log = new Log();

        $log = $hydrator->hydrate([
            'id' => 1,
            'created_at' => '2015-05-27 16:45:32',
            'created_by' => 'John DOE',
            'comment' => 'Fake log'
        ], $log);

        $this->assertEquals(1, $log->getId());
        $this->assertEquals(new \DateTime('2015-05-27 16:45:32'), $log->getCreatedAt());
        $this->assertEquals('John DOE', $log->getCreatedBy());
        $this->assertEquals('Fake log', $log->getComment());
    }
}
