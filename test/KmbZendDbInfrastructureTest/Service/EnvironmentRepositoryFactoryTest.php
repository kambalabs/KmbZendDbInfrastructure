<?php
namespace KmbZendDbInfrastructureTest\Service;

use KmbZendDbInfrastructure\Service\EnvironmentRepository;
use KmbZendDbInfrastructure\Service\EnvironmentRepositoryFactory;
use KmbZendDbInfrastructureTest\Bootstrap;

class EnvironmentRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new EnvironmentRepositoryFactory();
        $factory->setConfig([
            'aggregate_root_class' => 'KmbDomain\Model\Environment',
            'aggregate_root_proxy_factory' => 'KmbZendDbInfrastructure\Service\EnvironmentProxyFactory',
            'table_name' => 'environments',
            'paths_table_name' => 'environments_paths',
            'repository_class' => 'KmbZendDbInfrastructure\Service\EnvironmentRepository',
        ]);

        /** @var EnvironmentRepository $service */
        $service = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\EnvironmentRepository', $service);
        $this->assertEquals('environments_paths', $service->getPathsTableName());
    }

    /**
     * @test
     * @expectedException \GtnPersistZendDb\Exception\MissingConfigurationException
     * @expectedExceptionMessage paths_table_name is missing in repository configuration
     */
    public function cannotCreateServiceIfNoPathsTableName()
    {
        $factory = new EnvironmentRepositoryFactory();
        $factory->setConfig([
            'aggregate_root_class' => 'KmbDomain\Model\Environment',
            'aggregate_root_proxy_factory' => 'KmbZendDbInfrastructure\Service\EnvironmentProxyFactory',
            'table_name' => 'environments',
            'repository_class' => 'KmbZendDbInfrastructure\Service\EnvironmentRepository',
        ]);

        $factory->createService(Bootstrap::getServiceManager());
    }
}
