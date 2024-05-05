<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Tests\Unit\Playground\Make\Test;

use Playground\Test\OrchestraTestCase;

/**
 * \Tests\Unit\Playground\Make\Test\TestCase
 */
class TestCase extends OrchestraTestCase
{
    use FileTrait;

    protected function getPackageProviders($app)
    {
        return [
            \Playground\ServiceProvider::class,
            \Playground\Make\ServiceProvider::class,
            \Playground\Make\Test\ServiceProvider::class,
        ];
    }
}
