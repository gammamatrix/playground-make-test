<?php
/**
 * Playground
 */

declare(strict_types=1);
namespace Tests\Unit\Playground\Make\Test;

/**
 * \Tests\Unit\Playground\Make\Test\FileTrait
 */
trait FileTrait
{
    /**
     * @return array<mixed>
     */
    protected function getResourceFileAsArray(string $type = ''): array
    {
        $file = $this->getResourceFile($type);
        $content = file_exists($file) ? file_get_contents($file) : null;
        $data = $content ? json_decode($content, true) : [];
        return is_array($data) ? $data : [];
    }

    protected function getResourceFile(string $type = ''): string
    {
        $package_base = dirname(dirname(__DIR__));

        if (in_array($type, [
            'model',
            'model-backlog',
            'playground-model',
            'playground-model-resource',
            'playground-model-api',
        ])) {
            $file = sprintf(
                '%1$s/resources/testing/configurations/model.backlog.json',
                $package_base
            );

        } elseif (in_array($type, [
            'test-model',
            'model-crm-contact',
        ])) {
            $file = sprintf(
                '%1$s/resources/testing/configurations/model.crm.contact.json',
                $package_base
            );

        } elseif (in_array($type, [
            'playground-api',
        ])) {
            $file = sprintf(
                '%1$s/resources/testing/configurations/test.playground-api.json',
                $package_base
            );

        } elseif (in_array($type, [
            'playground-api-with-model',
        ])) {
            $file = sprintf(
                '%1$s/resources/testing/configurations/test.playground-api-with-model.json',
                $package_base
            );

        } elseif (in_array($type, [
            'playground-model',
        ])) {
            $file = sprintf(
                '%1$s/resources/testing/configurations/test.playground-model.json',
                $package_base
            );

        } elseif (in_array($type, [
            'playground-resource',
        ])) {
            $file = sprintf(
                '%1$s/resources/testing/configurations/test.playground-resource.json',
                $package_base
            );

        } elseif (in_array($type, [
            'playground-resource-with-model',
        ])) {
            $file = sprintf(
                '%1$s/resources/testing/configurations/test.playground-resource-with-model.json',
                $package_base
            );

        } elseif (in_array($type, [
            'test-model',
            'model-crm-contact',
        ])) {
            $file = sprintf(
                '%1$s/resources/testing/configurations/model.crm.contact.json',
                $package_base
            );

        } else {
            $file = sprintf(
                '%1$s/resources/testing/empty.json',
                $package_base
            );
        }
        // dump([
        //     '__METHOD__' => __METHOD__,
        //     '$file' => $file,
        // ]);

        return $file;
    }
}
