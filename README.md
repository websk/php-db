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
$container->set('DB_SERVICE_CONTAINER_ID', function (ContainerInterface $container) {
    $settings = $container->get('settings');
    $db_config = $settings['db']['db_skif'];

    return new DBServiceFactory::factoryMySQL($db_config);
});
```

## Use DBWrapper

Set DBWrapper db service in App
```
DBWrapper::setDbService($container->get('DB_SERVICE_CONTAINER_ID'));
```