<?php

namespace Twist\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Twist\Configuration\Configuration;

class EntityManagerFactory
{
    /** @var Configuration */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function create(array $paths, array $connection = [], bool $isDevMode = false): EntityManager
    {
        $config = Setup::createConfiguration($isDevMode);
        $driver = new AnnotationDriver(new AnnotationReader(), $paths);

        AnnotationRegistry::registerLoader('class_exists');
        $config->setMetadataDriverImpl($driver);

        [$connection, $createSchema] = $this->prepareConnection($connection);

        $em = EntityManager::create($connection, $config);

        if ($createSchema) {
            $this->createSchema($em);
        }

        return $em;
    }

    protected function prepareConnection(array $connection): array
    {
        $connection = array_merge([
            'driver' => 'pdo_sqlite',
            'path' => $this->configuration->get('database_path')
        ], $connection);

        return [$connection, !file_exists($connection['path'])];
    }

    protected function createSchema(EntityManager $em)
    {
        $tool = new SchemaTool($em);
        $tool->createSchema($em->getMetadataFactory()->getAllMetadata());
    }
}
