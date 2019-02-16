# WebSK php-db

## Configuration example

```
$config = [
    'settings' => [
        'db' => [
            'db_skif' => [
                'host' => 'mysql',
                'db_name' => 'skif',
                'user' => 'root',
                'password' => 'root',
            ]
        ]
    ]
];
```

## Registering a service

```
/**
 * @param ContainerInterface $container
 * @return DBService
 */
$container['db_service'] = function (ContainerInterface $container) {
    $db_config = $container['settings']['db']['db_skif'];

    $db_connector = new DBConnectorMySQL(
        $db_config['host'],
        $db_config['db_name'],
        $db_config['user'],
        $db_config['password']
    );

    $db_settings = new DBSettings(
        'mysql'
    );

    return new DBService($db_connector, $db_settings);
};
```

## Use DBWrapper

Set DBWrapper db service in App
```
DBWrapper::setDbService($container->get('DB_SERVICE_CONTAINER_ID'));
```