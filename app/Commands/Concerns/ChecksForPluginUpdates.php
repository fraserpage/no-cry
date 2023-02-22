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

        return $this->pluginUpdatesFromArray($plugins, $lando, count($plugins) - 1);
    }

    // Recursively traverse the array looking for the updates
    private function pluginUpdatesFromArray(array $plugins, string $lando, int $key, int $loopCount = 1){
        
        var_dump('loopCount:', $loopCount);
        var_dump('key:', $key);
        $parsedPlugins = collect(json_decode($plugins[$key], true));

        if ($parsedPlugins->isEmpty() || !isset($parsedPlugins[0]['name'])){
            if ($key === 0){
                $this->error("🚨🚨🚨🚨🚨🚨🚨 Oh boy, something's wrong. We didn't find a list of plugins where we expected it to be. Let's see what '{$lando} wp plugin list --format=json' returned. If wordpress is showing a 'PHP Notice' turning off WP_DEBUG might fix it. Take a look: ");
    
                var_dump($plugins);
    
                die();
            }
    
            // Try the next array key
            $this->pluginUpdatesFromArray($plugins, $lando, $key - 1, $loopCount + 1);
        }

        return $parsedPlugins;
    }

}