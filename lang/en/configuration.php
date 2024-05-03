<?php
/**
 * Playground
 */

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Configuration Language Lines
    |--------------------------------------------------------------------------
    |
    |
    */

    'Primary.addClassTo.property.required' => 'Adding a class [:class] requires both a property: [INVALID: :property] and an fqdn [:fqdn].',
    'Primary.addClassTo.fqdn.required' => 'Adding a class [:class] requires both a property: [:property] and an fqdn [INVALID: :fqdn].',
    'Primary.addClassTo.property.missing' => 'Adding a class [:class] requires the property to exist [MISSING: :property] to exist to add the fqdn [:fqdn]',

    'addClassTo.property.required' => 'Adding a class [:class] requires both a property: [INVALID: :property] and an fqdn [:fqdn].',
    'addClassTo.fqdn.required' => 'Adding a class [:class] requires both a property: [:property] and an fqdn [INVALID: :fqdn].',
    'addClassTo.property.missing' => 'Adding a class [:class] requires the property to exist [MISSING: :property] to exist to add the fqdn [:fqdn]',

    'addClassFileTo.property.required' => 'Adding a class [:class] requires both a property: [INVALID: :property] and a file [:file].',
    'addClassFileTo.file.required' => 'Adding a class [:class] requires both a property: [:property] and a file [INVALID: :file].',
    'addClassFileTo.property.missing' => 'Adding a class [:class] requires the property [MISSING: :property] to exist to add a file [:file]',

    'addMappedClassTo.property.required' => 'Adding a class [:class] requires a property: [INVALID: :property], a key: [:key] and an value [:value].',
    'addMappedClassTo.key.required' => 'Adding a mapped class [:class] requires a property: [:property], a key: [INVALID: :key] and a value [:value].',
    'addMappedClassTo.value.required' => 'Adding a mapped class [:class] requires a property: [:property], a key: [:key] and a value [INVALID: :value].',
    'addMappedClassTo.property.missing' => 'Adding a mapped class [:class] requires the property to exist: [MISSING: :property], a key: [:key] and a value [:value].',

    'addToUse.class.required' => 'Adding a use class [:class] requires a valid class: [INVALID: :use_class] and an optional key [:key].',
];
