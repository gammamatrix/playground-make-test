<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Playground\Make\Test\Building;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    // protected array $controllerCase_crud_api = [

    // ];

    // protected array $controllerCase_crud_api_revisionable = [

    // ];

    /**
     * @var array<int, string>
     */
    protected array $controllerCase_crud_resource = [
        'Resource/Playground/CreateJsonTrait',
        'Resource/Playground/CreateTrait',
        'Resource/Playground/DestroyJsonTrait',
        'Resource/Playground/DestroyTrait',
        'Resource/Playground/EditJsonTrait',
        'Resource/Playground/EditTrait',
        'Resource/Playground/IndexJsonTrait',
        'Resource/Playground/IndexTrait',
        'Resource/Playground/LockJsonTrait',
        'Resource/Playground/LockTrait',
        'Resource/Playground/RestoreJsonTrait',
        'Resource/Playground/RestoreTrait',
        'Resource/Playground/ShowJsonTrait',
        'Resource/Playground/ShowTrait',
        'Resource/Playground/StoreJsonTrait',
        'Resource/Playground/StoreTrait',
        'Resource/Playground/UnlockJsonTrait',
        'Resource/Playground/UnlockTrait',
        'Resource/Playground/UpdateJsonTrait',
        'Resource/Playground/UpdateTrait',
    ];

    // protected array $controllerCase_crud_resource_revisionable = [

    // ];

    /**
     * @param array<string, mixed> $options
     */
    public function prepareOptionsForControllerTestCase(array $options = []): void
    {
        $rootNamespace = $this->rootNamespace();

        $this->buildClass_uses_add('Playground/Test/Feature/Http/Controllers/Resource');
        $this->buildClass_uses_add('Tests\Feature\Playground\Matrix\Resource\TestCase as BaseTestCase');
        $this->c->setOptions([
            'extends' => 'BaseTestCase',
            'extends_use' => 'Tests\Feature\Playground\Matrix\Resource\TestCase as BaseTestCase',
        ]);
        $this->searches['model_attribute'] = 'title';
        $this->searches['module_label'] = $this->c->module();
        $this->searches['module_label_plural'] = Str::of($this->c->module())->plural()->toString();
        $this->searches['module_slug'] = $this->c->module_slug();
        $this->searches['module_route'] = Str::of($this->c->package())->replace('-', '.')->toString();
        $this->searches['module_privilege'] = Str::of($this->c->package())->finish(':')->toString();
        $this->searches['module_view'] = Str::of($this->c->package())->replace('-', '.')->finish('::')->toString();

        $this->addResourceTraits();

        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$options' => $options,
        //     '$rootNamespace' => $rootNamespace,
        //     '$this->c' => $this->c,
        //     '$this->searches' => $this->searches,
        //     // '$this->options()' => $this->options(),
        // ]);
    }

    public function addResourceTraits(): self
    {
        $this->searches['test_case_use_traits'] = '';
        $test_case_use_traits = '';
        foreach ($this->controllerCase_crud_resource as $use) {
            if (is_string($use) && $use) {
                $test_case_use_traits .= sprintf(
                    '    use %2$s;%1$s',
                    PHP_EOL,
                    $this->parseClassInput($use)
                );
            }
        }

        if (! empty($test_case_use_traits)) {
            $this->searches['test_case_use_traits'] = static::INDENT.trim($test_case_use_traits).PHP_EOL.PHP_EOL;
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function prepareOptionsForControllerModelCase(array $options = []): void
    {
        $rootNamespace = $this->rootNamespace();

        $this->buildClass_uses_add('Playground/Test/Feature/Http/Controllers/Resource');
        $this->buildClass_uses_add('Tests\Feature\Playground\Matrix\Resource\TestCase as BaseTestCase');
        $this->c->setOptions([
            'extends' => 'BaseTestCase',
            'extends_use' => 'Tests\Feature\Playground\Matrix\Resource\TestCase as BaseTestCase',
        ]);
        $this->searches['model_attribute'] = $this->model?->model_attribute() ?: 'title';
        $this->searches['module_label'] = $this->c->module();
        $this->searches['module_label_plural'] = Str::of($this->c->module())->plural()->toString();
        $this->searches['module_slug'] = $this->c->module_slug();
        $this->searches['module_route'] = Str::of($this->c->package())->replace('-', '.')->toString();
        $this->searches['module_privilege'] = Str::of($this->c->package())->finish(':')->toString();
        $this->searches['module_view'] = Str::of($this->c->package())->replace('-', '.')->finish('::')->toString();

        $this->searches['model'] = $this->model?->model() ?? 'Dummy';
        $this->searches['table'] = $this->model?->table() ?? '';

        $fqdn = $this->model?->fqdn() ?? 'Dummy';
        $this->searches['model_fqdn'] = $this->parseClassInput($fqdn);
        // $this->searches['model_fqdn'] = $this->parseClassConfig($fqdn);

        $this->searches['model_slug'] = $this->model?->model_slug() ?? 'dummy';
        $this->searches['model_label_plural'] = $this->model?->model_plural() ?? 'dummies';
        $this->searches['model_singular'] = $this->model?->model_singular() ?? 'Dummy';
        $this->searches['model_slug_plural'] = Str::of($this->searches['model_singular'])->plural()->kebab()->toString();
        // $this->searches['model_slug'] = $this->model?->model_slug() ?? '';

        $this->searches['model_route'] = Str::of($this->searches['module_route'])->finish('.')->finish($this->searches['model_slug_plural'])->toString();

        $this->searches['privilege'] = Str::of($this->c->package())->finish(':')->finish($this->searches['model_slug'])->toString();
        $this->searches['view'] = Str::of($this->c->package())->replace('-', '.')->finish('::')->finish($this->searches['model_slug'])->toString();

        $this->searches['model_label'] = $this->searches['model_singular'];
        $this->addStructureModel();

        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$options' => $options,
        //     '$rootNamespace' => $rootNamespace,
        //     '$this->c' => $this->c,
        //     '$this->searches' => $this->searches,
        //     // '$this->model' => $this->model?->toArray(),
        //     // '$this->options()' => $this->options(),
        // ]);
    }

    public function addStructureModel(): self
    {
        $this->searches['structure_model'] = '';

        $attributes = $this->model?->attributes() ?? [];
        // $structure_model = '';

        $structure_model = sprintf(
            '%1$s\'%2$s\',%3$s',
            str_repeat(static::INDENT, 2),
            'id',
            PHP_EOL
        );

        foreach ($attributes as $attribute => $default) {
            if (is_string($attribute) && $attribute) {
                $structure_model .= sprintf(
                    '%1$s\'%2$s\',%3$s',
                    str_repeat(static::INDENT, 2),
                    $attribute,
                    PHP_EOL
                );
            }
        }

        if (! empty($structure_model)) {
            $this->searches['structure_model'] = rtrim($structure_model);
        }
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$structure_model' => $structure_model,
        //     '$this->c' => $this->c,
        //     '$this->searches' => $this->searches,
        //     '$this->options()' => $this->options(),
        // ]);

        return $this;
    }
}
