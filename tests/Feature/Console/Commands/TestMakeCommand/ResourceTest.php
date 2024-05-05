<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Tests\Feature\Playground\Make\Test\Console\Commands\TestMakeCommand;

use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\CoversClass;
use Playground\Make\Test\Console\Commands\TestMakeCommand;
use Tests\Feature\Playground\Make\Test\TestCase;

/**
 * \Tests\Feature\Playground\Make\Test\Console\Commands\TestMakeCommand\ResourceTest
 */
#[CoversClass(TestMakeCommand::class)]
class ResourceTest extends TestCase
{
    public function test_command_make_playground_resource_tests_without_model_file_and_fail(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(__('playground-make-test::generator.model.required'));

        $command = sprintf(
            'playground:make:test --force --file %1$s',
            $this->getResourceFile('playground-resource')
        );
        // dump($command);
        // $result = $this->withoutMockingConsoleOutput()->artisan($command);
        // dd(Artisan::output());

        // /**
        //  * @var \Illuminate\Testing\PendingCommand $result
        //  */
        $this->artisan($command);
    }

    public function test_command_make_playground_resource_tests_with_force_without_skeleton(): void
    {
        $command = sprintf(
            'playground:make:test --force --file %1$s --model-file %2$s',
            $this->getResourceFile('playground-resource'),
            $this->getResourceFile('model-crm-contact')
        );
        // dump($command);
        // $result = $this->withoutMockingConsoleOutput()->artisan($command);
        // dd(Artisan::output());

        /**
         * @var \Illuminate\Testing\PendingCommand $result
         */
        $result = $this->artisan($command);
        $result->assertExitCode(0);
        $result->expectsOutputToContain('Unit Test [storage/app/stub/playground-crm/tests/Unit/Contact/InstanceTest.php] created successfully.');
    }

    public function test_command_make_playground_resource_tests_without_force_with_file_without_skeleton(): void
    {
        $command = sprintf(
            'playground:make:test --file %1$s --model-file %2$s',
            $this->getResourceFile('playground-resource'),
            $this->getResourceFile('model-crm-contact')
        );
        // dump($command);
        // $result = $this->withoutMockingConsoleOutput()->artisan($command);
        // dump(Artisan::output());

        /**
         * @var \Illuminate\Testing\PendingCommand $result
         */
        $result = $this->artisan($command);
        $result->assertExitCode(1);
        $result->expectsOutputToContain('Unit Test already exists.');
    }

    public function test_command_make_playground_resource_tests_with_force_with_file_with_skeleton(): void
    {
        $command = sprintf(
            'playground:make:test --skeleton --force --file %1$s --model-file %2$s',
            $this->getResourceFile('playground-resource'),
            $this->getResourceFile('model-crm-contact')
        );
        // dump($command);
        // $result = $this->withoutMockingConsoleOutput()->artisan($command);
        // dump(Artisan::output());

        /**
         * @var \Illuminate\Testing\PendingCommand $result
         */
        $result = $this->artisan($command);
        $result->assertExitCode(0);
        $result->expectsOutputToContain('Unit Test [storage/app/stub/playground-crm/tests/Unit/Contact/InstanceTest.php] created successfully.');
    }
}
