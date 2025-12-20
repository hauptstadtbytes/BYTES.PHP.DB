<?php
//set the namespace
namespace BytesPhp\Db;

//add internal namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;
use BytesPhp\Db\FieldValueComparison as FieldValueComparison;

//the DB entity base class
abstract class DBReadOnlyCachedEntity{

    //protected properties
    protected array $data = [];

    //public static properties
    public static ?string $table = null;
    public static array $fieldMappings = [];

    //constructor method(s)
    public function __construct(array $data){

        $this->data = $data;

    }

    //public (magic) getter method, for reading properties
    public function __get(string $property) {
            
        //check for a property overwrite
        $data = $this->ReadProperty($property);

        if(!is_null($data)){
            return $data;
        }

        //return the default value
        switch(strtolower($property)) {

            case "asarray":
                return $this->ParseAsArray();
                break;

            case "asjson":
                return json_encode($this->asarray);
                break;
                
            default:
                return $this->Read($property);
            
        }
        
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

            return new $class($row);

        }

        return null;

    }

    //public static method returning a list of all entities found
    public static function All(DBConnection $connection, array $where = null) : array {

        //get the child class type name
        $class = get_called_class();

        //return
        $output = [];

        foreach($connection->select(static::$table,"*",self::ParseWhere($where)) as $row) { //loop for all rows found

            $output[] = new $class($row);

        }

        return $output;

    }

    //protected method, intended for overwriting proptery reading
    protected function ReadProperty($property) {

        return null;

    }

    //protected method, reading the database fields
    protected function Read($property) {

        //try to get the key of the property
        $key = null;

        if(array_key_exists(strtolower($property),static::$fieldMappings)){
            $key = static::$fieldMappings[strtolower($property)];
        }  

        if(is_null($key)){ //there was no property (key) found

            return null;

        } else { //the property (key) was found

            return $this->data[$key];

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

    //creates the array representation of the mapped field
    private function ParseAsArray() : array {

        $output = [];

        foreach($this->data as $key => $val) {

            $newKey = array_search(strtolower($key),static::$fieldMappings);

            if($newKey === false){ //there was no mapping found
                $output[$key] = $val;
            } else { //the mapping was found
                $output[$newKey] = $val;
            }
        }

        return $output;

    }

}
?>