<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\RevisionRepository;
use KmbZendDbInfrastructure\Service\RevisionRepositoryFactory;
use KmbZendDbInfrastructureTest\Bootstrap;

class RevisionRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new RevisionRepositoryFactory();
        $factory->setConfig([
            'aggregate_root_class' => 'KmbDomain\Model\Revision',
            'aggregate_root_proxy_factory' => 'KmbZendDbInfrastructure\Service\RevisionProxyFactory',
            'aggregate_root_hydrator_class' => 'KmbZendDbInfrastructure\Hydrator\RevisionHydrator',
            'table_name' => 'revisions',
            'table_sequence_name' => 'revision_id_seq',
            'environment_class' => 'KmbDomain\Model\Environment',
            'environment_proxy_factory' => 'KmbZendDbInfrastructure\Service\EnvironmentProxyFactory',
            'environment_hydrator_class' => 'KmbZendDbInfrastructure\Hydrator\EnvironmentHydrator',
            'environment_table_name' => 'environments',
            'factory' => 'KmbZendDbInfrastructure\Service\RevisionRepositoryFactory',
            'repository_class' => 'KmbZendDbInfrastructure\Service\RevisionRepository',
        ]);

        /** @var RevisionRepository $service */
        $service = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\RevisionRepository', $service);
        $this->assertEquals('environments', $service->getEnvironmentTableName());
        $this->assertEquals('KmbDomain\Model\Environment', $service->getEnvironmentClass());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\EnvironmentProxyFactory', $service->getEnvironmentProxyFactory());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Hydrator\EnvironmentHydrator', $service->getEnvironmentHydrator());
    }
}
