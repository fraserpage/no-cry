<?php

namespace App\Commands\Concerns;

trait UpdatesPlugins
{
    public function updatePlugins($parsedPlugins, $lando): object
    {
      global $lando;
      $this->line("Updating plugins...");

      return $parsedPlugins->map(function (array $plugin) {
        
        global $lando;

        if ($plugin['update'] === 'none'){
            $this->line("❌ {$plugin['name']} did not require updates.  ");
            return;
        }
        
        if ($plugin['update'] !== 'available'){
            $this->error("🚨 {$plugin['name']}: {$plugin['update']}  ");
            return;
        }

        $this->line("Updating {$plugin['name']}...");
        exec("{$lando} wp plugin update {$plugin['name']} --format=json --quiet", $output);

        if (!is_array($output)){
            $this->error("🚨 {$plugin['name']}: {$output}  ");
            return;
        }
        
        global $arrayKey;
        $updatedPlugin = json_decode($output[$arrayKey], true);

        if (!is_array($updatedPlugin)){
            $this->error("🚨🚨🚨 {$plugin['name']}: Something went wrong. Here's what we know: ");
            var_dump($output);
            return;
        }

        $properName = exec("{$lando} wp plugin get {$plugin['name']} --field=title --quiet");

        $updated = "{$properName} from {$updatedPlugin[0]['old_version']} to version {$updatedPlugin[0]['new_version']}  ";
        $commitMessage = "deps(plugin): {$updated}";

        /**
         * @todo limit the scope of the `git add -A` to the current plugin path
         */
        exec('git add -A');
        exec("git commit -m '{$commitMessage}'");

        $this->info("✅ {$updated}");
        return "{$updated}";
      });
    }
}
