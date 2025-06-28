<?php
//set the namespace
namespace BytesPhp\Db\Migration\API;

//import namespace(s) required from 'BYTES.PHP' framework
use BytesPhp\Logging\Log as Log;

//import internal namspace(s) required
use BytesPhp\Db\DBConnection as DBConnection;

//the migration extension interface
interface DBMigrationExtension {
    
    //return the schema (version)
    public function GetSchema();

    //checks if a migration was already applied
    public function IsApplied(DBConnection $connection) : bool;

    //upgrade the schema from previous version
    public function Up(DBConnection $connection, Log &$log) : bool;

    //downgrade the schema to the previous version
    public function Down(DBConnection $connection, Log &$log) : bool;

}

?>