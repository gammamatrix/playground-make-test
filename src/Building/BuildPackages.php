<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Playground\Make\Test\Building;

// use Illuminate\Support\Str;

/**
 * \Playground\Make\Test\Building\Test\BuildPackages
 */
trait BuildPackages
{
    /**
     * @var array<int, string>
     */
    protected array $test_trait_providers_model = [
        '\Playground\ServiceProvider::class',

        '\Playground\Matrix\ServiceProvider::class',
    ];

    /**
     * @var array<int, string>
     */
    protected array $test_trait_providers_api = [
        '\Playground\ServiceProvider::class',
        '\Playground\Auth\ServiceProvider::class',
        '\Playground\Http\ServiceProvider::class',

        '\Playground\Matrix\Api\ServiceProvider::class',
        '\Playground\Matrix\ServiceProvider::class',
    ];

    /**
     * @var array<int, string>
     */
    protected array $test_trait_providers_resource = [
        '\Playground\ServiceProvider::class',
        '\Playground\Auth\ServiceProvider::class',
        '\Playground\Blade\ServiceProvider::class',
        '\Playground\Http\ServiceProvider::class',
        '\Playground\Login\Blade\ServiceProvider::class',
        '\Playground\Site\Blade\ServiceProvider::class',

        '\Playground\Matrix\Resource\ServiceProvider::class',
        '\Playground\Matrix\ServiceProvider::class',
    ];

    protected function buildClass_getPackageProviders(string $type): void
    {
        $hasMany_properties = PHP_EOL;

        $test_trait_providers = '';

        /**
         * @var array<int, string> $providers
         */
        $providers = [];

        if ($type === 'unit-test-trait-api') {
            $providers = $this->test_trait_providers_api;
        } elseif ($type === 'unit-test-trait-model') {
            $providers = $this->test_trait_providers_model;
        } elseif ($type === 'unit-test-trait-resource') {
            $providers = $this->test_trait_providers_resource;
        }

        foreach ($providers as $provider) {
            $test_trait_providers .= sprintf('%1$s\'%2$s\',%3$s', str_repeat(' ', 8), $provider, PHP_EOL);
        }

        $this->searches['test_trait_providers'] = $test_trait_providers;
    }
}
