<?php
//set namespace
namespace BytesPhp\Db\Tests;

//import namespace(s) required from 'BYTES.PHP' framework
use BytesPhp\Logging\Log as Log;
use BytesPhp\Logging\InformationLevel as InformationLevel;

//add framework namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;
use BytesPhp\Db\Migration\API\DBMigrationExtension as DBMigrationExtension;

use BytesPhp\Db\SQL\SQLScript as SQLScript;

//the migration class
class CreateDB implements DBMigrationExtension {
    
    //return the schema (version)
    public function GetSchema() {
        return 1;
    }

    //checks if a migration was already applied
    public function IsApplied(DBConnection $connection) : bool {

        //return false; //force schema enumeration to fail (for testing purpose)

        if($connection->TableExists("items")){ //the function ignores case by default
            return true;
        }

        return false;

    }

    //upgrade the schema from previous version
    public function Up(DBConnection $connection, Log &$log) : bool {

        //execute the SQL script file
        $script = new SQLScript(__DIR__."/1_CreateDB-Up.sql");

        $scriptLog = $script->Execute($connection);

        //evaluate the script run
        $output = true;

        foreach($scriptLog->Cache as $entry) {
            switch(true) {
                case $entry->level == InformationLevel::Warning:
                    $log->Write($entry);
                    $result = false;
                    break;
                default:
                    $log->Write($entry);
                    break;
            }
        }

        //return the output value
        return $output;

    }

    //downgrade the schema to the previous version
    public function Down(DBConnection $connection, Log &$log) : bool {

        //run the SQL query
        $query = "DROP TABLE IF EXISTS `Items`";

        try{

            $result = $connection->query($query)->fetchAll(); //execute the qury

            if(count($result) == 0) { //analyze the PDOStatement data returned

                $log->Info("Removing 'Items' table successfully");
                return true;

            } else {

                $log->Warning("Removing 'Items' table failed");
                return false;

            }

        } catch (\PDOException $e) {

            $log->Warning("Removing 'Items' table failed with message '".$e->getMessage()."'");
            return false;

        }catch(\Exception $e) {

            $log->Warning("Removing 'Items' table failed with message '".$e->getMessage()."'");
            return false;

        }

    }

}
?>