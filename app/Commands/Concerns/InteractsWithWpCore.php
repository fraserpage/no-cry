<?php

namespace App\Commands\Concerns;

trait InteractsWithWpCore
{
    public function updateWPCore($lando): string
    {
           
        $result = '';
        exec("{$lando} wp core check-update --format=json 2>&1", $updateCheck);

        if (count($updateCheck)){
            if ($this->confirm("There's a WordPress update available. Install it?",true)){

                exec("{$lando} wp core version 2>&1", $currentVersion, $getVersionResult);

                $this->line("Updating Wordpress...");
                exec("{$lando} wp core update 2>&1", $updateVersion, $updateResult);
                exec("{$lando} wp core version 2>&1", $newVersion, $getNewVersionResult);
                
                $result = "Wordpress from $currentVersion[0] to $newVersion[0]";
                $this->line($result);

                exec('git add -A');
                exec("git commit -m 'deps(wp-core): {$result}'");

                return $result;
            }
            else{
                return '';
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
