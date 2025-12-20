<?php

//set namespace
namespace BytesPhp\Db\Tests;

//add internal namsespace(s) required
use BytesPhp\Db\DBEntity as DBEntity;

class DBItem extends DBEntity{
    
    //public properties
    public static ?string $table = "Items";
    public static ?string $idField = "id";
    public static array $fieldMappings = ["id" => "id", "testtitle" => "title","content" => "content"];
}

?>