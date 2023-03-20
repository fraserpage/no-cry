<?php

namespace App\Commands\Concerns;

trait ChecksForPluginUpdates
{
    use Helpers;

    public function getPluginUpdates($lando) : array
    {
        $this->line("Checking for plugin updates...");

        // ask wordpress cli for the list of current plugins in json format
        exec( "{$lando} wp plugin list --fields=name,update,version,update_version,title --format=json --quiet --skip-themes 2>&1", $plugins, $result_code);

        if (!is_array($plugins) || !count($plugins)){
            $this->error("🚨🚨🚨🚨🚨🚨🚨 Oh boy, something's wrong. '{$lando} wp plugin list --format=json' didn't return an array. If wordpress is showing a 'PHP Notice' turning off WP_DEBUG should fix it. Here's a what we got: ");
            var_dump($plugins);
            die();
        }

        return $this->getCommandOutput(
            $plugins, 
            "name", 
            "🚨🚨🚨🚨🚨🚨🚨 Oh boy, something's wrong. We didn't find a list of plugins where we expected it to be. Let's see what '{$lando} wp plugin list --format=json' returned. If wordpress is showing a 'PHP Notice' turning off WP_DEBUG might fix it. Take a look: ",
            true
        );
    }

}