==========
Quickstart
==========

* Choose FileLoader
* Load optionally a default config file
* Choose a MetaDataLoader
* Choose a MetaDataFactory
* Load metadata for class

.. code-block:: php

    use a15l\serialization\metadata\factory\MetadataFactory;
    use a15l\serialization\metadata\loader\file\JSONLoader;
    use a15l\serialization\metadata\loader\LazyMetadataLoader;

    // select the desired file loader (XML,JSON,PHP)
    $loader = new JSONLoader('config-dir');
    // provide optionally a default configuration
    $defaultConfig = $loader->getClassMetadataConfig('metadata.defaults');
    // define the metadata loader
    $lazyLoader = new LazyMetadataLoader($loader);
    // create the factory
    $factory = new MetadataFactory($lazyLoader, $defaultConfig);

    // get the metadata for class
    $data = $factory->getClassMetadata('FQCN');

File Loaders
------------

There are three supported file formats: XML, PHP, JSON. Each format requires its own File loader.
The constructor of a File Loader requires a config directory and an optional file suffix.

.. note::

    If the File Loader doesn't finds the requested file a empty array will be returned

Example usage:

.. code-block:: php

    // or PHP or XML
    use a15l\serialization\metadata\loader\file\JSONLoader;

    // the default suffix 'json' will be used
    $loader = new JSONLoader('application/config');

    // tris to read the file: application/config/file.json
    $metadata = $loader->getClassMetadataConfig('file');

    // you can change the file suffix with the 2nd parameter
    $loaderSuffix = new JSONLoader('application/config', 'php');

    // tris to read the file: application/config/file.php
    $metadata = $loader->getClassMetadataConfig('file');

Metadata Loaders
----------------

These loaders loads the metadata of an class. The default metadata loader is the `LazyMetadataLoader`.

The `LazyMetadataLoader` uses a File Loader to load class metadata. It transforms the fully qualified class name
as following:

- Trim all \\
- Replace all \\ to dots (.)

So \\vendor\\foo\\Bar becomes to vendor.foo.Bar

Withe the specified File Loader the Metadata Loader tries now to read the file: vendor.foo.Bar

.. note::

    You can use your own implementation by providing a class that implements the `MetadataLoaderInterface`

Metadata Factories
------------------

Metadata factories uses the Metadata Loader to load class metadata. They combine the default configuration and the
class configuration that was loaded by the Metadata loader. The class configuration overrides always
the default configuration.

There are two implementations of the Metadata factories:

- MetadataFactory
- CacheMetadataFactory

The MetadataFactory uses an array as cache and has no other dependencies. CacheMetadataFactory use the doctrine/cache
dependency.