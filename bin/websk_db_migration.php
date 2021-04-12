<?php

use GetOpt\GetOpt;
use WebSK\Utils\Assert;

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
    die(
        'You need to set up the project dependencies using the following commands:' . PHP_EOL .
        'curl -sS https://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );
}

$settings_arr = \WebSK\Config\ConfWrapper::value('settings');
$db_settings_arr = $settings_arr['db'] ?? [];

Assert::assert(PHP_SAPI === 'cli', 'Only cli application!');

$get_opt = new GetOpt();

$get_opt->addCommand(
    new \WebSK\DB\Console\MigrationAutoCommand($db_settings_arr)
);

$get_opt->addCommand(
    new \WebSK\DB\Console\MigrationHandleCommand($db_settings_arr)
);

$this->get_opt->process();

$command = $this->get_opt->getCommand();
if (!$command) {
    echo $this->get_opt->getHelpText();
    exit;
}

call_user_func($command->getHandler(), $this->get_opt);
