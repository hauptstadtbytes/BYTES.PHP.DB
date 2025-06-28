<?php

//set namespace
namespace BytesPhp\Db\Tests;

//setup error displaying
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//add framework namespace(s) required
use BytesPhp\Db\SQL\SQLScript as SQLScript;
use BytesPhp\Db\DBConnection as DBConnection;

require_once(__DIR__.'/../../vendor/autoload.php');

//load the SQL script file
$file = new SQLScript(__DIR__."/Test.sql");

//print the commands
echo("<h1>Commands in File '".$file->path."'</h1>");
$counter = 0;

foreach($file->commands as $command) {

        $counter++;

        echo($counter.": ".$command."</br>\n");

}

//create a database connection
$connParams = include(__DIR__.'/../config.php');
$db = new DBConnection(["type" => "mysql","host" => $connParams["host"],"database" => $connParams["collection"],"username" => $connParams["user"],"password" => $connParams["password"]]);

//execute the query
echo("<h1>Execution Result</h1>");

$log = $file->Execute($db); //execute the script

foreach($log->Cache as $entry) {
        echo($entry->timestamp->format('Y-m-d H:i:s').";".$entry->level->name.";".$entry->message."<br />\n");
}
?>