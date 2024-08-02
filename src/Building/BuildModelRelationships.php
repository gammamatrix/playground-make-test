<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Playground\Make\Test\Building;

use Illuminate\Support\Str;

/**
 * \Playground\Make\Test\Building\Test\BuildModelRelationships
 */
trait BuildModelRelationships
{
    protected function buildClass_hasMany(string $type, string $suite): void
    {
        $model = $this->model;
        $hm = $model?->HasMany();
        $this->searches['hasMany_properties'] = '';
        // dd([
        //     '__METHOD__' => __METHOD__,
        //     '$hm' => $hm,
        // ]);

        if (! $model || ! $hm) {
            return;
        }

        $hasMany_properties = PHP_EOL;

        $docblock = '';

        if (in_array($suite, [
            'unit',
        ])) {
            $docblock = '@var array<int, string> Test has many relationships.';
            foreach ($hm as $HasMany) {
                $hasMany_properties .= sprintf('%1$s\'%2$s\',%3$s', str_repeat(' ', 8), $HasMany->accessor(), PHP_EOL);
            }
        } else {
            $docblock = '@var array<string, array<string, mixed>> Test has many relationships.';
            foreach ($hm as $HasMany) {
                $accessor = $HasMany->accessor();
                $localKey = $HasMany->localKey();
                $foreignKey = $HasMany->foreignKey();
                // $rule = $HasMany->rule();
                $rule = 'create';
                $related = $HasMany->related();
                $related_base = $related ? class_basename($related) : '';
                // dd([
                //     '__METHOD__' => __METHOD__,
                //     '$accessor' => $accessor,
                //     '$localKey' => $localKey,
                //     '$foreignKey' => $foreignKey,
                //     '$rule' => $rule,
                //     '$related' => $related,
                //     '$related_base' => $related_base,
                //     // '$hm' => $hm,
                // ]);

                if (! empty($related_base) && ! empty($related)) {
                    if ($related_base === $related) {
                        $related = sprintf(
                            '\\%1$s\\Models\\%2$s::class',
                            $this->parseClassInput($model->namespace()),
                            $this->parseClassInput($related)
                        );
                    } else {
                        if (Str::of($related)->endsWith('::class')) {
                            $related = '\\'.$this->parseClassInput($related);
                        } else {
                            $related = sprintf(
                                '\\Models\\%1$s::class',
                                $this->parseClassInput($related)
                            );
                        }
                    }
                } else {
                    $related = $this->parseClassInput($related);
                }
                $hasMany_properties .= sprintf('%1$s\'%2$s\' => [%3$s', str_repeat(' ', 8), $accessor, PHP_EOL);
                $hasMany_properties .= sprintf('%1$s\'key\' => \'%2$s\',%3$s', str_repeat(' ', 12), $foreignKey, PHP_EOL);
                $hasMany_properties .= sprintf('%1$s\'modelClass\' => %2$s,%3$s', str_repeat(' ', 12), $related, PHP_EOL);
                $hasMany_properties .= sprintf('%1$s],%2$s', str_repeat(' ', 8), PHP_EOL);

            }

        }
        $hasMany_properties .= str_repeat(' ', 4);

        $this->searches['hasMany_properties'] = <<<PHP_CODE


    /**
     * $docblock
     */
    protected array \$hasMany = [$hasMany_properties];
PHP_CODE;

        $this->searches['hasRelationships'] = 'true';
    }

