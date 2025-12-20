<?php

//set namespace
namespace BytesPhp\Db\Tests;

//add internal namsespace(s) required
use BytesPhp\Db\DBReadOnlyCachedEntity as DBReadOnlyCachedEntity;

class DBReadOnlyItem extends DBReadOnlyCachedEntity{
    
    //public properties
    public static ?string $table = "Items";
    public static array $fieldMappings = ["myid" => "id", "testtitle" => "title","content" => "content"];

    protected function ReadProperty($property) {

        if($property == "sample") {
            return "sample";
        }

    }
}

?>