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

//add internal classes
require_once(__DIR__.'/DBReadOnlyItem.php');

use BytesPhp\Db\Tests\DBReadOnlyItem as DBReadOnlyItem;

//create a database connection
//see 'https://medoo.in/api/new' for details
$connParams = include(__DIR__.'/../config.php');

$db = new DBConnection(["type" => "mysql","host" => $connParams["host"],"database" => $connParams["collection"],"username" => $connParams["user"],"password" => $connParams["password"]]);

//print all items found (as array)
$lastItem = null;

echo("<h3>All Items as Array</h3>");
foreach(DBReadOnlyItem::All($db) as $item) {
    //echo("Item ".$item->id." with title '".implode(";",$item->ToArray)."' found</br>\n");
    echo($item->AsJSON."</br>\n");

    $lastItem = $item;
}

echo("<h3>All Filtered Items as Array</h3>");
foreach(DBReadOnlyItem::All($db,["TestTitle" => "Hello"]) as $item) {
    //echo("Item ".$item->id." with title '".implode(";",$item->ToArray)."' found</br>\n");
    echo($item->AsJSON."</br>\n");

    $lastItem = $item;
}

echo("<h3>Single Property Reading</h3>");
echo("myID: ".$lastItem->myid."<br />\n");
echo("TestTitle: ".$lastItem->testtitle."<br />\n");
echo("Content: ".$lastItem->Content."<br />\n");

echo("<h3>Property Overwriting</h3>");
echo($lastItem->sample);

?>