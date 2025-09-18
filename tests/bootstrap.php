<?php

use Symfony\Component\Dotenv\Dotenv;
use Doctrine\ORM\Tools\SchemaTool;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env.test')) {
    (new Dotenv())->load(dirname(__DIR__) . '/.env.test');
}

$kernel = new App\Kernel($_ENV['APP_ENV'], (bool) ($_ENV['APP_DEBUG'] ?? true));
$kernel->boot();

if ($_ENV['APP_ENV'] === 'test') {
    $container = $kernel->getContainer();

    $entityManager = $container->get('doctrine')->getManager();

    $schemaTool = new SchemaTool($entityManager);
    $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

    if (!empty($metadata)) {
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadata);
    }
}
