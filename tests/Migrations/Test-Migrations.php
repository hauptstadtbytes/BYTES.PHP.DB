<?php
//set namespace
namespace BytesPhp\Db\Tests;

//setup error displaying
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//add namespace(s) from 'BYTES.PHP' framework
use BytesPhp\Logging\Log as Log;
use BytesPhp\Logging\InformationLevel as InformationLevel;

//add framework namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;
use BytesPhp\Db\Migration\MigrationManager as MigrationManager;

require_once(__DIR__.'/../../vendor/autoload.php');

//create a new database connection
$connParams = include(__DIR__.'/../config.php');
$db = new DBConnection(["type" => "mysql","host" => $connParams["host"],"database" => $connParams["collection"],"username" => $connParams["user"],"password" => $connParams["password"]]);

//get the migrations
$manager = new MigrationManager(__DIR__."/Migrations",$db);

echo("<h3>List all Migrations found</h3>\n");
foreach($manager->migrations as $shema => $applied) {
    echo("Migration '".$shema."' is applied:".$applied."</br>\n");
}

//check for a pending migration
echo("<h3>Any Pending Migrations?</h3>\n");

if(count($manager->PendingUpgrades) > 0) {
    echo("<strong>Yes:</strong> Schema(s) ".implode(" & ",$manager->PendingUpgrades)."<br />\n");
} else {
    echo("<strong>No</strong><br />\n");
}

echo("<br />\n");

echo("Current Schema: '".$manager->CurrentSchema."'<br />\n");
echo("Latest Schema: '".$manager->LatestSchema."'<br />\n");

//upgrade to the lastest schema version
if(count($manager->PendingUpgrades) > 0) {

    echo("<h3>Upgrade Log to the Latest Schema Version</h3>\n");

    foreach($manager->Upgrade()->Cache as $entry) {
        echo($entry->timestamp->format('Y-m-d H:i:s').";".$entry->level->name.";".$entry->message."<br />\n");
    }

}

//entirely downgrade the database
echo("<h3>Downgrade Log to Schema version '-1'</h3>\n");

foreach($manager->Downgrade()->Cache as $entry) {
    echo($entry->timestamp->format('Y-m-d H:i:s').";".$entry->level->name.";".$entry->message."<br />\n");
}

//upgrade to schema version '1'
if(count($manager->PendingUpgrades) > 0) {

    echo("<h3>Upgrade Log to Schema version '1'</h3>\n");

    foreach($manager->Upgrade(1)->Cache as $entry) {
        echo($entry->timestamp->format('Y-m-d H:i:s').";".$entry->level->name.";".$entry->message."<br />\n");
    }

}

//perform a single upgrade migration
echo("<h3>Runlog for Single Upgrade-Migration to Schema '3'</h3>\n");

$myLog = new Log();
$result = $manager->ExecuteUpgrade("3", $myLog);

foreach($myLog->Cache as $entry) {
    echo($entry->timestamp->format('Y-m-d H:i:s').";".$entry->level->name.";".$entry->message."<br />\n");
}

//perform a single downgrade migration
echo("<h3>Runlog for Single Downgrade-Migration from Schema '3'</h3>\n");

$myLog = new Log();
$result = $manager->ExecuteDowngrade("3", $myLog);

foreach($myLog->Cache as $entry) {
    echo($entry->timestamp->format('Y-m-d H:i:s').";".$entry->level->name.";".$entry->message."<br />\n");
}

echo("<h3>Upgrade Log to the Latest Schema '".$manager->LatestSchema."'</h3>\n");

    foreach($manager->Upgrade()->Cache as $entry) {
        echo($entry->timestamp->format('Y-m-d H:i:s').";".$entry->level->name.";".$entry->message."<br />\n");
    }
?>