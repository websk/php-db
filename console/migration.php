<?php

use GetOpt\GetOpt;
use WebSK\Utils\Assert;

require_once realpath(__DIR__ . '/../vendor/autoload.php');

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
