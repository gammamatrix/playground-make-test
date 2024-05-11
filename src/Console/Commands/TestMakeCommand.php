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
        'module' => '',
        'module_slug' => '',
        'namespace' => '',
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
        // Service Providers
        'providers',
        'providers-api',
        'providers-model',
        'providers-resource',
        // Models
        'model-case',
        'model',
        'playground-model',
        // APIs
        'playground-api',
        // Resources
        'playground-resource',
    ];

    public function prepareOptions(): void
    {
        $options = $this->options();
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

            // } elseif ($type === 'controller') {
            // } elseif ($type === 'playground-resource-index') {
            // } elseif ($type === 'playground-resource') {
        } else {
        }

        // $this->saveConfiguration();

        $this->applyConfigurationToSearch();
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

        $filename = '';

        if (in_array($type, [
            'model',
            'playground-api',
            'playground-resource',
            'playground-model',
        ])) {
            $type = 'test';
            $filename = sprintf(
                '%1$s.%2$s.%3$s.json',
                $type,
                Str::of($this->c->suite())->kebab(),
                Str::of($this->c->name())->before('Test')->kebab(),
            );
        } elseif (in_array($type, [
            'model-case',
        ])) {
            $filename = sprintf(
                'test.%1$s.model.json',
                Str::of($this->c->suite())->kebab(),
            );
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
        } else {
            $type = 'test';
            $filename = sprintf(
                '%1$s/%2$s.%3$s.json',
                Str::of($this->c->name())->before('Test')->kebab(),
                $type,
                Str::of($this->c->suite())->kebab(),
            );
        }

        return $filename;
        // return ! is_string($this->c->name()) ? '' : sprintf(
        //     'test.%1$s.%2$s%3$s%4$s.json',
        //     Str::of($this->c->suite())->kebab(),
        //     Str::of($type)->kebab(),
        //     $type ? '.' : '',
        //     Str::of($this->c->name())->kebab()
        // );
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     */
    protected function qualifyClass($name): string
    {
        $type = $this->getConfigurationType();
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
        } elseif ($type === 'model-case') {
            $this->c->setOptions([
                'class' => 'ModelCase',
            ]);
            // } elseif ($type === 'controller') {
            // } elseif ($type === 'playground-resource-index') {
            // } elseif ($type === 'playground-resource') {
        } elseif (in_array($type, [
            'providers',
            'providers-api',
            'providers-model',
            'providers-resource',
        ])) {
            $this->c->setOptions([
                'class' => 'PackageProviders',
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
        //     '$this->options()' => $this->options(),
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
            'providers-model',
            'providers-resource',
        ])) {
            $test = 'test/playground-trait-providers.stub';
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
        $type = $this->c->type();

        // Set the suite on the namespace.
        $namespace = Str::of(
            Str::of($this->suite)->studly()->toString()
        )->prepend('Tests\\')->toString();

        if ($rootNamespace && is_string($rootNamespace)) {
            $namespace = Str::of($namespace)
                ->finish('\\')
                ->append($this->parseClassInput($rootNamespace))
                ->toString();
        }

        if (in_array($type, [
            'controller',
            'request',
            'policy',
        ])) {
            $namespace = Str::of($namespace)
                ->finish('\\')
                ->append(Str::of($type)->plural()->studly()->toString())
                ->toString();
        } elseif (in_array($type, [
            'model-case',
        ])) {
            $namespace = Str::of($namespace)
                ->finish('\\')
                // ->append(Str::of($type)->plural()->studly()->toString())
                // ->finish('\\')
                ->append('Models')
                ->toString();
        } elseif (in_array($type, [
            'model',
        ])) {
            $namespace = Str::of($namespace)
                ->finish('\\')
                // ->append(Str::of($type)->plural()->studly()->toString())
                // ->finish('\\')
                ->append(Str::of($this->c->name())->studly()->toString())
                ->toString();
        }

        // $name = $this->c->name();
        // if ($name && ! in_array($type, [
        //     'model-case',
        //     'providers',
        //     'providers-api',
        //     'providers-model',
        //     'providers-resource',
        // ])) {
        //     // $namespace = Str::of($namespace)
        //     //     ->finish('\\')
        //     //     // ->append(Str::of($name)->studly()->toString())
        //     //     ->toString();
        // }
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$type' => $type,
        //     '$rootNamespace' => $rootNamespace,
        //     '$namespace' => $namespace,
        //     '$this->c' => $this->c,
        //     '$this->options()' => $this->options(),
        // ]);

        $this->c->setNamespace($namespace);
        $this->searches['namespace'] = $this->c->namespace();
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

        return $options;
    }

    protected function folder(): string
    {
        if (empty($this->folder) && is_string($this->c->name())) {

            if (in_array($this->c->type(), [
                'providers',
                'providers-api',
                'providers-model',
                'providers-resource',
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
                'model',
                'playground-model',
            ])) {
                $this->folder = sprintf(
                    '%1$s/%2$s/Models/%3$s',
                    $this->getDestinationPath(),
                    Str::of($this->suite)->studly()->toString(),
                    Str::of($this->c->name())->studly()->toString()
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

        return $this->folder;
    }
}
