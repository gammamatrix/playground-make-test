<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Tests\Feature\Playground\Make\Test\Console\Commands\TestMakeCommand;

use PHPUnit\Framework\Attributes\CoversClass;
use Playground\Make\Test\Console\Commands\TestMakeCommand;
use Tests\Feature\Playground\Make\Test\TestCase;

/**
 * \Tests\Feature\Playground\Make\Test\Console\Commands\TestMakeCommand
 */
#[CoversClass(TestMakeCommand::class)]
class CommandTest extends TestCase
{
    public function test_command_without_options_or_arguments(): void
    {
        /**
         * @var \Illuminate\Testing\PendingCommand $result
         */
        $result = $this->artisan('playground:make:test');
        $result->assertExitCode(1);
        $result->expectsOutputToContain( __('playground-make::generator.input.error'));
    }

    public function test_command_skeleton(): void
    {
        /**
         * @var \Illuminate\Testing\PendingCommand $result
         */
        $result = $this->artisan('playground:make:test testing --skeleton --force');
        $result->assertExitCode(0);
    }
}
