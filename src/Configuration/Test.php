<?php

declare(strict_types=1);
/**
 * Playground
 */

namespace Playground\Make\Test\Configuration;

use Playground\Make\Configuration\PrimaryConfiguration;

/**
 * \Playground\Make\Test\Configuration\Test
 */
class Test extends PrimaryConfiguration
{
    protected string $extends = '';

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
        'model_camel' => '',
        'model_camels' => '',
        'model_kebab' => '',
        'model_kebabs' => '',
        'model_label' => '',
        'model_labels' => '',
        'model_route_param' => '',
        'model_lower' => '',
        'model_lowers' => '',
        'model_slug' => '',
        'model_slugs' => '',
        'model_snake' => '',
        'model_snakes' => '',
        'model_studly' => '',
        'model_studlies' => '',
        'model_variable' => '',
        'model_variable_plural' => '',
        'model_variables' => '',
        'module' => '',
        'module_label' => '',
        'module_labels' => '',
        'module_slug' => '',
        'module_slugs' => '',
        'name' => '',
        'names' => '',
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

        if (! empty($options['model_variable_plural'])
            && is_string($options['model_variable_plural'])
        ) {
            $this->model_variable_plural = $options['model_variable_plural'];
        }

        if (! empty($options['model_variables'])
            && is_string($options['model_variables'])
            && empty($this->model_variable_plural)
        ) {
            $this->model_variable_plural = $options['model_variables'];
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

    public function model_variable_plural(): string
    {
        return $this->model_variable_plural;
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
