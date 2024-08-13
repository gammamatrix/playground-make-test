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
            '\\%1$s\\ServiceProvider::class',
            $namespace
        ));

        if ($add_Package_Api) {
            $this->addToBuildPackageProviders('\Laravel\Sanctum\SanctumServiceProvider::class');
        }

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
        $type = $this->c->type();
        $rootNamespace = $this->rootNamespace();

        $options_set = [];

        if (in_array($this->suite, [
            'acceptance',
            'feature',
        ])) {
            $this->buildClass_uses_add(sprintf(
                'Tests\Unit\%1$s\\PackageProviders',
                $rootNamespace
            ));
            $options_set['extends'] = 'OrchestraTestCase';
            $options_set['extends_use'] = 'Playground/Test/OrchestraTestCase';
        } else {
            $options_set['extends'] = 'OrchestraTestCase';
            $options_set['extends_use'] = 'Playground/Test/OrchestraTestCase';
        }

        if (! empty($options['model-package'])
            && is_string($options['model-package'])
        ) {
            $this->searches['packagist_vendor'] = Str::of($options['model-package'])->before('/')->toString();
            $this->searches['packagist_model'] = Str::of($options['model-package'])->after('/')->toString();
        }

        $this->c->setOptions($options_set);
        // dd([
        //     '__METHOD__' => __METHOD__,
        //     '$options' => $options,
        //     '$options_set' => $options_set,
        //     '$rootNamespace' => $rootNamespace,
        //     // '$this->c->uses()' => $this->c->uses(),
        //     // '$this->c->suite()' => $this->c->suite(),
        //     '$this->c' => $this->c,
        //     '$this->searches' => $this->searches,
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
            'Tests\\Unit\\%1$s\\PackageProviders',
            $rootNamespace
        ));
        $this->buildClass_uses_add('Playground/Test/Unit/Http/Requests/RequestCase');
        $this->c->setOptions([
            'extends' => 'RequestCase',
            'extends_use' => 'Playground/Test/Unit/Http/Requests/RequestCase',
        ]);

        $request_type = '';
        if (in_array($this->c->type(), [
            'playground-request-model',
            'playground-request-model-store',
            'playground-request-model-update',
        ])) {

            $request_type = Str::of($this->c->name())->before('RequestTest')->toString();

        }

        $this->searches['request_type'] = $request_type;

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
     * @var array<int, string>
     */
    protected array $controllerCase_crud_api = [
        'Resource/Playground/CreateJsonTrait',
        'Resource/Playground/DestroyJsonTrait',
        'Resource/Playground/EditJsonTrait',
        'Resource/Playground/IndexJsonTrait',
        'Resource/Playground/LockJsonTrait',
        'Resource/Playground/RestoreJsonTrait',
        'Resource/Playground/ShowJsonTrait',
        'Resource/Playground/StoreJsonTrait',
        'Resource/Playground/UnlockJsonTrait',
        'Resource/Playground/UpdateJsonTrait',
    ];

    /**
     * @var array<int, string>
     */
    protected array $controllerCase_crud_api_revisionable = [
        'Resource/Playground/CreateJsonTrait',
        'Resource/Playground/DestroyJsonTrait',
        'Resource/Playground/EditJsonTrait',
        'Resource/Playground/IndexJsonTrait',
        'Resource/Playground/LockJsonTrait',
        'Resource/Playground/RestoreJsonTrait',
        'Resource/Playground/RestoreRevisionJsonTrait',
        'Resource/Playground/RevisionJsonTrait',
        'Resource/Playground/RevisionsJsonTrait',
        'Resource/Playground/ShowJsonTrait',
        'Resource/Playground/StoreJsonTrait',
        'Resource/Playground/UnlockJsonTrait',
        'Resource/Playground/UpdateJsonTrait',
    ];

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

    /**
     * @var array<int, string>
     */
    protected array $controllerCase_crud_resource_revisionable = [
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
        'Resource/Playground/RestoreRevisionJsonTrait',
        'Resource/Playground/RestoreRevisionTrait',
        'Resource/Playground/RestoreTrait',
        'Resource/Playground/RevisionJsonTrait',
        'Resource/Playground/RevisionsJsonTrait',
        'Resource/Playground/RevisionsTrait',
        'Resource/Playground/RevisionTrait',
        'Resource/Playground/ShowJsonTrait',
        'Resource/Playground/ShowTrait',
        'Resource/Playground/StoreJsonTrait',
        'Resource/Playground/StoreTrait',
        'Resource/Playground/UnlockJsonTrait',
        'Resource/Playground/UnlockTrait',
        'Resource/Playground/UpdateJsonTrait',
        'Resource/Playground/UpdateTrait',
    ];

    /**
     * @param array<string, mixed> $options
     */
    public function prepareOptionsForControllerTestCase(array $options = []): void
    {
        $rootNamespace = $this->rootNamespace();
        $revision = $this->hasOption('revision') && $this->option('revision');

        $this->buildClass_uses_add('Playground/Test/Feature/Http/Controllers/Resource');
        $this->buildClass_uses_add(sprintf(
            'Tests\Feature\%1$s\TestCase as BaseTestCase',
            Str::of(
                $this->parseClassInput($this->rootNamespace())
            )->trim('\\')->toString()
        ));
        $this->searches['model_attribute'] = 'title';
        $this->searches['module_label'] = $this->c->module();
        $this->searches['module_label_plural'] = Str::of($this->c->module())->plural()->toString();
        $this->searches['module_slug'] = $this->c->module_slug();
        $this->searches['module_route'] = Str::of($this->c->package())->replace('-', '.')->toString();
        $this->searches['module_privilege'] = Str::of($this->c->package())->finish(':')->toString();
        $this->searches['module_view'] = Str::of($this->c->package())->finish('::')->toString();

        $this->addResourceTraits();

        if ($revision) {
            $this->addRevisionProperties();
            $this->addRevisionMethods();
        }

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
        $revision = $this->hasOption('revision') && $this->option('revision');

        $type = $this->c->type();

        if (in_array($type, [
            'playground-api-controller-test-case',
        ])) {
            if ($revision) {
                $traits = $this->controllerCase_crud_api_revisionable;
            } else {
                $traits = $this->controllerCase_crud_api;
            }
        } elseif (in_array($type, [
            'playground-resource-controller-test-case',
        ])) {
            if ($revision) {
                $traits = $this->controllerCase_crud_resource_revisionable;
            } else {
                $traits = $this->controllerCase_crud_resource;
            }
        } else {
            $traits = $this->controllerCase_crud_resource;
        }

        $this->searches['test_case_use_traits'] = '';
        $test_case_use_traits = '';
        foreach ($traits as $use) {
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

    public function addRevisionPropertiesForModel(
        string $fqdn,
        string $variable
    ): self {
        $this->searches['revision_properties'] = <<<PHP_CODE

    /**
     * @var class-string<Model>
     */
    public string \$fqdnRevision = \\{$fqdn}Revision::class;

    public string \$revisionId = '{$variable}_id';

    public string \$revisionRouteParameter = '{$variable}_revision';

PHP_CODE;

        return $this;
    }

    public function addRevisionProperties(): self
    {
        $this->searches['revision_properties'] = <<<'PHP_CODE'

    /**
     * @var class-string<Model>
     */
    public string $fqdnRevision = Model::class;

    public string $revisionId = 'revision_id';

    public string $revisionRouteParameter = 'revision';

PHP_CODE;

        return $this;
    }

    public function addRevisionMethods(): self
    {
        $this->searches['revision_methods'] = <<<'PHP_CODE'

    /**
     * @return class-string<Model>
     */
    public function getGetFqdnRevision(): string
    {
        return $this->fqdnRevision;
    }

    public function getRevisionId(): string
    {
        return $this->revisionId;
    }

    public function getRevisionRouteParameter(): string
    {
        return $this->revisionRouteParameter;
    }

PHP_CODE;

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function prepareOptionsForControllerModelCase(array $options = []): void
    {
        $rootNamespace = $this->rootNamespace();
        $revision = $this->hasOption('revision') && $this->option('revision');

        // $this->buildClass_uses_add('Playground/Test/Feature/Http/Controllers/Resource');
        // $this->buildClass_uses_add('Tests\Feature\Playground\Matrix\Resource\TestCase as BaseTestCase');
        $this->c->setOptions([
            'extends' => 'TestCase',
            'extends_use' => '',
            // 'extends_use' => sprintf(
            //     'Tests\Feature\%1$s\TestCase',
            //     Str::of(
            //         $this->parseClassInput($this->rootNamespace())
            //     )->trim('\\')->toString()
            // ),
        ]);

        $this->searches['model_attribute'] = $this->model?->model_attribute() ?: 'title';
        $this->searches['module_label'] = $this->c->module();
        $this->searches['module_label_plural'] = Str::of($this->c->module())->plural()->toString();
        $this->searches['module_slug'] = $this->c->module_slug();
        $this->searches['module_route'] = Str::of($this->c->package())->replace('-', '.')->toString();
        $this->searches['module_privilege'] = Str::of($this->c->package())->finish(':')->toString();
        $this->searches['module_view'] = Str::of($this->c->package())->finish('::')->toString();

        $this->searches['model'] = $this->model?->model() ?? 'Dummy';
        $this->searches['table'] = $this->model?->table() ?? '';

        $fqdn = $this->model?->fqdn() ?? 'Dummy';
        $this->searches['model_fqdn'] = $this->parseClassInput($fqdn);
        // $this->searches['model_fqdn'] = $this->parseClassConfig($fqdn);

        $model_slug = $this->model?->model_slug() ?? 'dummy';
        $variable = Str::of($model_slug)->snake()->toString();

        $this->searches['model_slug'] = $model_slug;
        $this->searches['model_label_plural'] = $this->model?->model_plural() ?? 'dummies';
        $this->searches['model_singular'] = $this->model?->model_singular() ?? 'Dummy';
        $this->searches['model_slug_plural'] = Str::of($this->searches['model_singular'])->plural()->kebab()->toString();
        // $this->searches['model_slug'] = $this->model?->model_slug() ?? '';

        $this->searches['model_route'] = Str::of($this->searches['module_route'])->finish('.')->finish($this->searches['model_slug_plural'])->toString();

        $this->searches['privilege'] = Str::of($this->c->package())->finish(':')->finish($this->searches['model_slug'])->toString();
        $this->searches['view'] = Str::of($this->c->package())->replace('-', '.')->finish('::')->finish($this->searches['model_slug'])->toString();

        $this->searches['model_label'] = $this->searches['model_singular'];

        if ($this->c->withCovers()) {
            $this->addCovers();
        }

        $this->addStructureModel();
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$options' => $options,
        //     '$revision' => $revision,
        //     // '$rootNamespace' => $rootNamespace,
        //     // '$this->c' => $this->c,
        //     '$this->searches' => $this->searches,
        //     // '$this->model' => $this->model?->toArray(),
        //     // '$this->options()' => $this->options(),
        // ]);

        if ($revision) {
            $this->addRevisionPropertiesForModel(
                $this->parseClassInput($fqdn),
                $variable
            );
        }

        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$options' => $options,
        //     '$revision' => $revision,
        //     // '$rootNamespace' => $rootNamespace,
        //     // '$this->c' => $this->c,
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

    /**
     * @param array<string, mixed> $options
     */
    public function prepareOptionsForAboutCommand(array $options = []): void
    {
        $isApi = $this->hasOption('api') && $this->option('api');
        $isResource = $this->hasOption('resource') && $this->option('resource');

        $name = $this->c->module();
        if ($this->c->playground()) {
            $name = Str::of($name)->start('Playground: ')->toString();

            if ($isApi) {
                $name = Str::of($name)->finish(' API')->toString();
            } elseif ($isResource) {
                $name = Str::of($name)->finish(' Resource')->toString();
            }
        }
        $this->searches['name'] = $name;
        $this->searches['namespace_root'] = $this->parseClassInput($this->rootNamespace());
    }
}
