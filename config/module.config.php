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
            'aggregate_root_proxy_factory' => 'KmbDomain\Service\EnvironmentProxyFactory',
            'aggregate_root_hydrator_class' => 'KmbZendDbInfrastructure\Model\EnvironmentHydrator',
            'table_name' => 'environments',
            'table_sequence_name' => 'environment_id_seq',
            'paths_table_name' => 'environments_paths',
            'factory' => 'KmbZendDbInfrastructure\Service\EnvironmentRepositoryFactory',
            'repository_class' => 'KmbZendDbInfrastructure\Service\EnvironmentRepository',
        ],
        'UserRepository' => [
            'aggregate_root_class' => 'KmbDomain\Model\User',
            'aggregate_root_hydrator_class' => 'KmbZendDbInfrastructure\Model\UserHydrator',
            'table_name' => 'users',
            'table_sequence_name' => 'user_id_seq',
            'repository_class' => 'KmbZendDbInfrastructure\Service\UserRepository',
        ],
    ],
];
