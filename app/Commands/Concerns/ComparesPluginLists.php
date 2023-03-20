<?php

namespace App\Commands\Concerns;

/**
 * Used in composer based flow 
 */
trait ComparesPluginLists
{
    use Helpers;

    public function getUpdatedPlugins(array $oldPlugins, array $newPlugins, string $lando): object
    {
        $newPluginNames = array_column($newPlugins, 'name');

        return collect($oldPlugins)
            ->map(function($oldPlugin, $key) use ($newPlugins, $newPluginNames, $lando) {

                $newKey = array_search($oldPlugin['name'], $newPluginNames);
                $newVersion = $newKey !== false ? $newPlugins[$newKey] : false;
                
                if($newVersion === false){
                    $this->error("Error updating {$oldPlugin['name']} error. Plugin not found in wp plugin list");
                    var_dump($newPlugins);
                    return;
                }

                if ($oldPlugin['version'] === $newVersion['version']){
                    if ($oldPlugin['update'] === 'available'){
                        $this->error("{$oldPlugin['name']} has version {$oldPlugin['update_version']} available but was not updated from current version {$newVersion['version']}. Update composer version requirements if you wish to allow this update.");
                    }
                    return;
                }

                if ($newVersion['update_version']){
                    $this->info("{$newVersion['name']} was updated to {$newVersion['version']} from {$oldPlugin['version']}. Version {$newVersion['update_version']} available. Change composer version requirement to update.");
                }

                return "{$newVersion['title']} from {$oldPlugin['version']} to version {$newVersion['version']}  ";
                
            });
    }
}
