<?php

namespace App\Commands\Concerns;

trait UpdatesPlugins
{
    use Helpers;

    public function updatePlugins($parsedPlugins, $lando): object
    {
      $this->line("Updating plugins...");

      return collect($parsedPlugins)->map(function (array $plugin) use ($lando) {

        if ($plugin['update'] === 'none'){
            $this->line("❌ {$plugin['name']} did not require updates.  ");
            return;
        }
        
        if ($plugin['update'] !== 'available'){
            $this->error("🚨 {$plugin['name']}: {$plugin['update']}  ");
            return;
        }

        $this->line("Updating {$plugin['name']}...");
        exec("{$lando} wp plugin update {$plugin['name']} --format=json --quiet --skip-themes", $output);

        if (!is_array($output) || !count($output)){
            $this->error("🚨 {$plugin['name']}: Something wrong!");
            var_dump($output);
            return;
        }
        
        $updatedPlugin = $this->getCommandOutput($output, "name", "🚨🚨🚨 {$plugin['name']}: Something went wrong. Here's what we know: ");

        if ($updatedPlugin[0]['status'] === 'Error'){
            $this->error("🚨🚨🚨 {$plugin['name']}: wp plugin update gave status 'Error' when attempting to upgrade from {$updatedPlugin[0]['old_version']} to {$updatedPlugin[0]['new_version']}. This is sometimes the result of unlicensed pro plugins.");
            return;
        }

        $updated = "{$plugin['title']} from {$updatedPlugin[0]['old_version']} to version {$updatedPlugin[0]['new_version']}  ";
        $commitMessage = "deps(plugin): {$updated}";

        /**
         * @todo limit the scope of the `git add -A` to the current plugin path
         */
        exec('git add -A');
        exec('git commit -m "'.$commitMessage.'"');

        $this->info("✅ {$updated}");
        return "{$updated}";
      });
    }
}
