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
    protected string $extends = '\Tests\TestCase';

    protected string $model_fqdn = '';

    /**
     * @var array<int, string>
     */
    protected array $package_providers = [];

    protected string $suite = '';

    /**
     * @var array<string, string>
     */
    protected array $models = [];

    /**
     * @var array<string, mixed>
     */
    protected $properties = [
        'class' => '',
        'config' => '',
        'extends' => '\Tests\TestCase',
        'fqdn' => '',
        'model' => '',
        'model_fqdn' => '',
        'module' => '',
        'module_slug' => '',
        'name' => '',
        'namespace' => '',
        'organization' => '',
        'package' => '',
        'suite' => '',
        'type' => '',
        'uses' => [],
        'models' => [],
        'package_providers' => [],
    ];

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options = []): self
    {
        parent::setOptions($options);

        if (! empty($options['model_fqdn'])
            && is_string($options['model_fqdn'])
        ) {
            $this->model_fqdn = $options['model_fqdn'];
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

        return $this;
    }

    public function model_fqdn(): string
    {
        return $this->model_fqdn;
    }

    public function module_slug(): string
    {
        return Str::of($this->module_slug)->replace('-', '_')->toString();
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
}
