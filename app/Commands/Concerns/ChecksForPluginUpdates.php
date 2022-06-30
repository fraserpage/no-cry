<?php

namespace App\Commands\Concerns;

trait ChecksForPluginUpdates
{
    public function getPluginUpdates($lando): object
    {
        $this->line("Checking for plugin updates...");

        // ask wordpress cli for the list of current plugins in json format
        exec( "{$lando} wp plugin list --format=json --quiet", $plugins);

        if (!is_array($plugins) || !count($plugins)){
            $this->error("🚨🚨🚨🚨🚨🚨🚨 Oh boy, something's wrong. '{$lando} wp plugin list --format=json' didn't return an array. If wordpress is showing a 'PHP Notice' turning off WP_DEBUG should fix it. Here's a what we got: ");
            var_dump($plugins);
            die();
        }

        // parse the json into an array
        global $arrayKey;
        $arrayKey = 0;
        $parsedPlugins = collect(json_decode($plugins[$arrayKey], true));

        if ($parsedPlugins->isEmpty()){
            $this->error("🚨🚨🚨🚨🚨🚨🚨 Oh boy, something's wrong. We didn't find a list of plugins where we expected it to be. Let's see what '{$lando} wp plugin list --format=json' returned. If wordpress is showing a 'PHP Notice' turning off WP_DEBUG might fix it. Take a look: ");
            var_dump($plugins);

            if ($this->confirm('Want to try another array key?', true)){
                $arrayKey = $this->ask('Cool. What key?');
                $parsedPlugins = collect(json_decode($plugins[$arrayKey], true));
            }
            else{
                die();
            }
        }

        return $parsedPlugins;
    }
}
