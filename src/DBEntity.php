<?php
//set the namespace
namespace BytesPhp\Db;

//add namespace(s) required from 'BYTES.PHP' framework
use BytesPhp\Data\FieldPropertyMapping as FieldPropertyMapping;
use BytesPhp\Data\FieldPropertyMappingsList as FieldPropertyMappingsList;

//add internal namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;
use BytesPhp\Db\FieldValueComparison as FieldValueComparison;

use BytesPhp\Db\DBReadOnlyEntity as DBReadOnlyEntity;

//the DB entity base class
abstract class DBEntity extends DBReadOnlyEntity{

    //public (magic) setter method, for writing properties
    public  function __set($property, $value) {

        if($this->WriteProperty($property, $value, static::GetPropertyMappings()) != true) { //check if the data update was done by the custom handler

            switch(strtolower($property)) {

            case "asarray":
                return $this->WriteTable($value,static::GetPropertyMappings());
                break;

            case "asjson":
                return $this->WriteTable(json_decode($value,true),static::GetPropertyMappings());
                break;
                
            default:
                return $this->WriteTable([$property => $value],static::GetPropertyMappings());
            
            }

        }

    }

    //public static method for creating a new entity
    public static function Create(DBConnection $connection, array $properties) {

        //get the child class type name
        $class = get_called_class();

        //remap the properties to db fields
        $data = static::RemapArrayToFields($properties,static::GetPropertyMappings());

        //insert the new itemdata to databse table
        $connection->insert(static::$table,$data);

        //return the output value
        return new $class($connection,$connection->id()); //see 'https://medoo.in/api/insert' for getting the last ID inserted

    }

    //public method, deleting the current item
    public function Delete() {

        $this->connection->lastResult = $this->connection->delete(static::$table,[static::$idField => $this->id]);

    }

    //property writing method, intended for overwriting property writing child classes
    protected function WriteProperty(string $property, mixed $value, FieldPropertyMappingsList $mappings) : bool {

        return false; //return a boolean, indicating if the update was done (true = no additional action required, false = call the default)

    }

    //writing data to database table
    protected function WriteTable(array $properties, FieldPropertyMappingsList $mappings) {

        //remap the properties to db fields and prepare update statement
        $updates = static::RemapArrayToFields($properties,$mappings);

        //update the database        
        if(!empty($updates)){ //check for updates

            //set the 'where' statement
            $where = [static::$idField => $this->id];

            //update the data set
            $this->connection->lastResult = $this->connection->update(static::$table,$updates,$where);

        }

    }

    //remaps properties for table writing
    protected static function RemapArrayToFields(array $properties, FieldPropertyMappingsList $mappings) : array{

        $output = [];

        foreach($properties as $property => $value) {

            if(array_key_exists(strtolower($property),$mappings->AsArrayByPropertyName)){ //the property is known

                $mapping = $mappings->AsArrayByPropertyName[strtolower($property)];

                $output[$mapping->fieldName] = $value;

            } else { //apply the update for a raw field index

                $output[$property] = $value;

            }
        }

        return $output;

    }

}
?>