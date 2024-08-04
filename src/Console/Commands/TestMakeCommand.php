<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Playground\Make\Test\Console\Commands;

use Illuminate\Support\Str;
use Playground\Make\Building\Concerns;
use Playground\Make\Configuration\Contracts\PrimaryConfiguration as PrimaryConfigurationContract;
use Playground\Make\Console\Commands\GeneratorCommand;
use Playground\Make\Test\Building;
use Playground\Make\Test\Configuration\Test as Configuration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * \Playground\Stub\Console\Commands\TestMakeCommand
 */
#[AsCommand(name: 'playground:make:test')]
class TestMakeCommand extends GeneratorCommand
{
    use Building\BuildCovers;
    use Building\BuildForControllers;
    use Building\BuildForModels;
    use Building\BuildModelRelationships;
    use Building\BuildPackages;
    use Concerns\BuildImplements;
    use Concerns\BuildUses;

    /**
     * @var class-string<Configuration>
     */
    public const CONF = Configuration::class;

    /**
     * @var PrimaryConfigurationContract&Configuration
     */
    protected PrimaryConfigurationContract $c;

    /**
     * @var array<string, string>
     */
    public const SEARCH = [
        'class' => '',
        'name' => '',
        'module' => '',
        'module_slug' => '',
        'namespace' => '',
        'namespace_root' => '',
        'extends' => '',
        'implements' => '',
        'organization' => '',
        'use' => '',
        'use_class' => '',
        'properties' => '',
        'table' => '',
        'setup' => '',
        'tests' => '',
        'model_fqdn' => '',
        'hasRelationships' => 'false',
        'hasMany_properties' => '',
        'hasOne_properties' => '',
        'test_trait_providers' => '',
        'covers_class' => '',
    ];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'playground:make:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new test case';

    protected string $suite = 'unit';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Test';

    protected string $path_destination_folder = 'tests';

    /**
     * @var array<int, string>
     */
    protected array $options_type_suggested = [
        // Standard package tests
        'command-about',
        // Service Providers
        'providers',
        'providers-api',
        'providers-model',
        'providers-resource',
        // Models: Case
        'model-case',
        // Models: Test
        'model',
        'playground-model',
        // Case
        'test-case',
        'playground-model-test-case',
        // APIs: Case
        'api-test-case',
        'playground-api-test-case',
        'playground-api-controller-test-case',
        'playground-api-controller-model-case',
        // APIs: Test
        'playground-api',
        // Policies
        'policy',
        // Requests
        'playground-request-test-case',
        // Resources: Case
        'resource-test-case',
        'playground-resource-test-case',
        'playground-resource-controller-test-case',
        'playground-resource-controller-model-case',
        // Resources: Test
        'playground-resource',
        // Service Provider
        'playground-service-provider-policies',
    ];

