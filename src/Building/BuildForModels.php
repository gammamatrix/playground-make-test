<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Playground\Make\Test\Building;

/**
 * \Playground\Make\Test\Building\Test\BuildForModels
 */
trait BuildForModels
{
    /**
     * @param array<string, mixed> $options
     */
    public function prepareOptionsForModels(array $options = []): void
    {
        $this->initModel($this->c->skeleton());
        if (! $this->model) {
            throw new \RuntimeException('Provide a [--model-file].');
        }

        // The FQDN, from the model, is the source of truth.
        $model_fqdn = $this->model->fqdn();
        if (! $model_fqdn) {
            $model_fqdn = $this->c->model_fqdn();
        }

        $this->c->setOptions([
            'model_fqdn' => $model_fqdn,
        ]);

        $this->searches['model_fqdn'] = $model_fqdn ? $this->parseClassInput($model_fqdn) : 'ReplaceFqdn';
    }

    /**
     * @param array<string, mixed> $options
     */
    public function prepareOptionsForModelCase(array $options = []): void
    {
        $rootNamespace = $this->rootNamespace();

        if (in_array($this->suite, [
            'acceptance',
            'feature',
        ])) {
            $this->buildClass_uses_add(sprintf(
                'Tests\\Unit\\%1$s\\PackageProviders',
                $rootNamespace
            ));
            $this->c->setOptions([
                'extends' => 'Playground/Test/Feature/Models/ModelCase as BaseModelCase',
            ]);
        } else {
            $this->buildClass_uses_add(sprintf(
                'Tests\\Unit\\%1$s\\PackageProviders',
                $rootNamespace
            ));
            $this->c->setOptions([
                'extends' => 'Playground/Test/Unit/Models/ModelCase as BaseModelCase',
            ]);
        }
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     // '$options' => $options,
        //     '$rootNamespace' => $rootNamespace,
        //     '$this->c->uses()' => $this->c->uses(),
        //     '$this->c->suite()' => $this->c->suite(),
        //     // '$this->options()' => $this->options(),
        // ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function prepareOptionsForSuites(array $options = []): void
    {
        $extends = 'ModelCase';
        // dd([
        //     '__METHOD__' => __METHOD__,
        //     '$options' => $options,
        //     'this->model?->module()' => $this->model?->module(),
        //     'this->model?->namespace()' => $this->model?->namespace(),
        //     'this->c->namespace()' => $this->c->namespace(),
        //     '$this->c->suite()' => $this->c->suite(),
        //     // '$this->options()' => $this->options(),
        // ]);

        if (in_array($this->suite, [
            'acceptance',
            'feature',
        ]) && $this->model?->module()) {

            $this->buildClass_uses_add(sprintf(
                'Tests\Feature\%1$s\Models\ModelCase',
                $this->parseClassInput($this->model->namespace())
            ));

        } elseif ($this->model?->module()) {

            $this->buildClass_uses_add(sprintf(
                'Tests\Unit\%1$s\Models\ModelCase',
                $this->parseClassInput($this->model->namespace())
            ));
        }

        $this->c->setOptions([
            'extends' => $extends,
        ]);

        $this->searches['extends'] = $extends;

        $this->buildClass_hasMany($this->c->type(), $this->suite);
        $this->buildClass_hasOne($this->c->type(), $this->suite);
    }
}
