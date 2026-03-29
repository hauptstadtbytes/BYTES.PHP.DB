<?php
//set the namespace
namespace BytesPhp\Db;

//the compare class
class FieldValueComparison{

    //public properties
    public string $operator = "=";
    public ?string $fieldName = null;
    public mixed $value = null;

    //constructor method(s)
    public function __construct(string $fieldName, mixed $value, string $operator = null) {

        $this->fieldName = $fieldName;
        $this->value = $value;

        if(!is_null($operator)) {

            $this->operator = $operator;
            
        }

    }

    //public static method parsing a string statement to an object instance
    public static function Parse(string $statement, mixed $value) : FieldValueComparison {

        $matches = [];
        preg_match('/\[\S]/', $statement, $matches);

        if(count($matches) > 0) {

            $field = preg_replace('/\[\S]/', '', $statement);
            $operator = str_replace(["[","]"],"",$matches[0]);

            return new FieldValueComparison($field, $value, $operator); 

        } else {

            return new FieldValueComparison($statement, $value);

        }

    }

    //returns a meedo compatible array key, see 'https://medoo.in/api/where' for more details
    public function GetMeedoKey() {

        return $this->fieldName."[".$this->operator."]";

    }

}

?>