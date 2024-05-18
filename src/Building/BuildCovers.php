<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Playground\Make\Test\Building;

use Illuminate\Support\Str;

/**
 * \Playground\Make\Test\Building\Test\BuildCovers
 */
trait BuildCovers
{
    /**
     * @var array<int, string>
     */
    protected array $build_covers = [];

    /**
     * @var array<int, string>
     */
    protected array $build_covers_controllers = [];

    /**
     * @var array<int, string>
     */
    protected array $build_covers_models = [];

    /**
     * @var array<int, string>
     */
    protected array $build_covers_policies = [];

    /**
     * @var array<int, string>
     */
    protected array $build_covers_requests = [];

    /**
     * @var array<int, string>
     */
    protected array $build_covers_resources = [];

    public function addCovers(): self
    {
        $this->searches['covers_class'] = '';

        $this->buildClass_uses_add('PHPUnit/Framework/Attributes/CoversClass');

        $this->addCovers_controllers();
        $this->addCovers_policies();
        $this->addCovers_requests();
        $this->addCovers_resources();
        // use PHPUnit\Framework\Attributes\CoversClass;
        // use Playground\Matrix\Resource\Http\Controllers\MilestoneController;
        // use Playground\Matrix\Resource\Http\Requests;
        // use Playground\Matrix\Resource\Http\Resources;

        // #[CoversClass(MilestoneController::class)]
        // #[CoversClass(Resources\Backlog::class)]
        // #[CoversClass(Resources\BacklogCollection::class)]
        // #[CoversClass(BacklogPolicy::class)]

        // $covers_class = sprintf(
        //     '%1$s\'%2$s\',%3$s',
        //     str_repeat(static::INDENT, 2),
        //     'id',
        //     PHP_EOL
        // );

        // foreach ($attributes as $attribute => $default) {
        //     if (is_string($attribute) && $attribute) {
        //         $covers_class .= sprintf(
        //             '%1$s\'%2$s\',%3$s',
        //             str_repeat(static::INDENT, 2),
        //             $attribute,
        //             PHP_EOL
        //         );
        //     }
        // }

        // if (! empty($covers_class)) {
        //     $this->searches['covers_class'] = rtrim($covers_class);
        // }

        foreach ($this->build_covers_controllers as $class) {
            if (! in_array($class, $this->build_covers)) {
                $this->build_covers[] = $class;
            }
        }

        foreach ($this->build_covers_models as $class) {
            if (! in_array($class, $this->build_covers)) {
                $this->build_covers[] = $class;
            }
        }

        foreach ($this->build_covers_policies as $class) {
            if (! in_array($class, $this->build_covers)) {
                $this->build_covers[] = $class;
            }
        }

        foreach ($this->build_covers_requests as $class) {
            if (! in_array($class, $this->build_covers)) {
                $this->build_covers[] = $class;
            }
        }

        foreach ($this->build_covers_resources as $class) {
            if (! in_array($class, $this->build_covers)) {
                $this->build_covers[] = $class;
            }
        }

        $covers_class = '';
        foreach ($this->build_covers as $class) {
            $covers_class .= $class.PHP_EOL;
        }

        if (! empty($covers_class)) {
            $this->searches['covers_class'] = PHP_EOL.rtrim($covers_class);
        }

        dump([
            '__METHOD__' => __METHOD__,
            // '$covers_class' => $covers_class,
            '$this->c->type()' => $this->c->type(),
            '$this->c' => $this->c,
            '$this->searches' => $this->searches,
            '$this->options()' => $this->options(),
            '$this->build_covers_controllers' => $this->build_covers_controllers,
            '$this->build_covers_models' => $this->build_covers_models,
            '$this->build_covers_policies' => $this->build_covers_policies,
            '$this->build_covers_requests' => $this->build_covers_requests,
            '$this->build_covers' => $this->build_covers,
        ]);

        return $this;
    }

