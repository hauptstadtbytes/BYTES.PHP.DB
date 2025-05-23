<?php

//set namespace
namespace BytesPhp\Db\Tests;

//setup error displaying
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//add framework namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;

require_once(__DIR__.'/../../vendor/autoload.php');

//create a database connection
//see 'https://medoo.in/api/new' for details
$connParams = include(__DIR__.'/../config.php');

$db = new DBConnection(["type" => "mysql","host" => $connParams["host"],"database" => $connParams["collection"],"username" => $connParams["user"],"password" => $connParams["password"]]);

//print the database information
echo("<h1>Server Information</h1>");
print_r($db->info());
?>