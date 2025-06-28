<?php
//set the namespace
namespace BytesPhp\Db\Migration;

//import namespace(s) required from 'BYTES.PHP' framework
use BytesPhp\Logging\Log as Log;
use BytesPhp\Logging\InformationLevel as InformationLevel;

//add framework namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;
use BytesPhp\Db\Migration\API\DBMigrationExtension as DBMigrationExtension;

use BytesPhp\Db\SQL\SQLScript as SQLScript;

//the migration base class
abstract class Migration implements DBMigrationExtension{

    //return the schema (version)
    abstract public function GetSchema();

    //checks if a migration was already applied
    abstract public function IsApplied(DBConnection $connection):bool;

    //upgrade the schema from previous version
    abstract public function Up(DBConnection $connection, Log &$log):bool;

    //downgrade the schema to the previous version
    abstract public function Down(DBConnection $connection, Log &$log):bool;

    //protected method executing a SQL script file
    protected function ExecuteSQLScript(string $filePath, DBConnection $connection, Log &$log): bool {

        //execute the SQL script file
        $script = new SQLScript($filePath);
        $scriptLog = $script->Execute($connection);

        //write to the log and return the output value
        $output = true;

        foreach($scriptLog->Cache as $entry) {

            $log->Write($entry);

            if($entry->level == InformationLevel::Warning) {
                $result = false;
            }

        }

        //return the output value
        return $output;

    }

    //protected method executing a SQL query
    protected function ExecuteSQLQuery(string $query, DBConnection $connection, Log &$log): bool {

        try{

            $result = $connection->query($query)->fetchAll(); //execute the qury

            if(count($result) == 0) { //analyze the PDOStatement data returned

                $log->Info("Query '".$query."' executed successfully");
                return true;

            } else {

                $log->Warning("Failed to execute query '".$query."'");
                return false;

            }

        } catch (\PDOException $e) {

            $log->Warning("Failed to execute query '".$query."' with message '".$e->getMessage()."'");
            return false;

        }catch(\Exception $e) {

            $log->Warning("Failed to execute query '".$query."' with message '".$e->getMessage()."'");
            return false;

        }

    }

}

?>