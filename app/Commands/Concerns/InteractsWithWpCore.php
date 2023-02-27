<?php

namespace App\Commands\Concerns;

trait InteractsWithWpCore
{
    use Helpers;

    public function updateWPCore($lando): string
    {
           
        $result = '';
        exec("{$lando} wp core check-update --format=json 2>&1", $updateCheck);

        if (count($updateCheck)){

            $updateResults = $this->getCommandOutput($updateCheck, 'version',"Something went wrong checking for a Wordpress update.");

            if (is_array($updateResults)){

                if ($this->confirm("There's a WordPress update available. Install it?",true)){

                    exec("{$lando} wp core version --quiet --skip-themes 2>&1", $currentVersion);
    
                    $this->line("Updating Wordpress...");
                    exec("{$lando} wp core update --quiet --skip-themes 2>&1", $updateVersion);
    
                    exec("{$lando} wp core version --quiet --skip-themes 2>&1", $newVersion);
                    
                    $result = "Wordpress from {$currentVersion[count($currentVersion)-1]} to {$newVersion[count($newVersion)-1]}";
                    $this->line($result);
    
                    exec('git add -A');
                    exec('git commit -m "deps(wp-core): '.$result.'"');
    
                    return $result;
                }
                else{
                    return '';
                }
            }

           
        }
        else{
            $this->line("------------------------------");
            $this->newLine(1);
            $this->line("WordPress is at the latest version.");
            return '';
        }
    }
}
