<?php

namespace App\Commands\Concerns;

trait Helpers
{
    /**
     * getCommandOutput
     * 
     * Gets WP CLI JSON data from command output.
     * 
     * @date 24/2/23
     * @since 1.2
     * 
     * @param array $rawOutput The Command output.
     * @param string $requiredKey The key to search for in the JSON data.
     * @param string $error Error message displayed to user if key not found.
     * @param bool $dieOnError = false Exit the CLI tool if error.
     * @param int $key Key to start looking on. Looks by default to last key in $rawOutput array.
     * @return mixed Returns an array if successful.
     *  
     */
    public function getCommandOutput(
        array $rawOutput, 
        string $requiredKey, 
        string $error = "🚨🚨🚨 Sorry something went wrong.", 
        bool $dieOnError = false,
        int $key = null
    ){
        if(is_null($key)){
          $key = count($rawOutput) - 1; 
        }

        if(empty($rawOutput[$key])){
            $this->doError("error in getCommandOutput. Key {$key} does not exist in rawOutput:", $rawOutput);
            return;
        }

        $decodedOutput = json_decode($rawOutput[$key], true);

        if(is_string($decodedOutput)){
            $regex = '/\[[^\]]+\]/'; // Matches anything inside square brackets
            preg_match($regex, $decodedOutput, $matches);
            $decodedOutput = json_decode($matches[0], true);
        }

        // we expect our decoded output to be an array of plugins each having a (e.g.) name. 
        // check the first plugin has the key (e.g. name) we're looking for.
        if(isset($decodedOutput[0][$requiredKey])){
            return $decodedOutput;
        }
        else if($key > 0){
            $this->getCommandOutput($rawOutput, $requiredKey, $error, $dieOnError, $key - 1);
        }
        else{
            $this->doError($error, $rawOutput, $dieOnError);
        }
    }

    public function doError($message, $dump, $die = false){
        $this->error($message);
        var_dump($dump);

        if($die){
          die();
        }
    }
    
}