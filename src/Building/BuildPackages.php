<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Playground\Make\Test\Building;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * \Playground\Make\Test\Building\Test\BuildPackages
 */
trait BuildPackages
{
    /**
     * @var array<int, string>
     */
    protected array $build_providers = [];

    protected function addToBuildPackageProviders(string $provider): void
    {
        if ($provider && ! in_array($provider, $this->build_providers)) {
            // $pattern = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/';
            $pattern = '@^([\\\\])(.*)::class$@';
            if (! preg_match($pattern, $provider)) {
                Log::warning(__('playground-make-package::configuration.provider.invalid', [
                    'provider' => $provider,
                ]));

                return;
            }
            $this->c->addClassTo('package_providers', $provider);

            $this->build_providers[] = $provider;
        }
    }

    protected function buildClass_getPackageProviders(string $type): void
    {
        $extends = '';

        $this->build_providers = $this->c->package_providers();

        $test_trait_providers = '';

        $add_Playground = $this->c->playground();
        $add_Playground_Auth = false;
        $add_Playground_Blade = false;
        $add_Playground_Http = false;
        $add_Playground_Login = false;
        $add_Playground_Site = false;

        $add_Package_Api = false;
        $add_Package_Model = false;
        $add_Package_Resource = false;

        if ($type === 'providers-api') {
            $add_Playground_Auth = true;
            $add_Playground_Http = true;
            $add_Package_Api = true;
            $add_Package_Model = true;
        } elseif ($type === 'providers-model') {
            $add_Package_Model = true;
        } elseif ($type === 'providers-resource') {
            $add_Playground_Auth = true;
            $add_Playground_Blade = true;
            $add_Playground_Http = true;
            $add_Playground_Login = true;
            $add_Playground_Site = true;
            $add_Package_Model = true;
            $add_Package_Resource = true;
        }

        if ($add_Playground) {
            $this->addToBuildPackageProviders('\Playground\ServiceProvider::class');
        }

        if ($add_Playground_Auth) {
            $this->addToBuildPackageProviders('\Playground\Auth\ServiceProvider::class');
        }

        if ($add_Playground_Blade) {
            $this->addToBuildPackageProviders('\Playground\Blade\ServiceProvider::class');
        }

        if ($add_Playground_Http) {
            $this->addToBuildPackageProviders('\Playground\Http\ServiceProvider::class');
        }

        if ($add_Playground_Login) {
            $this->addToBuildPackageProviders('\Playground\Login\Blade\ServiceProvider::class');
        }

        if ($add_Playground_Site) {
            $this->addToBuildPackageProviders('\Playground\Site\Blade\ServiceProvider::class');
        }

        $namespace = $this->parseClassInput($this->rootNamespace());

        if ($add_Package_Api) {
            // Add the model ServiceProvider
            $this->addToBuildPackageProviders(sprintf(
                '\\%1$s\\ServiceProvider::class',
                Str::of($this->rootNamespace())->before('\\Api')->toString()
            ));
        }

        if ($add_Package_Resource) {
            // Add the model ServiceProvider
            $this->addToBuildPackageProviders(sprintf(
                '\\%1$s\\ServiceProvider::class',
                Str::of($this->rootNamespace())->before('\\Resource')->toString()
            ));
        }

        // Add the package service provider
        $this->addToBuildPackageProviders(sprintf(
            '\\%1$sServiceProvider::class',
            $namespace
        ));

        foreach ($this->build_providers as $provider) {
            $test_trait_providers .= sprintf('%1$s%2$s,%3$s', str_repeat(' ', 12), $provider, PHP_EOL);
        }

        $this->c->setOptions([
            'extends' => $extends,
        ]);

        // dd([
        //     '__METHOD__' => __METHOD__,
        //     '$add_Playground' => $add_Playground,
        //     '$add_Playground_Auth' => $add_Playground_Auth,
        //     '$add_Playground_Blade' => $add_Playground_Blade,
        //     '$add_Playground_Http' => $add_Playground_Http,
        //     '$add_Playground_Login' => $add_Playground_Login,
        //     '$add_Playground_Site' => $add_Playground_Site,
        //     '$add_Package_Model' => $add_Package_Model,
        //     '$add_Package_Resource' => $add_Package_Resource,
        //     '$add_Package_Api' => $add_Package_Api,
        //     '$namespace' => $namespace,
        //     '$test_trait_providers' => $test_trait_providers,
        //     '$this->build_providers' => $this->build_providers,
        //     '$this->c' => $this->c,
        // ]);
        $this->searches['test_trait_providers'] = $test_trait_providers;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function prepareOptionsForTestCase(array $options = []): void
    {
        $rootNamespace = $this->rootNamespace();

        if (in_array($this->suite, [
            'acceptance',
            'feature',
        ])) {
            $this->buildClass_uses_add(sprintf(
                'Tests\Unit\%1$sPackageProviders',
                $rootNamespace
            ));
            $this->c->setOptions([
                'extends' => 'OrchestraTestCase',
                'extends_use' => 'Playground/Test/OrchestraTestCase',
            ]);
        } else {
            // $this->buildClass_uses_add(sprintf(
            //     'Tests\Unit\%1$sPackageProviders',
            //     $rootNamespace
            // ));
            $this->c->setOptions([
                'extends' => 'OrchestraTestCase',
                'extends_use' => 'Playground/Test/OrchestraTestCase',
            ]);
        }
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     // '$options' => $options,
        //     '$rootNamespace' => $rootNamespace,
        //     // '$this->c->uses()' => $this->c->uses(),
        //     // '$this->c->suite()' => $this->c->suite(),
        //     '$this->c' => $this->c,
        //     '$this->options()' => $this->options(),
        // ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function prepareOptionsForRequestTestCase(array $options = []): void
    {
        $rootNamespace = $this->rootNamespace();

        $this->buildClass_uses_add(sprintf(
            'Tests\Unit\%1$sPackageProviders',
            $rootNamespace
        ));
        $this->buildClass_uses_add('Playground/Test/Unit/Http/Requests/RequestCase');
        $this->c->setOptions([
            'extends' => 'RequestCase',
            'extends_use' => 'Playground/Test/Unit/Http/Requests/RequestCase',
        ]);
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     // '$options' => $options,
        //     '$rootNamespace' => $rootNamespace,
        //     // '$this->c->uses()' => $this->c->uses(),
        //     // '$this->c->suite()' => $this->c->suite(),
        //     '$this->c' => $this->c,
        //     '$this->options()' => $this->options(),
        // ]);
    }
}
