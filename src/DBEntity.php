<?php
//set the namespace
namespace BytesPhp\Db;

//add internal namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;
use BytesPhp\Db\FieldValueComparison as FieldValueComparison;

//the DB entity base class
abstract class DBEntity{

    //protected properties
    protected DBConnection $connection;
    protected int $id;

    //public properties
    public static ?string $table = null;
    public static array $fieldMappings = [];
    public static ?string $idField = null;

    //constructor method(s)
    public function __construct(DBConnection $connection, int $id){

        $this->connection = $connection;
        $this->id = $id;

    }

    //public (magic) getter method, for reading properties
    public function __get(string $property) {
            
        switch(strtolower($property)) {

            case "id":
                return $this->id;
                break;

            case "asarray":
                return $this->Read();
                break;

            case "asjson":
                return json_encode($this->Read());
                break;
                
            default:
                return $this->Read([$property]);
            
        }
        
    }

    //public (magic) setter method, for writing properties
    public  function __set($property, $value) {

        switch(strtolower($property)) {

            case "asarray":
                return $this->Write($value);
                break;

            case "asjson":
                return $this->Write(json_decode($value,true));
                break;
                
            default:
                return $this->Write([$property => $value]);
            
        }

    }

    //public static method for creating a new entity
    public static function Create(DBConnection $connection, array $properties) {

        //get the child class type name
        $class = get_called_class();

        //remap the properties to db fields
        $tmpMappings = self::GetTmpFieldMappings(array_keys($properties));

        $insertValues = [];

        foreach($properties as $key => $val) {

            if(array_key_exists($key,$tmpMappings)) {

                $insertValues[$tmpMappings[$key]] = $val;

            }

        }

        //insert the new item
        $connection->insert(static::$table,$insertValues);

        //return the output value
        return new $class($connection,$connection->id());

    }

    //public static method returning the first entity found
    public static function First(DBConnection $connection, array $where = null): ?DBEntity {

        //get the child class type name
        $class = get_called_class();

        //parse the 'where' statement
        if(is_null($where)){
            $where = [];
        } else {
            $where = self::ParseWhere($where);
        }

        $where["LIMIT"] = 1;

        foreach($connection->select(static::$table,"*",$where) as $row) { //loop for the first row only

            return new $class($connection,$row[static::$idField]);

        }

        return null;

    }

    //public static method returning a list of all entities found
    public static function All(DBConnection $connection, array $where = null) : array {

        //get the child class type name
        $class = get_called_class();

        //return the output value
        $output = [];

        foreach($connection->select(static::$table,"*",self::ParseWhere($where)) as $row) { //loop for all rows found

            $output[] = new $class($connection,$row[static::$idField]);

        }

        return $output;

    } 

    //public method, deleting the current item
    public function Delete() {

        $this->connection->delete(static::$table,[static::$idField => $this->id]);

    }

    //private method, reading the database fields
    private function Read(array $fields = null) {

        //assemble the selector, mapping known properties to db table fiels ignoring cases/ using '*' as default
        $selector = "*";

        $tmpMappings = [];

        if(!is_null($fields)){

            $tmpMappings = $this->GetTmpFieldMappings($fields);

            if(!empty($tmpMappings)) {

                $selector = implode(",",$tmpMappings);

            }

        }

        //query the database
        foreach($this->connection->select(static::$table,$selector,[static::$idField => $this->id,"LIMIT" => 1]) as $row) { //loop for the first row only

            if(is_array($row)) {

                //create the output value
                $output = [];

                //add the default mapping if no tmp mapping was defined
                if(empty($tmpMappings)) {

                    $tmpMappings = static::$fieldMappings;

                }

                //create the output value
                if(!is_null($fields)){ //a dedicated set of fields has been requested

                    if(count($fields) == 1) {

                        if(array_key_exists($fields[0],$tmpMappings)) {

                            return $row[$tmpMappings[$fields[0]]];

                        } else {

                            return null;

                        }

                    } else {

                        foreach($fields as $field) {

                            if(array_key_exists($field,$tmpMappings)) {

                                $output[$field] = $row[$tmpMappings[$field]];

                            } else {

                                $output[$field] = null;

                            }

                        }

                    }

                } else { //all (default) fields have been requested

                    foreach($tmpMappings as $property => $fieldName) {

                        if(array_key_exists($fieldName,$row)){

                            $output[$property] = $row[$fieldName];

                        } else {

                            $output[$property] = null;

                        }
                    
                    }

                }

                //return the output
                return $output;

            } else { //the query result is e.g. a single string value

                return $row;

            }

        }

        return null;

    }

    //private method, writing to database
    private function Write(array $properties, array $where = null) {

        //remap the properties to db fields and prepare update statement
        $tmpMappings = $this->GetTmpFieldMappings(array_keys($properties));

        $updates = [];

        foreach($properties as $key => $val) {

            if(array_key_exists($key,$tmpMappings)) {

                if($tmpMappings[$key] != static::$idField) { //prevent ID from being updated

                    $updates[$tmpMappings[$key]] = $val;

                }

            }

        }

        if(!empty($updates)){ //check for any valid updates

            //parse the 'where' statement
            if(is_null($where)) {

                $where = [static::$idField => $this->id];

            } else {

                $where = $this->ParseWhere($where);

            }

            //update the data set
            $this->connection->update(static::$table,$updates,$where);

        }

    }

    //public static method returning an array of field-mappings, matching the format given
    public static function GetTmpFieldMappings(array $fields) {

        $output = [];

        $knownFields = array_change_key_case(static::$fieldMappings,CASE_LOWER);

        foreach($fields as $field) {

            if(array_key_exists(strtolower($field),$knownFields)){

                    $output[$field] = $knownFields[strtolower($field)];

            } 

        }

        return $output;

    }

    //public static method parsing the 'where' statement
    public static function ParseWhere(?array $where) {

        //check for null
        if(is_null($where)) {
            return null;
        }

        //parse to a list of typed comparing statements
        $comparingList = [];

        foreach($where as $field => $value) {

            if(is_a($value,'BytesPhp\Db\FieldValueComparison')) {

                $comparingList[$field] = $value;

            } else {

                $matches = [];
                preg_match('/\[\S]/', $field, $matches);

                if(count($matches) > 0) {

                    $cleanField = preg_replace('/\[\S]/', '', $field);
                    $operator = str_replace(["[","]"],"",$matches[0]);

                    $comparingList[$cleanField] = new FieldValueComparison($value,$operator); 

                } else {

                    $comparingList[$field] = new FieldValueComparison($value);

                }

            }

        }

        //create the medoo compatible 'where' list, translating properties to DB field name(s)
        $tmpMappings = self::GetTmpFieldMappings(array_keys($comparingList));

        $output = [];

        foreach($comparingList as $field => $comp) {

            if(array_key_exists($field,$tmpMappings)) {

                $output[$tmpMappings[$field]."[".$comp->operator."]"] = $comp->value;

            }
        }

        //return the output value
        return $output;

    }

}
?>