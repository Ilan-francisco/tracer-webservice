<?php
require './vendor/autoload.php';
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;


$container = new \Slim\Container();
$isDevMode = true;


$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src/Models/Entity"), $isDevMode);

$conn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite',
);


$entityManager = EntityManager::create($conn, $config);

$container['em'] = $entityManager;

$app = new \Slim\App($container);
