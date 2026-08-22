<?php

declare(strict_types=1);
/**
 * Playground
 */

namespace Playground\Make\Test\Configuration;

use Illuminate\Support\Str;
use Playground\Make\Configuration\PrimaryConfiguration;

/**
 * \Playground\Make\Test\Configuration\Test
 */
class Test extends PrimaryConfiguration
{
    protected string $extends = '';

    protected string $model_fqdn = '';

    protected string $model_variable = '';

    protected string $model_variable_plural = '';

    /**
     * @var array<int, string>
     */
    protected array $package_providers = [];

    protected string $suite = '';

    /**
     * @var array<string, string>
     */
    protected array $models = [];

    protected bool $withCovers = false;

    /**
     * @var array<string, mixed>
     */
    protected $properties = [
        'class' => '',
        'config' => '',
        'extends' => '',
        'fqdn' => '',
        'model' => '',
        'model_fqdn' => '',
        'model_variable' => '',
        'model_variable_plural' => '',
        'module' => '',
        'module_slug' => '',
        'name' => '',
        'namespace' => '',
        'organization' => '',
        'package' => '',
        'playground' => false,
        'suite' => '',
        'type' => '',
        'uses' => [],
        'models' => [],
        'package_providers' => [],
    ];

    /**
     * @param  array<string, mixed>  $options
     */
    public function setOptions(array $options = []): self
    {
        parent::setOptions($options);

        if (! empty($options['model_fqdn'])
            && is_string($options['model_fqdn'])
        ) {
            $this->model_fqdn = $options['model_fqdn'];
        }

        if (! empty($options['model_variable'])
            && is_string($options['model_variable'])
        ) {
            $this->model_variable = $options['model_variable'];
        }

        if (! empty($options['model_variable_plural'])
            && is_string($options['model_variable_plural'])
        ) {
            $this->model_variable_plural = $options['model_variable_plural'];
        }

        if (! empty($options['suite'])
            && is_string($options['suite'])
        ) {
            $this->suite = $options['suite'];
        }

        if (! empty($options['models'])
            && is_array($options['models'])
        ) {
            foreach ($options['models'] as $key => $file) {
                $this->addMappedClassTo('models', $key, $file);
            }
        }

        if (! empty($options['package_providers'])
            && is_array($options['package_providers'])
        ) {
            foreach ($options['package_providers'] as $provider) {
                $this->addClassTo('package_providers', $provider);
            }
        }

        if (array_key_exists('withCovers', $options)) {
            $this->withCovers = ! empty($options['withCovers']);
        }

        return $this;
    }

    public function model_fqdn(): string
    {
        return $this->model_fqdn;
    }

    public function model_variable(): string
    {
        return $this->model_variable;
    }

    public function model_variable_plural(): string
    {
        return $this->model_variable_plural;
    }

    public function module_slug(): string
    {
        return $this->module_slug;
        // return Str::of($this->module_slug)->replace('-', '_')->toString();
    }

    /**
     * @return array<string, string>
     */
    public function models(): array
    {
        return $this->models;
    }

    /**
     * @return array<int, string>
     */
    public function package_providers(): array
    {
        return $this->package_providers;
    }

    public function suite(): string
    {
        return $this->suite;
    }

    public function withCovers(): bool
    {
        return $this->withCovers;
    }
}
