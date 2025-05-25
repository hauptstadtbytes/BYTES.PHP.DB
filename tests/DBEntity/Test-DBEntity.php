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
require_once(__DIR__.'/DBItem.php');
require_once(__DIR__.'/NonExistingItem.php');

use BytesPhp\Db\Tests\DBItem as DBItem;
use BytesPhp\Db\Tests\NonExistingItem as NonExistingItem;

//create a database connection
//see 'https://medoo.in/api/new' for details
$connParams = include(__DIR__.'/../config.php');

$db = new DBConnection(["type" => "mysql","host" => $connParams["host"],"database" => $connParams["collection"],"username" => $connParams["user"],"password" => $connParams["password"]]);

//print all items found (as array)
echo("<h3>All Items as Array</h3>");
foreach(DBItem::All($db) as $item) {
    //echo("Item ".$item->id." with title '".implode(";",$item->ToArray)."' found</br>\n");
    echo($item->AsJSON."</br>\n");
}

//print all items (with specific properties only)
echo("<h3>All Items with Specific Properties</h3>");
foreach(DBItem::All($db) as $item) {
    echo("Item ".$item->id." found with title '".$item->Testtitle."'</br>\n");
}

//get the first item only
echo("<h3>The first item</h3>");

$item = DBItem::First($db);
echo("Item ".$item->id." with title '".$item->Testtitle."' without any filter</br>\n");

$item = DBItem::First($db,["Testtitle" => "Hello"]);
echo("Item ".$item->id." with title '".$item->Testtitle."' for  filter 'testtitle = Hello'</br>\n");

//use null for non-existing properties
echo("<h3>Use NULL for non-existing Properties</h3>");

foreach(DBItem::All($db) as $item) {
    echo("There should not be any value or error visbile for 'NotExisting' (".$item->id."): ".$item->NotExisting."</br>\n");
}

//use 'where' parameters for listing item(s)
echo("<h3>All Items by Filter</h3>");

foreach(DBItem::All($db,["id[<]" => 8]) as $item) {
    echo("Item ".$item->id." found with title '".$item->Testtitle."' for filter 'id < 8'</br>\n");
}

foreach(DBItem::All($db,["testtitle" => "changed again"]) as $item) {
    echo("Item ".$item->id." found with title '".$item->Testtitle."' for filter 'testtitle = changed again'</br>\n");
}

//create a new item
echo("<h3>Create new items</h3>");

$item = DBItem::Create($db,[]);
echo("Item ".$item->id." created with no information:</ br>\n");
echo($item->AsJSON."</ br>\n</br>\n");

$props = ["content" => "this item was created by code"];

$item = DBItem::Create($db,$props);
echo("Item ".$item->id." created with partial information:</ br>\n");
echo($item->AsJSON."</ br>\n</br>\n");

$props["testtitle"] = "created by code";

$item = DBItem::Create($db,$props);
echo("Item ".$item->id." created with all information:</ br>\n");
echo($item->AsJSON."</ br>\n</br>\n");

//update the new item
echo("<h3>Update the (Last) Item</h3>");
$title = $item->Testtitle;

$item->Testtitle = "another title";
echo("The title of item ".$item->id." was changed from '".$title."' to '".$item->Testtitle."'</ br>\n");
echo("</br>\n");

$props = ["Testtitle" => "changed again", "content" => "content changed again"];
$item->AsArray = $props; //update multiple properties simultaneously
echo("The item was changed again (from array): ".$item->AsJSON."'</ br>\n");
echo("</br>\n");

$jsonProps = '{"id":99,"testtitle":"changed by json","content":"content changed by json again"}';
$item->AsJSON = $jsonProps;
echo("The item was changed again (from JSON): ".$item->AsJSON."'</ br>\n");
echo("</br>\n");

$item->test = "sample";//try to update an unknown property
echo("There shall be no error before this line</ br>\n");

//delete the last item
//echo("<h3>Remove the last item</h3>");
$counter = 0;
$items = DBItem::All($db);

foreach($items as $item) {

    $counter++;

    if($counter == count($items)) {

        $id = $item->id;

        //$item->Delete();
        //echo("Item ".$id." removed</ br>\n");

    }

}

//fail to print non-existing items
//echo("<h3>Non-Existingh Item(s)</h3>");
//foreach(NonExistingItem::All($db) as $item) {
    //echo("Item ".$item->id." found with title '".$item->title."'</br>\n");
//}
?>