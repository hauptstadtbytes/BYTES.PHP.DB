<?php
//set the namespace
namespace BytesPhp\Db\Migration;

//import namespace(s) required from 'BYTES.PHP' framework
use BytesPhp\Reflection\Extensibility\PluginsManager as PluginsManager;

use BytesPhp\Logging\Log as Log;
use BytesPhp\Logging\InformationLevel as InformationLevel;

//import internal namspace(s) required
use BytesPhp\Db\DBConnection as DBConnection;

//the migration manager class
class MigrationManager{

    //private variable(s)
    private string $searchPath;
    private PluginsManager $manager;

    private DBConnection $connection;

    //constructor method(s)
    public function __construct(string $searchPath, DBConnection $connection) {

        $this->searchPath = $searchPath; //set the search path
        $this->manager = new PluginsManager();

        $this->connection = $connection;

    }

    //public (magic) getter method, for reading properties
    public function __get(string $property) {
            
        switch(strtolower($property)) {

            case "migrations":
                return $this->EvaluateMigrations();
                break;

            case "pendingupgrades":
                return $this->GetPendingUpgrades();
                break;

            case "latestschema":
                $keys = array_keys($this->Migrations);
                return end($keys);
                break;

            case "currentschema":
                return $this->GetCurrentSchema();
                break;
                
            default:
                return null;
            
        }
        
    }

    //public method for upgrading (to an optional schema version)
    public function Upgrade($targetSchema = null) : Log {

        //validate the parameter(s) given
        if(is_null($targetSchema)) {
            $targetSchema = $this->LatestSchema;
        }

        //create the output value
        $myLog = new Log();
        $myLog->Threshold = InformationLevel::Debug;
        
        //pre-check the consistency
        if($this->PreCheckForUpgrade($targetSchema,$myLog) != true){

            return $myLog;

        }

        //apply migrations
        $appliedMigrations = $this->EvaluateMigrations();
        ksort($appliedMigrations);

        foreach($this->GetMigrations() as $schema => $extension) { //loop for each migration known

            if($appliedMigrations[$schema] != true) { //check if the migration was not already applied

                $myLog->Info("== Starting upgrade to schema version '".$schema."' ==");

                if($this->ExecuteUpgrade($schema,$myLog)) {

                    $myLog->Info("== Upgrading to schema version '".$schema."' finished successful ==");

                } else { //break the upgrade loop if an error occured

                    $myLog->Warning("Upgrading to schema version '".$schema."' failed. Upgrade batch terminated.");

                    break;
                }

            }

            //terminate the upgrade if a specific schema version was reached
            if(!is_null($targetSchema)) {

                if($schema == $targetSchema) {

                    break;

                }

            }

        }

        //return the output value
        return $myLog;

    }

    //public function for downgrading (to an optional schema version given)
    public function Downgrade($targetSchema = null) : Log {

        //create the output value
        $myLog = new Log();
        $myLog->Threshold = InformationLevel::Debug;

        //get the list of available migrations in descenting order
        $migrations = $this->GetMigrations();
        krsort($migrations); //sort the items by key descending

        //get the list of migrations (including status)
        $status = $this->migrations; 

        //apply migrations
        foreach($migrations as $schema => $extension) { //loop for each migration known

            if($status[$schema]) { //check if the migration was applied

                $myLog->Info("== Starting downgrade from schema version '".$schema."' ==");

                if($this->ExecuteDowngrade($schema,$myLog)) {

                    $myLog->Info("== Downgrading from schema version '".$schema."' finished successful ==");

                } else { //break the upgrade loop if an error occured

                    $myLog->Warning("Downgrading from schema version '".$schema."' failed. Downgrade batch terminated.");

                    break;
                }

            }

            //terminate the downgrade if a specific schema version was reached
            if(!is_null($targetSchema)) {

                if($schema == $targetSchema) {

                    break;

                }

            }

        }

        //return the output value
        return $myLog;

    }

    //public function executing the 'up' method of a specific migration given
    public function ExecuteUpgrade($schema, Log &$log): bool {

        //get the extension
        $extensions = $this->GetMigrations();

        //check for the extension
        if(!array_key_exists($schema,$extensions)) {

            $log->Warning("Unable to find migration for schema '".$schema."'");
            return false;

        }

        //execute the migration
        $extension = $extensions[$schema];

        return $extension->instance->Up($this->connection, $log);

    }

    //public function executing the 'up' method of a specific migration given
    public function ExecuteDowngrade($schema, Log &$log): bool {

        //get the extension
        $extensions = $this->GetMigrations();

        //check for the extension
        if(!array_key_exists($schema,$extensions)) {

            $log->Warning("Unable to find migration for schema '".$schema."'");
            return false;

        }

        //execute the migration
        $extension = $extensions[$schema];

        return $extension->instance->Down($this->connection, $log);

    }

    //private function returning the current schema
    private function GetCurrentSchema() {

        $output = -1;

        //check for the last applied migration
        foreach($this->EvaluateMigrations() as $schema => $isApplied) {

            if($isApplied) {
                $output = $schema;
            }

        }

        return $output;

    }

    //enumerates the migrations existing
    private function GetMigrations() {

        $output = [];

        //enumerate the extensions
        foreach($this->manager->GetPlugins([$this->searchPath],"BytesPhp\Db\Migration\API\DBMigrationExtension") as $extension) {
            $output[$extension->instance->GetSchema()] = $extension;
        }

        ksort($output); //sort the plugins by schema version ascending

        //return the output value
        return $output;

    }

    //evaluates the migrations
    private function EvaluateMigrations() {

        $output = [];

        //enumerate the extensions
        foreach($this->GetMigrations() as $schema => $extension) {
            $output[$schema] = $extension->instance->IsApplied($this->connection);
        }

        //return the output value
        return $output;

    }

    //lists all pending upgrades
    private function GetPendingUpgrades() : array {

        $output = [];

        //check for a non-applied migration
        foreach($this->EvaluateMigrations() as $schema => $isApplied) {

            if($isApplied != true) {

                $output[] = $schema;
            }

        }

        //return the output value
        return $output;

    }

    //private functions forming consistency pre-checks for upgrading
    private function PreCheckForUpgrade($targetSchema, Log &$log) : bool {

        //check for the latest schema version
        if($this->CurrentSchema == $targetSchema) {

            $log->Info("The latest migration was already applied, automatic migration skipped");
            return false;

        }

        //evaluate for "migration gaps" 
        $appliedMigrations = $this->EvaluateMigrations();

        krsort($appliedMigrations); //sort the items by key descending

        $alreadyApplied = false;

        foreach($appliedMigrations as $schema => $status) {

            if($status == false && $alreadyApplied) {

                $log->Warning("Cannot apply migration to version '".$schema."' since a more updated schema was already applied; Automatic upgrade skipped");
                return false;

            }

            if($status) {
                $alreadyApplied = true;
            }

        }

        //return the default output value
        return true;

    }

}
?>