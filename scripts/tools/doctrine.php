<?php

/**
 * Doctrine commandline script
 * Run from command line- APPLICATION_ENV='development-local' php scripts/tools/doctrine.php
 *
 * @author     Md. Sarwar Zahan <md.sarwar.zahan@gmail.com>
 * @package    scripts
 */

// setting notices off
error_reporting(E_ALL ^ E_NOTICE);

$environment = getenv("APPLICATION_ENV");
if (empty($environment)) {
    echo "Please provide environment variable APPLICATION_ENV. Example- APPLICATION_ENV='development-local' php executables/doctrine.php";
    die;
}

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../vendor/zendframework/zendframework1/library'),
    realpath(APPLICATION_PATH . '/../vendor/doctrine/common/lib'),
    realpath(APPLICATION_PATH . '/../vendor/doctrine/dbal/lib'),
    realpath(APPLICATION_PATH . '/../vendor/doctrine/orm/lib'),
    realpath(APPLICATION_PATH . '/../vendor/doctrine/migrations/lib'),
    realpath(APPLICATION_PATH . '/../vendor/symfony/symfony/src'),
    get_include_path(),
)));

//Change the directory path to 'configs' directory
$configPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs';
chdir($configPath);

set_time_limit(0);

//Register autoload
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);
$autoloader->suppressNotFoundWarnings(true);

$commands = array(
    // Migrations Commands
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand()
);

// Initialise the database
$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', $environment);
$configArray = $config->resources->db->toArray();

//Construct $params from $configArray
$params              = array();
$params['driver']    = $configArray['adapter'];
$params['user']      = $configArray['params']['username'];
$params['password']  = $configArray['params']['password'];
$params['host']      = $configArray['params']['host'];
//$params['port']      = $configArray['params']['port'];
$params['dbname']    = $configArray['params']['dbname'];
$params['charset']   = $configArray['params']['charset'];

$db = \Doctrine\DBAL\DriverManager::getConnection($params);

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($db),
    'dialog' => new \Symfony\Component\Console\Helper\DialogHelper(),
));

// Run the doctrine application
\Doctrine\ORM\Tools\Console\ConsoleRunner::run($helperSet, $commands);