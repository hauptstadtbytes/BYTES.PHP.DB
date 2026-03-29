<?php

//set namespace
namespace BytesPhp\Db\Tests;

//setup error displaying
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//add framework namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;

//add namespace(s) required from 'BYTES.PHP' framework
use BytesPhp\Data\FieldPropertyMapping as FieldPropertyMapping;
use BytesPhp\Data\FieldPropertyMappingsList as FieldPropertyMappingsList;

//add internal namsespace(s) required
use BytesPhp\Db\DBEntity as DBEntity;

require_once(__DIR__.'/../../vendor/autoload.php');

class DBItem extends DBEntity{
    
    //public properties
    public static ?string $table = "Items";
    public static ?string $idField = "id";

    //protected method, intended for overwriting propterty mapping
    protected static function GetPropertyMappings() : FieldPropertyMappingsList {

        $srcList = [];
        $srcList[] = new FieldPropertyMapping("id","id");
        $srcList[] = new FieldPropertyMapping("testtitle","title");
        $srcList[] = new FieldPropertyMapping("content","content");
        $srcList[] = new FieldPropertyMapping("body","content");

        return new FieldPropertyMappingsList($srcList);

    }
}

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
foreach(DBItem::All($db,null) as $index => $item) {
    echo("Item ".$item->id." (index '".$index."') found with title '".$item->Testtitle."'</br>\n");
}

//use null for non-existing properties
echo("<h3>Use NULL for non-existing Properties</h3>");

foreach(DBItem::All($db) as $item) {
    echo("There should not be any value or error visbile for property 'NotExisting' (".$item->id."): ".json_encode($item->NotExisting)."</br>\n");
}

//print all filtered items
echo("<h3>All Items matching filter id > 1</h3>");
foreach(DBItem::All($db,["id[>]" => 1]) as $index => $item) {
    echo("Item ".$item->id." (index '".$index."') found with title '".$item->Testtitle."'</br>\n");
}

//get the first item only
echo("<h3>The first item</h3>");

$item = DBItem::First($db);
echo("Item ".$item->id." with title '".$item->Testtitle."' without any filter</br>\n");

$item = DBItem::First($db,["testtitle" => "Hello"]);
echo("Item ".$item->id." with title '".$item->testtitle."' for  filter 'testtitle = Hello'</br>\n");


//get the first item with property overloading
echo("<h3>The first item with property overloading</h3>");

$item = DBItem::First($db,["body" => "World!"]);
echo("Item ".$item->id." with title '".$item->testtitle."' for  filter 'body/content = World!'</br>\n");

$item = DBItem::First($db,["content" => "World!"]);
echo("Item ".$item->id." with title '".$item->testtitle."' for  filter 'body/content = World!'</br>\n");

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
echo("<h3>Update the Last Item</h3>");
$title = $item->Testtitle;

$item->Testtitle = "another title";
echo("The title of item ".$item->id." was changed from '".$title."' to '".$item->Testtitle."'</ br>\n");
echo("</br>\n");

$props = ["Testtitle" => "changed again", "content" => "content changed again"];
$item->AsArray = $props; //update multiple properties simultaneously
echo("The item was changed again (from array): ".$item->AsJSON."'</ br>\n");
echo("</br>\n");

$jsonProps = '{"id":'.$item->id.',"testtitle":"changed by json","content":"content changed by json again"}';
$item->AsJSON = $jsonProps;
echo("The item was changed again (from JSON): ".$item->AsJSON."'</ br>\n");
echo("</br>\n</br>\n");

echo($db->lastResult->rowCount()." items have been updated in the last DB query</br>\n");
echo("</br>\n");

//$item->test = "sample";//trying to update a non-existing column will result in a PDO exception
//echo("There shall be no error before this line</ br>\n");

//delete the last items
echo("<h3>Remove all items > 7</h3>");

foreach(DBItem::All($db,["id[>]" => 7]) as $item) {

        $id = $item->id;

        $item->Delete();
        echo("Item ".$id." removed</br>\n");

}
?>