    protected function buildClass_hasOne(string $type, string $suite): void
    {
        $model = $this->model;
        $ho = $model?->HasOne();
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$this->suite' => $this->suite,
        //     '$model->playground()' => $model->playground(),
        //     '$this->type' => $this->type,
        //     '$type' => $type,
        //     '$suite' => $suite,
        //     'empty($ho)' => empty($ho),
        //     // '$ho' => $ho,
        // ]);
        $this->searches['hasOne_properties'] = '';

        if (! $model || is_null($ho)) {
            return;
        }

        /**
         * @var array<string, array<string, string>> $hasOnes
         */
        $hasOnes = [];

        if ($model->playground() && in_array($type, [
            'model',
        ])) {

            /**
             * @var class-string<\Illuminate\Contracts\Auth\Authenticatable>
             */
            $uc = config('auth.providers.users.model', '\\App\\Models\\User');
            $uc = '\Playground\Models\User::class';

            $hasOnes = [
                'creator' => [
                    'localKey' => 'created_by_id',
                    'rule' => 'create',
                    'related' => $uc,
                ],
                'modifier' => [
                    'localKey' => 'modified_by_id',
                    'rule' => 'first',
                    'related' => $uc,
                ],
                'owner' => [
                    'localKey' => 'owned_by_id',
                    'rule' => 'first',
                    'related' => $uc,
                ],
                'parent' => [
                    'localKey' => 'parent_id',
                    'rule' => 'create',
                    'related' => $model->fqdn(),
                ],
            ];
        }

        foreach ($ho as $HasOne) {
            // dd([
            //     '__METHOD__' => __METHOD__,
            //     '$HasOne' => $HasOne,
            // ]);
            if ($HasOne->accessor()) {
                $hasOnes[$HasOne->accessor()] = [
                    'localKey' => $HasOne->localKey(),
                    'rule' => 'create',
                    'related' => $HasOne->related(),
                ];
            }
        }

        // dd([
        //     '__METHOD__' => __METHOD__,
        //     '$hasOnes' => $hasOnes,
        // ]);
        $hasOne_properties = PHP_EOL;

        $docblock = '';

        if (in_array($suite, [
            'unit',
        ])) {
            $docblock = '@var array<int, string> Test has one relationships.';
            foreach ($hasOnes as $accessor => $meta) {
                $hasOne_properties .= sprintf('%1$s\'%2$s\',%3$s', str_repeat(' ', 8), $accessor, PHP_EOL);
            }
        } else {
            $docblock = '@var array<string, array<string, mixed>> Test has one relationships.';
            foreach ($hasOnes as $accessor => $meta) {
                $localKey = '';
                if (! empty($meta['localKey']) && is_string($meta['localKey'])) {
                    $localKey = $meta['localKey'];
                }
                $rule = 'create';
                if (! empty($meta['rule']) && is_string($meta['rule'])) {
                    $rule = $meta['rule'];
                }
                $related = '';
                if (! empty($meta['related']) && is_string($meta['related'])) {
                    $related = $meta['related'];
                }
                $related_base = $related ? class_basename($related) : '';
                if (! empty($related_base) && ! empty($related)) {
                    if ($related_base === $related) {
                        $related = sprintf(
                            '\\%1$s\\Models\\%2$s::class',
                            $this->parseClassInput($model->namespace()),
                            $this->parseClassInput($related)
                        );
                    } else {
                        if (Str::of($related)->endsWith('::class')) {
                            $related = '\\'.$this->parseClassInput($related);
                        } else {
                            $related = sprintf(
                                '\\%1$s::class',
                                $this->parseClassInput($related)
                            );
                        }
                    }
                } else {
                    $related = $this->parseClassInput($related);
                }
                $hasOne_properties .= sprintf('%1$s\'%2$s\' => [%3$s', str_repeat(' ', 8), $accessor, PHP_EOL);
                $hasOne_properties .= sprintf('%1$s\'key\' => \'%2$s\',%3$s', str_repeat(' ', 12), $localKey, PHP_EOL);
                $hasOne_properties .= sprintf('%1$s\'rule\' => \'%2$s\',%3$s', str_repeat(' ', 12), $rule, PHP_EOL);
                $hasOne_properties .= sprintf('%1$s\'modelClass\' => %2$s,%3$s', str_repeat(' ', 12), $related, PHP_EOL);
                $hasOne_properties .= sprintf('%1$s],%2$s', str_repeat(' ', 8), PHP_EOL);
            }
        }
        $hasOne_properties .= str_repeat(' ', 4);

        $this->searches['hasOne_properties'] = <<<PHP_CODE


    /**
     * $docblock
     */
    protected array \$hasOne = [$hasOne_properties];
PHP_CODE;

        $this->searches['hasRelationships'] = 'true';
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$this->searches' => $this->searches,
        // ]);
    }
}
