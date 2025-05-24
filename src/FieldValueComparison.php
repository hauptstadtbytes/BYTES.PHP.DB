<?php
//set the namespace
namespace BytesPhp\Db;

//the compare class
class FieldValueComparison{

    //public properties
    public string $operator = "=";
    public $value = null;

    //constructor method(s)
    public function __construct($value, string $operator = null) {

        $this->value = $value;

        if(!is_null($operator)) {

            $this->operator = $operator;
            
        }

    }

    //public static method parsing a string statement to an object instance
    public static function Parse(string $statement) {

        $matches = [];
        preg_match('/\[\S]/', $statement, $matches);

        if(count($matches) > 0) {

            $field = preg_replace('/\[\S]/', '', $statement);
            $operator = str_replace(["[","]"],"",$matches[0]);

            return new FieldValueComparison($field, $operator); 

        } else {

            return new FieldValueComparison($statement);

        }

    }

}

?>