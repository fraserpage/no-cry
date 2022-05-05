<?php

namespace App\Commands\Concerns;

trait InteractsWithWpCore
{
    public function updateWPCore($lando): string
    {
        if ($this->confirm("Want to update WP Core?",true)){
            
            $result = '';
            exec("{$lando} wp core check-update --format=json 2>&1", $updateCheck);

            if (count($updateCheck)){
                $this->line("WordPress update avialable.");
                exec("{$lando} wp core version 2>&1", $currentVersion, $getVersionResult);

                $this->line("Updating Wordpress...");
                exec("{$lando} wp core update 2>&1", $updateVersion, $updateResult);
                exec("{$lando} wp core version 2>&1", $newVersion, $getNewVersionResult);
                
                $result = "Wordpress from $currentVersion[0] to $newVersion[0]";
                $this->line($result);

                exec('git add -A');
                exec("git commit -m 'deps(wp-core): {$result}'");
            }
            else{
                $this->line("WordPress is at the latest version.");
            }

            return $result;
        }
        else{
            return '';
        }
    }
}