    public function prepareOptions(): void
    {
        $initModel = false;
        $options = $this->options();

        $model = empty($options['model']) || ! is_string($options['model']) ? '' : $options['model'];
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$options' => $options,
        //     // '$this->configuration' => $this->configuration,
        //     '$this->c' => $this->c,
        //     '$this->searches' => $this->searches,
        //     // '$this->model' => $this->model,
        // ]);

        if ($this->hasOption('playground') && $this->option('playground')) {
            $this->c->setOptions([
                'playground' => true,
            ]);
        }

        if ($this->hasOption('covers') && $this->option('covers')) {
            $this->c->setOptions([
                'withCovers' => true,
            ]);
        }

        $type = $this->prepareOptionsType($options);

        $suite = $this->option('suite');
        $suite = is_string($suite) ? strtolower($suite) : '';
        $suite = $suite ? $suite : $this->c->suite();

        // NOTE: Suites could be a configuration option.
        $this->suite = empty($suite) || ! in_array($suite, [
            'acceptance',
            'feature',
            'unit',
        ]) ? 'unit' : $suite;

        $this->c->setOptions([
            'suite' => $this->suite,
        ]);

        $this->type = 'Test';
        if ($this->suite) {
            $this->type = Str::of(
                $this->suite
            )->replace('-', ' ')->ucfirst()->finish(' Test')->toString();
        }

        $rootNamespace = $this->rootNamespace();

        if (in_array($this->c->type(), [
            'playground-api-controller-model-case',
            'playground-resource-controller-model-case',
        ])) {
            $initModel = true;
        }

        if ($initModel) {
            $this->initModel($this->c->skeleton());

            $modelFile = $this->getModelFile();
            if ($modelFile && $this->model?->name()) {
                $this->c->addMappedClassTo(
                    'models',
                    $this->model->name(),
                    $modelFile
                );

                $this->c->setOptions([
                    'model_fqdn' => $this->model->model_fqdn(),
                ]);
            }
            // dd([
            //     '__METHOD__' => __METHOD__,
            //     '$modelFile' => $modelFile,
            //     '$this->type' => $this->type,
            //     '$type' => $type,
            //     '$rootNamespace' => $rootNamespace,
            //     '$this->model' => $this->model,
            //     // '$this->model' => $this->model->toArray(),
            // ]);
        }
        // $this->applyConfigurationToSearch();

        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$this->suite' => $this->suite,
        //     '$this->type' => $this->type,
        //     '$type' => $type,
        //     '$rootNamespace' => $rootNamespace,
        // ]);
        $this->c->setOptions([
            'folder' => Str::of($this->suite)->title()->toString(),
        ]);

        if (in_array($type, [
            'model-case',
        ])) {
            $this->prepareOptionsForModelCase($options);
        } elseif (in_array($type, [
            'api-test-case',
            'playground-api-test-case',
            'playground-resource-test-case',
            'playground-model-test-case',
            'resource-test-case',
            'test-case',
        ])) {
            $this->prepareOptionsForTestCase($options);
        } elseif (in_array($type, [
            'playground-request-test-case',
        ])) {
            $this->prepareOptionsForRequestTestCase($options);
        } elseif (in_array($type, [
            'playground-api-controller-test-case',
            'playground-resource-controller-test-case',
        ])) {
            $this->prepareOptionsForControllerTestCase($options);
        } elseif (in_array($type, [
            'playground-api-controller-model-case',
            'playground-resource-controller-model-case',
        ])) {
            $this->prepareOptionsForControllerModelCase($options);
        } elseif (in_array($type, [
            'providers',
            'providers-api',
            'providers-model',
            'providers-resource',
        ])) {
            $this->buildClass_getPackageProviders($type);
        } elseif (in_array($type, [
            'model',
            'playground-api',
            'playground-resource',
            'playground-model',
        ])) {
            $this->prepareOptionsForModels($options);
            $this->prepareOptionsForSuites($options);
        } elseif (in_array($type, [
            'command-about',
        ])) {
            $this->prepareOptionsForAboutCommand($options);
        } elseif (in_array($type, [
            'policy',
        ])) {
            // $this->buildClass_uses_add('PHPUnit/Framework/Attributes/CoversClass');
            $this->buildClass_uses_add(sprintf(
                'Tests\%1$s\%2$s\TestCase',
                Str::of($this->suite)->studly()->toString(),
                Str::of($this->rootNamespace())->trim('\\/')->toString()
            ));
            $this->buildClass_uses_add(sprintf('%1$sPolicies/%2$sPolicy', $this->rootNamespace(), $model));
        }

        // $this->saveConfiguration();

        if (is_string($this->c->name())) {
            // dump([
            //     '__METHOD__' => __METHOD__,
            //     // '$this->c' => $this->c,
            //     '$this->c->name()' => $this->c->name(),
            //     // '$this->searches' => $this->searches,
            //     // '$this->options()' => $this->options(),
            // ]);
            $this->buildClass_uses($this->c->name());
        }

        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$this->c' => $this->c,
        //     '$this->searches' => $this->searches,
        //     '$this->options()' => $this->options(),
        // ]);
    }

