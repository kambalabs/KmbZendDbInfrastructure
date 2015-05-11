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
        /** @var EnvironmentRepository $service */
        $service = Bootstrap::getServiceManager()->get('EnvironmentRepository');

        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\EnvironmentRepository', $service);
        $this->assertEquals('environments_paths', $service->getPathsTableName());
        $this->assertEquals('auto_updated_modules', $service->getAutoUpdatedModulesTableName());
        $this->assertInstanceOf('KmbZendDbInfrastructure\Service\RevisionRepository', $service->getRevisionRepository());
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
            'aggregate_root_proxy_factory' => 'KmbZendDbInfrastructure\Proxy\EnvironmentProxyFactory',
            'table_name' => 'environments',
            'repository_class' => 'KmbZendDbInfrastructure\Service\EnvironmentRepository',
        ]);

        $factory->createService(Bootstrap::getServiceManager());
    }

    /**
     * @test
     * @expectedException \GtnPersistZendDb\Exception\MissingConfigurationException
     * @expectedExceptionMessage auto_updated_modules_table_name is missing in repository configuration
     */
    public function cannotCreateServiceIfNoAutoUpdatedModulesTableName()
    {
        $factory = new EnvironmentRepositoryFactory();
        $factory->setConfig([
            'aggregate_root_class' => 'KmbDomain\Model\Environment',
            'aggregate_root_proxy_factory' => 'KmbZendDbInfrastructure\Proxy\EnvironmentProxyFactory',
            'table_name' => 'environments',
            'paths_table_name' => 'environments',
            'repository_class' => 'KmbZendDbInfrastructure\Service\EnvironmentRepository',
        ]);

        $factory->createService(Bootstrap::getServiceManager());
    }
}
