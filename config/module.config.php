<?php
return [
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
    ],
    'zenddb_repositories' => [
        'EnvironmentRepository' => [
            'aggregate_root_class' => 'KmbDomain\Model\Environment',
            'aggregate_root_proxy_factory' => 'KmbZendDbInfrastructure\Proxy\EnvironmentProxyFactory',
            'aggregate_root_hydrator_class' => 'KmbZendDbInfrastructure\Hydrator\EnvironmentHydrator',
            'table_name' => 'environments',
            'table_sequence_name' => 'environment_id_seq',
            'paths_table_name' => 'environments_paths',
            'factory' => 'KmbZendDbInfrastructure\Service\EnvironmentRepositoryFactory',
            'repository_class' => 'KmbZendDbInfrastructure\Service\EnvironmentRepository',
        ],
        'UserRepository' => [
            'aggregate_root_class' => 'KmbDomain\Model\User',
            'aggregate_root_hydrator_class' => 'KmbZendDbInfrastructure\Hydrator\UserHydrator',
            'table_name' => 'users',
            'table_sequence_name' => 'user_id_seq',
            'repository_class' => 'KmbZendDbInfrastructure\Service\UserRepository',
        ],
        'RevisionRepository' => [
            'aggregate_root_class' => 'KmbDomain\Model\Revision',
            'aggregate_root_proxy_factory' => 'KmbZendDbInfrastructure\Proxy\RevisionProxyFactory',
            'aggregate_root_hydrator_class' => 'KmbZendDbInfrastructure\Hydrator\RevisionHydrator',
            'table_name' => 'revisions',
            'table_sequence_name' => 'revision_id_seq',
            'environment_class' => 'KmbDomain\Model\Environment',
            'environment_proxy_factory' => 'KmbZendDbInfrastructure\Proxy\EnvironmentProxyFactory',
            'environment_hydrator_class' => 'KmbZendDbInfrastructure\Hydrator\EnvironmentHydrator',
            'environment_table_name' => 'environments',
            'factory' => 'KmbZendDbInfrastructure\Service\RevisionRepositoryFactory',
            'repository_class' => 'KmbZendDbInfrastructure\Service\RevisionRepository',
        ],
        'GroupRepository' => [
            'aggregate_root_class' => 'KmbDomain\Model\Group',
            'aggregate_root_proxy_factory' => 'KmbZendDbInfrastructure\Proxy\GroupProxyFactory',
            'aggregate_root_hydrator_class' => 'KmbZendDbInfrastructure\Hydrator\GroupHydrator',
            'table_name' => 'groups',
            'table_sequence_name' => 'group_id_seq',
            'factory' => 'KmbZendDbInfrastructure\Service\GroupRepositoryFactory',
            'repository_class' => 'KmbZendDbInfrastructure\Service\GroupRepository',
        ],
    ],
];
