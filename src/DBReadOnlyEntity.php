<?php
//set the namespace
namespace BytesPhp\Db;

//add namespace(s) required from 'BYTES.PHP' framework
use BytesPhp\Data\FieldPropertyMapping as FieldPropertyMapping;
use BytesPhp\Data\FieldPropertyMappingsList as FieldPropertyMappingsList;

//add internal namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;
use BytesPhp\Db\FieldValueComparison as FieldValueComparison;

//the DB read-only entity base class
abstract class DBReadOnlyEntity{

    //protected properties
    protected DBConnection $connection;
    protected string $id;

    //protected static properties
    protected static ?string $table = null;
    protected static ?string $idField = null;

    //constructor method(s)
    public function __construct(DBConnection &$connection, string $id){

        $this->connection = $connection;
        $this->id = $id;

    }

    //public (magic) getter method, for reading properties
    public function __get(string $property) {
            
        //check for a property overwrite
        $data = $this->ReadProperty($property,static::GetPropertyMappings());

        if(!is_null($data)){
            return $data;
        }

        //return the default value
        switch(strtolower($property)) {

            case "id":
                return $this->id;
                break;

            case "asarray":
                return $this->ReadTable(static::GetPropertyMappings());
                break;

            case "asjson":
                return json_encode($this->ReadTable(static::GetPropertyMappings()));
                break;
                
            default:
                return $this->ReadTable(static::GetPropertyMappings(),[$property]);
            
        }
        
    }

    //returns all instances of the entity
    public static function All(DBConnection $connection, array $where = null) : array {

        //return the output value
        return static::Enumerate($connection, $where);

    }

    //returns the first instance of the entity
    public static function First(DBConnection $connection, array $where = null): ?DBEntity {

        //return the output value
        foreach(static::Enumerate($connection, $where) as $instance){
            return $instance;
        }

        return null;

    } 

    //compares this data set with another one
    public function Equals($other){

        if($this->id == $other->id){
            return true;
        }

        return false;

    }

    //protected method, intended for overwriting propterty mapping
    protected static function GetPropertyMappings() : FieldPropertyMappingsList {

        $srcList = [];
        return new FieldPropertyMappingsList($srcList);

    }

    //enumeration method, intended for returning a (filtered) list of all entities available
    protected static function Enumerate(DBConnection $connection, ?array $where = null) {

        //get the child class type name
        $class = get_called_class();

        //parse the 'where' statement
        if(!is_null($where)) {

            $parsedList = [];

            //create a list of comparisons
            foreach($where as $key => $value) {

                switch(true) {
                    case is_a($value,'BytesPhp\Db\FieldValueComparison'): //it's already a comparing statement
                        $parsedList[] = $value; 
                        break;
                    default:
                        $parsedList[] = FieldValueComparison::Parse($key,$value);//create a parsed comparing statement
                        break;
                }

            }

            //replace the 'where' statement in a meedo compatible format, remapping properties to field names
            $mappings = static::GetPropertyMappings();
            $where = [];

            foreach($parsedList as $comp){

                if(array_key_exists(strtolower($comp->fieldName),$mappings->AsArrayByPropertyName)) {

                    $mapping = $mappings->AsArrayByPropertyName[strtolower($comp->fieldName)];
                    $comp->fieldName = $mapping->fieldname;

                }

                $where[$comp->GetMeedoKey()] = $comp->value;

            }

        }

        //query the database
        $output = [];

        foreach($connection->select(static::$table,"*",$where) as $row) { //loop for all rows found

            $output[] = new $class($connection,$row[static::$idField]);

        }

        //return the output value
        return $output;

    }

    //property reading method, intended for overwriting proptery reading or adding additional properties in child classes
    protected function ReadProperty(string $property, FieldPropertyMappingsList $mappings) {

        return null;

    }

    //reading data from database table
    protected function ReadTable(FieldPropertyMappingsList $mappings, array $properties = null) {

        //assemble the selector, mapping known properties to db table fields
        $selector = "*";

        if(!is_null($properties)) {

            $fields = [];

            foreach($properties as $property) {

                if(array_key_exists(strtolower($property),$mappings->AsArrayByPropertyName)){

                    $fields[] = $mappings->AsArrayByPropertyName[strtolower($property)]->fieldname;
                }

            }

            if(!empty($fields)) {

                $selector = implode(",",$fields);

            }

        }

        //query the database
        foreach($this->connection->select(static::$table,$selector,[static::$idField => $this->id,"LIMIT" => 1]) as $row) { //loop for the first row only

            if(is_array($row)) {

                $remappedValues = $this->RemapArrayToProperties($row,$mappings);

                if(is_null($properties)) { //no specific properties have been selected

                    return $remappedValues;

                } else { //return the output for specific properties

                    $props = array_change_key_case($properties,CASE_LOWER);

                    if(count($props) == 1) {

                        if(array_key_exists($props[0],$remappedValues)) {
                            return $remappedValues[$props[0]];
                        } else {
                            return null;
                        }

                    } else {

                        $output = [];

                        foreach($remappedValues as $key => $value) {

                            if(array_key_exists(strtolower($key),$props)) {
                                $output[$key] = $value;
                            }

                        }

                        return $output;

                    }

                }

            } else { //the query result is e.g. a single string value

                return $row;

            }

        }

        return null;

    }

    //remaps fields for table reading
    protected function RemapArrayToProperties(array $row, FieldPropertyMappingsList $mappings) : array{

        $output = [];

        foreach($row as $key => $value) {

            if(array_key_exists(strtolower($key),$mappings->AsArrayByFieldName)){ //the field is known

                $mapping = $mappings->AsArrayByFieldName[strtolower($key)];

                $output[$mapping->propertyName] = $value;

            } else { //apply the update for a raw field index

                $output[$key] = $value;

            }
        }

        return $output;

    }

}

?>