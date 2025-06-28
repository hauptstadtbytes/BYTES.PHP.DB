<?php
//set namespace
namespace BytesPhp\Db\Tests;

//import namespace(s) required from 'BYTES.PHP' framework
use BytesPhp\Logging\Log as Log;
use BytesPhp\Logging\InformationLevel as InformationLevel;

//add framework namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;
use BytesPhp\Db\Migration\Migration as Migration;

//the migration class
class ExtendDB extends Migration {
    
    //return the schema (version)
    public function GetSchema() {
        return 2;
    }

    //checks if a migration was already applied
    public function IsApplied(DBConnection $connection) : bool {

        if($connection->TableExists("properties")){ //the function ignores case by default
            return true;
        }

        return false;

    }

    //upgrade the schema from previous version
    public function Up(DBConnection $connection, Log &$log) : bool {

        return $this->ExecuteSQLScript(__DIR__."/2_ExtendDB-Up.sql",$connection,$log);

    }

    //downgrade the schema to the previous version
    public function Down(DBConnection $connection, Log &$log) : bool {

        return $this->ExecuteSQLScript(__DIR__."/2_ExtendDB-Down.sql",$connection,$log);

    }

}
?>