#!/usr/bin/env php
<?php

use GetOpt\GetOpt;
use GetOpt\Command;

if (PHP_SAPI !== 'cli') {
    die('Only cli application!');
}

$autoloader  = function () {
    $files = [
        __DIR__ . '/../../../autoload.php', // composer dependency
        __DIR__ . '/../vendor/autoload.php', // stand-alone package
    ];
    foreach ($files as $file) {
        if (is_file($file)) {
            require_once $file;

            return true;
        }
    }

    return false;
};
if (!$autoloader()) {
    die('autoload.php not found');
}

$files = [
    __DIR__ . '/../../../../config/config.php', // composer dependency
    __DIR__ . '/../config/config.php', // stand-alone package
];
$config = null;
foreach ($files as $file) {
    if (is_file($file)) {
        $config = require_once $file;
    }
}

if (!is_array($config)
    || !isset($config['settings'])
    || !is_array($config['settings'])
    || !isset($config['settings']['db'])
    || !is_array($config['settings']['db'])
) {
    throw new \Exception(
        'Empty config'
    );
}

$db_settings_arr = [];
foreach ($config['settings']['db'] as $db_id => $db_config) {
    if (!is_string($db_id)
        || !is_array($db_config)
        || !isset($db_config['host'], $db_config['db_name'], $db_config['user'], $db_config['password'])
        || !is_string($db_config['host'])
        || !is_string($db_config['db_name'])
        || !is_string($db_config['user'])
        || !is_string($db_config['password'])
        || (isset($db_config['dump_file_path']) && !is_string($db_config['dump_file_path']))
    ) {
        throw new \Exception('Invalid database config');
    }

    $normalized_config = [
        'host' => $db_config['host'],
        'db_name' => $db_config['db_name'],
        'user' => $db_config['user'],
        'password' => $db_config['password'],
    ];
    if (isset($db_config['dump_file_path'])) {
        $normalized_config['dump_file_path'] = $db_config['dump_file_path'];
    }

    $db_settings_arr[$db_id] = $normalized_config;
}

$get_opt = new GetOpt();

$get_opt->addCommand(
    new \WebSK\DB\Console\MigrationAutoCommand($db_settings_arr)
);

$get_opt->addCommand(
    new \WebSK\DB\Console\MigrationHandleCommand($db_settings_arr)
);

$get_opt->process();

$command = $get_opt->getCommand();
if (!$command instanceof Command) {
    echo $get_opt->getHelpText();
    exit;
}

call_user_func($command->getHandler(), $get_opt);