    protected function getConfigurationFilename(): string
    {
        $type = $this->c->type();
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$type' => $type,
        // ]);

        $filename = '';

        if (in_array($type, [
            'model',
            'playground-api',
            'playground-resource',
            'playground-model',
        ])) {
            $filename = sprintf(
                '%3$s/%1$s.%2$s.json',
                'test',
                Str::of($this->c->suite())->kebab(),
                Str::of($this->c->name())->before('Test')->kebab(),
            );
            // $filename = sprintf(
            //     '%1$s.%2$s.%3$s.json',
            //     'test',
            //     Str::of($this->c->suite())->kebab(),
            //     Str::of($this->c->name())->before('Test')->kebab(),
            // );
        } elseif (in_array($type, [
            'policy',
        ])) {
            $filename = sprintf(
                '%3$s/%1$s.%2$s.policy.json',
                'test',
                Str::of($this->c->suite())->kebab(),
                Str::of($this->c->model())->kebab(),
            );
            // $filename = sprintf(
            //     '%1$s.%2$s.%3$s.json',
            //     'test',
            //     Str::of($this->c->suite())->kebab(),
            //     Str::of($this->c->name())->before('Test')->kebab(),
            // );
        } elseif (in_array($type, [
            'playground-api-controller-model-case',
            'playground-resource-controller-model-case',
        ])) {
            $filename = sprintf(
                '%1$s/%2$s.%3$s.json',
                Str::of($this->c->model())->kebab(),
                'test',
                'case',
            );
        } elseif (in_array($type, [
            'model-case',
        ])) {
            $filename = sprintf(
                'test.%1$s.model.json',
                Str::of($this->c->suite())->kebab(),
            );
        } elseif (in_array($type, [
            'playground-request-test-case',
        ])) {
            $filename = sprintf(
                'test.%1$s.request.json',
                Str::of($this->c->suite())->kebab(),
            );
        } elseif (in_array($type, [
            'command-about',
        ])) {
            $filename = 'test.command.about.json';
        } elseif (in_array($type, [
            'playground-api-controller-test-case',
            'playground-resource-controller-test-case',
        ])) {
            $filename = 'test.controller.json';
        } elseif (in_array($type, [
            'playground-service-provider-policies',
        ])) {
            $filename = 'test.service-provider.json';
        } elseif (in_array($type, [
            'providers',
            'providers-api',
            'providers-model',
            'providers-resource',
        ])) {
            $filename = sprintf(
                'test.%1$s.json',
                Str::of($type)->kebab(),
            );
        } elseif (in_array($type, [
            'test-case',
        ])) {
            $filename = sprintf(
                'test.%1$s.json',
                Str::of($this->c->suite())->kebab(),
            );
        } else {
            $filename = sprintf(
                '%1$s/%2$s.%3$s.json',
                Str::of($this->c->name())->before('Test')->kebab(),
                'test',
                Str::of($this->c->suite())->kebab(),
            );
        }

        return $filename;
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     */
    protected function qualifyClass($name): string
    {
        $type = $this->c->type();
        // return parent::qualifyClass($name);
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$type' => $type,
        // ]);

        if (in_array($type, [
            'model',
        ])) {
            $this->c->setOptions([
                'class' => 'ModelTest',
            ]);
        } elseif (in_array($type, [
            'policy',
        ])) {
            $this->c->setOptions([
                'class' => 'PolicyTest',
            ]);
        } elseif ($type === 'model-case') {
            $this->c->setOptions([
                'class' => 'ModelCase',
            ]);
        } elseif (in_array($type, [
            'api-test-case',
            'playground-api-test-case',
            'playground-resource-test-case',
            'playground-model-test-case',
            'resource-test-case',
            'test-case',
        ])) {
            $this->c->setOptions([
                'class' => 'TestCase',
            ]);
        } elseif (in_array($type, [
            'playground-request-test-case',
        ])) {
            $this->c->setOptions([
                'class' => 'RequestTestCase',
            ]);
        } elseif (in_array($type, [
            'playground-api-controller-test-case',
            'playground-resource-controller-test-case',
        ])) {
            $this->c->setOptions([
                'class' => 'TestCase',
            ]);
        } elseif (in_array($type, [
            'playground-api-controller-model-case',
            'playground-resource-controller-model-case',
        ])) {
            $this->c->setOptions([
                'class' => Str::of($this->c->model())->finish('TestCase')->toString(),
            ]);
        } elseif (in_array($type, [
            'providers',
            'providers-api',
            'providers-model',
            'providers-resource',
        ])) {
            $this->c->setOptions([
                'class' => 'PackageProviders',
            ]);
        } elseif (in_array($type, [
            'command',
            'command-about',
        ])) {
            $this->c->setOptions([
                'class' => 'CommandTest',
            ]);
        } else {
            $this->c->setOptions([
                'class' => 'InstanceTest',
            ]);
        }

        $this->searches['class'] = $this->c->class();

        $rootNamespace = $this->rootNamespace();
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$type' => $type,
        //     '$rootNamespace' => $rootNamespace,
        //     '$this->c->class()' => $this->c->class(),
        //     // '$this->options()' => $this->options(),
        // ]);

        if (in_array($type, [
            'model-case',
        ])) {
            return $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\Models';
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$this->c->class();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $suite = $this->c->suite();

        $test = 'test/test.stub';

        $type = $this->getConfigurationType();

        $isApi = $this->hasOption('api') && $this->option('api');
        $isResource = $this->hasOption('resource') && $this->option('resource');

        if (in_array($type, [
            'model',
        ])) {
            if ($this->model?->playground()) {
                $test = 'test/model/playground.stub';
            } else {
                $test = 'test/test.stub';
            }
        } elseif (in_array($type, [
            'providers',
            'providers-api',
            'providers-resource',
        ])) {
            $test = 'test/playground-trait-providers.stub';
        } elseif (in_array($type, [
            'providers-model',
        ])) {
            $test = 'test/playground-trait-providers-models.stub';
        } elseif (in_array($type, [
            'policy',
        ])) {
            $test = 'test/policy/PolicyTest.php.stub';
        } elseif (in_array($type, [
            'api-test-case',
            'playground-api-test-case',
            'playground-resource-test-case',
            'resource-test-case',
            'test-case',
        ])) {
            if ($suite === 'feature') {
                $test = 'test/case/playground-resource-feature.stub';
            } else {
                $test = 'test/case/playground-resource-unit.stub';
            }
        } elseif (in_array($type, [
            'playground-model-test-case',
        ])) {
            if ($suite === 'feature') {
                $test = 'test/case/playground-model-feature.stub';
            } else {
                $test = 'test/case/playground-resource-unit.stub';
            }
        } elseif (in_array($type, [
            'playground-request-test-case',
        ])) {
            $test = 'test/case/playground-request.stub';
        } elseif (in_array($type, [
            'playground-resource-controller-test-case',
        ])) {
            $test = 'test/controller/playground-resource-feature-case.stub';
        } elseif (in_array($type, [
            'command-about',
        ])) {
            // dd([
            //     '__METHOD__' => __METHOD__,
            //     '$this->c' => $this->c,
            //     '$this->options()' => $this->options(),
            // ]);
            if ($isApi || $isResource) {
                $test = 'test/command/about-CommandTest.php.stub';
            } else {
                $test = 'test/command/about-CommandTest.php-models.stub';
            }
        } elseif (in_array($type, [
            'playground-api-controller-model-case',
            'playground-resource-controller-model-case',
        ])) {
            $test = 'test/controller/playground-resource-feature-model-case.stub';
        } elseif (in_array($type, [
            'playground-service-provider-policies',
        ])) {
            $test = 'test/service-provider/playground-policies.stub';
        } elseif (in_array($type, [
            'model-case',
        ])) {
            if ($suite === 'feature') {
                $test = 'test/model/playground-base-feature.stub';
            } else {
                $test = 'test/model/playground-base-unit.stub';
            }
        }

        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$test' => $test,
        //     '$type' => $type,
        //     '$suite' => $suite,
        // ]);

        return $this->resolveStubPath($test);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        $testSuiteSpace = Str::of(
            Str::of($this->suite)->studly()->toString()
        )->start('Tests/')->finish('/')->toString();

        $namespace = Str::of(
            $this->parseClassConfig($rootNamespace)
        )->start($testSuiteSpace)->toString();

        $type = $this->c->type();
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$type' => $type,
        //     '$testSuiteSpace' => $testSuiteSpace,
        //     '$rootNamespace' => $rootNamespace,
        //     '$namespace' => $namespace,
        //     '$this->c' => $this->c->toArray(),
        //     '$this->c->namespace()' => $this->c->namespace(),
        //     '$this->options()' => $this->options(),
        // ]);

        // // Set the suite on the namespace.
        // $namespace = Str::of(
        //     Str::of($this->suite)->studly()->toString()
        // )->prepend('Tests\\')->toString();

        // if ($rootNamespace && is_string($rootNamespace)) {
        //     $namespace = Str::of($namespace)
        //         ->finish('\\')
        //         ->append($this->parseClassInput($rootNamespace))
        //         ->toString();
        // }

        if (in_array($type, [
            'controller',
            'request',
        ])) {
            $namespace = Str::of($namespace)->finish(
                '/'.Str::of($type)->plural()->studly()->toString()
            )->toString();
        } elseif (in_array($type, [
            'model-case',
        ])) {
            $namespace = Str::of(
                $namespace
            )->finish('/Models')->toString();
        } elseif (in_array($type, [
            'playground-request-test-case',
        ])) {
            $namespace = Str::of(
                $namespace
            )->finish('/Http/Requests')->toString();
        } elseif (in_array($type, [
            'playground-api-test-case',
            'playground-resource-test-case',
            'playground-model-test-case',
        ])) {
            // $namespace;
        } elseif (in_array($type, [
            'playground-api-controller-test-case',
            'playground-api-controller-model-case',
            'playground-resource-controller-test-case',
            'playground-resource-controller-model-case',
        ])) {
            $namespace = Str::of(
                $namespace
            )->finish('/Http/Controllers')->toString();
        } elseif (in_array($type, [
            'playground-service-provider-policies',
        ])) {
            $namespace = Str::of(
                $namespace
            )->finish('/ServiceProvider')->toString();
        } elseif (in_array($type, [
            'model',
        ])) {
            $namespace = Str::of($namespace)->finish(
                '/'.Str::of($this->c->name())->studly()->toString()
            )->toString();
        } elseif (in_array($type, [
            'policy',
        ])) {
            // Tests\Unit\Playground\Matrix\Resource\Policies\BacklogPolicy
            $namespace = Str::of($namespace)->finish('/Policies')->finish(
                Str::of($this->c->model())->studly()->start('/')->finish('Policy')->toString()
            )->toString();
        } else {
            //
        }

        $this->c->setNamespace($namespace);

        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$type' => $type,
        //     '$rootNamespace' => $rootNamespace,
        //     '$namespace' => $namespace,
        //     // '$this->c' => $this->c,
        //     '$this->c->namespace()' => $this->c->namespace(),
        //     // '$this->options()' => $this->options(),
        // ]);

        $this->searches['namespace'] = $this->parseClassInput($this->c->namespace());

        return $namespace;
    }

    /**
     * Get the console command arguments.
     *
     * @return array<int, mixed>
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();

        $options[] = ['suite', null, InputOption::VALUE_OPTIONAL, 'The test suite: unit|feature|acceptance'];
        $options[] = ['covers', null, InputOption::VALUE_NONE, 'Use CoversClass for code coverage'];
        $options[] = ['api', null, InputOption::VALUE_NONE, 'The test is for APIs'];
        $options[] = ['resource', null, InputOption::VALUE_NONE, 'The test is for resources'];

        return $options;
    }

    protected function folder(): string
    {
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$this->c->name()' => $this->c->name(),
        //     '$this->folder' => $this->folder,
        // ]);
        if (empty($this->folder) && is_string($this->c->name())) {

            if (in_array($this->c->type(), [
                'providers',
                'providers-api',
                'providers-model',
                'providers-resource',
                'api-test-case',
                'playground-api-test-case',
                'playground-resource-test-case',
                'playground-model-test-case',
                'resource-test-case',
                'test-case',
            ])) {
                $this->folder = sprintf(
                    '%1$s/%2$s',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString()
                );
            } elseif (in_array($this->c->type(), [
                'model-case',
            ])) {
                $this->folder = sprintf(
                    '%1$s/%2$s/Models',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString()
                );
            } elseif (in_array($this->c->type(), [
                'playground-request-test-case',
            ])) {
                $this->folder = sprintf(
                    '%1$s/%2$s/Http/Requests',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString()
                );
            } elseif (in_array($this->c->type(), [
                'playground-api-controller-test-case',
                'playground-resource-controller-test-case',
            ])) {
                $this->folder = sprintf(
                    '%1$s/%2$s/Http/Controllers',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString()
                );
            } elseif (in_array($this->c->type(), [
                'playground-api-controller-model-case',
                'playground-resource-controller-model-case',
            ])) {
                $this->folder = sprintf(
                    '%1$s/%2$s/Http/Controllers',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString()
                );
            } elseif (in_array($this->c->type(), [
                'playground-service-provider-policies',
            ])) {
                $this->folder = sprintf(
                    '%1$s/%2$s/ServiceProvider',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString()
                );
            } elseif (in_array($this->c->type(), [
                'model',
                'playground-model',
            ])) {
                $this->folder = sprintf(
                    '%1$s/%2$s/Models/%3$s',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString(),
                    Str::of($this->c->name())->studly()->toString()
                );
            } elseif (in_array($this->c->type(), [
                'policy',
            ])) {
                $this->folder = sprintf(
                    '%1$s/%2$s/Policies/%3$sPolicy',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString(),
                    Str::of($this->c->model())->studly()->toString()
                );
            } elseif (in_array($this->c->type(), [
                'command-about',
            ])) {
                $this->folder = sprintf(
                    '%1$s/%2$s/Console/Commands/About',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString()
                );
            } else {
                $this->folder = sprintf(
                    '%1$s/%2$s/%3$s',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString(),
                    Str::of($this->c->name())->studly()->toString()
                );
            }
        }

        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$this->c->type()' => $this->c->type(),
        //     '$this->folder' => $this->folder,
        // ]);

        return $this->folder;
    }
}