    public function addCovers_controllers(): self
    {
        // use Playground\Matrix\Resource\Http\Controllers\MilestoneController;

        $model = $this->c->model();
        if (in_array($this->c->type(), [
            'playground-resource-controller-model-case',
        ])) {

            if ($model) {
                $this->buildClass_uses_add(sprintf(
                    '%1$sHttp/Controllers/%2$sController',
                    $this->rootNamespace(),
                    $model
                ));

                $this->build_covers_controllers[] = sprintf(
                    '#[CoversClass(%1$sController::class)]',
                    $model,
                );
            }
        }

        dump([
            '__METHOD__' => __METHOD__,
            '$model' => $model,
            '$this->c->name()' => $this->c->name(),
            '$this->c->type()' => $this->c->type(),
            '$this->c' => $this->c,
            '$this->build_covers_controllers' => $this->build_covers_controllers,
            // '$this->options()' => $this->options(),
        ]);

        return $this;
    }

    public function addCovers_policies(): self
    {
        // use Playground\Matrix\Resource\Policies\BacklogPolicy;

        $model = $this->c->model();
        if (in_array($this->c->type(), [
            'playground-resource-controller-model-case',
        ])) {

            if ($model) {
                $this->buildClass_uses_add(sprintf('%1$sPolicies/%2$sPolicy', $this->rootNamespace(), $model));

                $this->build_covers_policies[] = sprintf(
                    '#[CoversClass(%1$sPolicy::class)]',
                    $model,
                );
            }
        }

        dump([
            '__METHOD__' => __METHOD__,
            '$model' => $model,
            '$this->c->name()' => $this->c->name(),
            '$this->c->type()' => $this->c->type(),
            '$this->c' => $this->c,
            '$this->build_covers_policies' => $this->build_covers_policies,
            // '$this->options()' => $this->options(),
        ]);

        return $this;
    }

    public function addCovers_requests(): self
    {
        /**
         * @var array<int, string> $requests
         */
        $requests = [];

        $model = $this->c->model();
        if (in_array($this->c->type(), [
            'playground-resource-controller-model-case',
        ])) {
            $requests[] = 'CreateRequest';
            $requests[] = 'DestroyRequest';
            $requests[] = 'EditRequest';
            $requests[] = 'IndexRequest';
            $requests[] = 'LockRequest';
            $requests[] = 'RestoreRequest';
            // $requests[] = 'RestoreRevisionRequest';
            // $requests[] = 'RevisionsRequest';
            $requests[] = 'ShowRequest';
            // $requests[] = 'ShowRevisionRequest';
            $requests[] = 'StoreRequest';
            $requests[] = 'UnlockRequest';
            $requests[] = 'UpdateRequest';

            $this->buildClass_uses_add(sprintf('%1$sHttp/Requests', $this->rootNamespace()));
        }

        if ($model) {
            foreach ($requests as $request) {
                $this->build_covers_requests[] = sprintf(
                    '#[CoversClass(Requests\%1$s\%2$s::class)]',
                    $model,
                    $request,
                );
            }
        }

        dump([
            '__METHOD__' => __METHOD__,
            '$model' => $model,
            '$requests' => $requests,
            '$this->c->name()' => $this->c->name(),
            '$this->c->type()' => $this->c->type(),
            '$this->c' => $this->c,
            '$this->build_covers_requests' => $this->build_covers_requests,
            // '$this->options()' => $this->options(),
        ]);

        return $this;
    }

    public function addCovers_resources(): self
    {
        /**
         * @var array<int, string> $resources
         */
        $resources = [];

        $model = $this->c->model();
        if (in_array($this->c->type(), [
            'playground-resource-controller-model-case',
        ])) {
            if ($model) {
                $resources[] = $model;
                $resources[] = $model.'Collection';

                $this->buildClass_uses_add(sprintf('%1$sHttp/Resources', $this->rootNamespace()));
            }
        }

        if ($model) {
            foreach ($resources as $resource) {
                $this->build_covers_resources[] = sprintf(
                    '#[CoversClass(Resources\%1$s::class)]',
                    $resource,
                );
            }
        }

        dump([
            '__METHOD__' => __METHOD__,
            '$model' => $model,
            '$resources' => $resources,
            '$this->c->name()' => $this->c->name(),
            '$this->c->type()' => $this->c->type(),
            '$this->c' => $this->c,
            '$this->build_covers_requests' => $this->build_covers_resources,
            // '$this->options()' => $this->options(),
        ]);

        return $this;
    }
}
