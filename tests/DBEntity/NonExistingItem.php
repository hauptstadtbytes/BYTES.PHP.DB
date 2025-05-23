<?php

//set namespace
namespace BytesPhp\Db\Tests;

//add internal namsespace(s) required
use BytesPhp\Db\DBEntity as DBEntity;

class NonExistingItem extends DBEntity{
    
    //protected properties
    public static ?string $table = "NotExisting";
    public static ?string $idField = "id";
    public static array $fieldMappings = ["id" => "id", "title" => "title","content" => "content"];
}

?>