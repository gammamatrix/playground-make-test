<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Tests\Feature\Playground\Make\Test\Console\Commands\About;

use PHPUnit\Framework\Attributes\CoversClass;
use Playground\Test\OrchestraTestCase;

/**
 * \Tests\Feature\Playground\Make\Test\Console\Commands\About
 */
#[CoversClass(\Playground\Make\Test\ServiceProvider::class)]
class CommandTest extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Playground\ServiceProvider::class,
            \Playground\Make\ServiceProvider::class,
            \Playground\Make\Test\ServiceProvider::class,
        ];
    }

    public function test_command_about_displays_package_information_and_succeed_with_code_0(): void
    {
        /**
         * @var \Illuminate\Testing\PendingCommand $result
         */
        $result = $this->artisan('about');
        $result->assertExitCode(0);
        $result->expectsOutputToContain('Playground: Make Test');
    }
}
