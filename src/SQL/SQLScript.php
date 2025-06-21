<?php
//set the namespace
namespace BytesPhp\Db\SQL;

//add namespace(s) from 'BYTES.NET' framework required
use BytesPhp\Logging\Log as Log;
use BytesPhp\Logging\InformationLevel as InformationLevel;

//add internal namespace(s) required
use BytesPhp\Db\DBConnection as DBConnection;

//the migration manager class
class SQLScript{

    //private variable(s)
    private string $path;
    private array $lines = [];
    private Log $log;

    private DBConnection $connection;

    //constructor method(s)
    public function __construct(string $path = null) {

        $this->ResetLog();

        if(!is_null($path)) {
            $this->Read($path);
        }

    }

    //public (magic) getter method, for reading properties
    public function __get(string $property) {
            
        switch(strtolower($property)) {

            case "path":
                return $this->path;
                break;

            case "log":
                return $this->log;
                break;

            case "commands":
                return $this->lines;
                break;
                
            default:
                return null;
            
        }
        
    }

    //public function ready the script from disk file (based on the article found at 'https://stackoverflow.com/questions/4027769/running-mysql-sql-files-in-php')
    public function Read(string $path) {

        //validate the parameter(s) given
        if(!is_file($path)) {
            throw new \Exception("Unable to find script file at '".$path."'");
        }

        //load file data
        $content = \file_get_contents($path);

        //remove comments
        $commands = '';

        foreach(explode("\n",$content) as $line){

            $line = \trim($line);

            if( $line && !\string_startswith($line,'--') ){
                $commands .= $line . "\n";
            }

        }

        //set the variable(s)
        $this->lines = explode(";",$commands);
        $this->path = $path;

    }

    //public method executing the command(s)
    public function Execute(DBConnection $connection) {

        //reset the log
        $this->ResetLog();
        
        //execute the query (writing the result to the log)
        $counter = 0;

        foreach($this->commands as $command) {

            $counter++;

            $command = \trim($command);

            if($command === "") { //check for empty commands

                $this->log->Info($counter.": Skipped");

            } else {

                try{

                    $result = $connection->query($command)->fetchAll(); //execute the qury

                    if(count($result) == 0) { //analyze the PDOStatement data returned

                        $this->log->Info($counter.": Executed successful");

                    } else {

                        $this->log->Info($counter.": Failed");

                    }

                } 
                catch (\PDOException $e) {
                    $this->log->Warning($counter.": Failed with message '".$e->getMessage()."'");
                }
                catch(\Exception $e) {

                    $this->log->Warning($counter.": Failed with message '".$e->getMessage()."'");

                }

            }

        }

    }

    //private function resetting the internal execution log
    private function ResetLog() {

        $this->log = new Log();
        $this->log->Threshold = InformationLevel::Debug;

    }

}
?>