<?php

namespace WebSK\DB\Tests;

use GetOpt\GetOpt;
use PHPUnit\Framework\TestCase;
use WebSK\DB\Console\MigrationAutoCommand;
use WebSK\DB\Console\MigrationHandleCommand;

class ConsoleCommandsTest extends TestCase
{
    public function testCommandsAreRegisteredAndShownInHelp(): void
    {
        $db_config = [
            'host' => 'localhost',
            'db_name' => 'test',
            'user' => 'test',
            'password' => 'test',
        ];
        $get_opt = new GetOpt();

        $get_opt->addCommand(new MigrationAutoCommand(['default' => $db_config]));
        $get_opt->addCommand(new MigrationHandleCommand(['default' => $db_config]));

        $help = $get_opt->getHelpText();
        self::assertStringContainsString(MigrationAutoCommand::NAME, $help);
        self::assertStringContainsString(MigrationHandleCommand::NAME, $help);
    }

    public function testCommandsRejectEmptyDatabaseConfiguration(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No database entries in config');

        (new MigrationAutoCommand([]))->execute();
    }

    public function testInteractiveCommandRejectsEmptyDatabaseConfiguration(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No database entries in config');

        (new MigrationHandleCommand([]))->execute();
    }

    public function testCliScriptReportsMissingConfiguration(): void
    {
        $process = proc_open(
            [PHP_BINARY, dirname(__DIR__) . '/bin/websk_db_migration.php'],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );
        self::assertIsResource($process);

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit_code = proc_close($process);

        self::assertNotSame(0, $exit_code);
        self::assertStringContainsString('Empty config', $stdout . $stderr);
    }
}
