<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Playground\Make\Test\Building;

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

        $namespace = $this->parseClassInput($this->c->namespace());

        if ($add_Package_Api) {
            $this->addToBuildPackageProviders(sprintf(
                '\%1$s\Api\ServiceProvider::class',
                $namespace
            ));
        }

        if ($add_Package_Resource) {
            $this->addToBuildPackageProviders(sprintf(
                '\%1$s\Resource\ServiceProvider::class',
                $namespace
            ));
        }

        if ($add_Package_Model) {
            $this->addToBuildPackageProviders(sprintf(
                '\%1$s\Model\ServiceProvider::class',
                $namespace
            ));
        }

        foreach ($this->build_providers as $provider) {
            $test_trait_providers .= sprintf('%1$s\'%2$s\',%3$s', str_repeat(' ', 8), $provider, PHP_EOL);
        }

        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$test_trait_providers' => $test_trait_providers,
        //     '$this->build_providers' => $this->build_providers,
        // ]);
        $this->searches['test_trait_providers'] = $test_trait_providers;
    }
}
