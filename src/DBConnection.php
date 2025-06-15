<?php
//set the namespace
namespace BytesPhp\Db;

//add namespace(s) required from "medoo" framework
use Medoo\Medoo as Medoo;

//the database connection class
class DBConnection extends Medoo{

    //public properties
    public $lastResult;

    //public method, checks if a table exists
    public function TableExists(string $name, bool $ignoreCase = true): bool {

        if($ignoreCase) {
            $name = strtolower($name);
        }
        
        if(in_array($name,$this->GetTables($ignoreCase))){
            return true;
        }

        return false;

    }

    //private function, returning a list of all (non-system) tables found in the database
    private function GetTables(bool $lowercase = true) {

        $output = [];

        //enumerate the table(s)
        $query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE NOT (Table_Schema = 'information_schema' OR Table_Schema = 'performance_schema')";
        foreach($this->query($query) as $row) {

            $name = $row["TABLE_NAME"];;

            if($lowercase) {
                $name = strtolower($name);
            }

            $output[] = $name; 

        }

        return $output;

    }

}
?>