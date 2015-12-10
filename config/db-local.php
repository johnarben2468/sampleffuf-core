<?php

$ormCache = new \Doctrine\Common\Cache\ArrayCache();
$entityManagerConfig = \Doctrine\ORM\Tools\Setup::createXMLMetadataConfiguration(
                array(__DIR__ . "/orm"), false, __DIR__ . "/orm/proxy", $ormCache);
$entityManagerConfig->setAutoGenerateProxyClasses(\Doctrine\Common\Proxy\AbstractProxyFactory::AUTOGENERATE_ALWAYS);
$entityManagerConfig->setMetadataCacheImpl($ormCache);
//$entityManagerConfig->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
$entityManager = \Doctrine\ORM\EntityManager::create(array(
            'driver' => 'pdo_mysql',
            'user' => 'root',
            'password' => 'admin',
            'dbname' => 'sample',
            'host' => '127.0.0.1',
            'connection' => array('compress' => 'true'),
            'driverOptions' => array(
                1002 => 'SET NAMES utf8'
            )
                ), $entityManagerConfig);
$entityManager->getConnection()->getDatabasePlatform()
        ->registerDoctrineTypeMapping('enum', 'string');
