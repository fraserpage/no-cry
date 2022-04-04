<?php

namespace App\Commands;

use Carbon\Carbon;
use App\Commands\Concerns\InteractsWithGit;
use App\Commands\Concerns\InteractsWithWpCore;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class UpdateWordpressCliPluginsCommand extends Command
{
    use InteractsWithGit;
    use InteractsWithWpCore;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'please {ticket?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Update the plugins of the WordPress site at the current directory using the WordPress CLI.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->workingDirectoryIsDirty()) {
            $this->error('☠️  The working directory is dirty, please commit or stash your changes before running this command.');
            return;
        }

        $this->checkoutNewBranchForDate( $this->argument('ticket') );

        $this->line("Checking for plugin updates...");

        // ask wordpress cli for the list of current plugins in json format
        exec('lando wp plugin list --format=json', $plugins);

        if (!is_array($plugins)){
            $this->line("🚨🚨🚨🚨🚨🚨🚨 Oh boy, something's wrong. 'lando wp plugin list --format=json' didn't return an array. If wordpress is showing a 'PHP Notice' turning off WP_DEBUG should fix it. Here's a what we got: ");
            var_dump($plugins);
            return;
        }

        // parse the json into an array
        global $arrayKey;
        $arrayKey = 0;
        $parsedPlugins = collect(json_decode($plugins[$arrayKey], true));

        if ($parsedPlugins->isEmpty()){
            $this->error("🚨🚨🚨🚨🚨🚨🚨 Oh boy, something's wrong. We didn't find a list of plugins where we expected it to be. Let's see what 'lando wp plugin list --format=json' returned. If wordpress is showing a 'PHP Notice' turning off WP_DEBUG might fix it. Take a look: ");
            var_dump($plugins);

            if ($this->confirm('Want to try another array key?', true)){
                $arrayKey = $this->ask('Cool. What key?');
                $parsedPlugins = collect(json_decode($plugins[$arrayKey], true));
            }
            else{
                return;
            }
        }

        $this->line("Updating plugins...");

        // loop through the array and update the plugins
        $updatedPlugins = $parsedPlugins->map(function (array $plugin) {

            if ($plugin['update'] === 'none'){
                $this->line("❌ {$plugin['name']} did not require updates.  ");
                return;
            }
            
            if ($plugin['update'] !== 'available'){
                $this->error("🚨 {$plugin['name']}: {$plugin['update']}  ");
                return;
            }

            exec('lando wp plugin update '.$plugin['name'].' --format=json', $output);

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

            $updated = "{$plugin['name']} from {$updatedPlugin[0]['old_version']} to version {$updatedPlugin[0]['new_version']}  ";
            $commitMessage = "deps(plugin): {$updated}";

            /**
             * @todo limit the scope of the `git add -A` to the current plugin path
             */
            exec('git add -A');
            exec("git commit -m '{$commitMessage}'");

            $this->info("✅ {$updated}");
            return "{$updated}";
        });

        $wpCore = '';
        if ($this->confirm("Want to update WP Core?",true)){
            $wpCore = $this->updateWPCore();
        }

        // Output what was updated
        $this->newLine(1);
        $this->info("------------------------------");
        $now = Carbon::now();
        $title = "## Updated Plugins for {$now->format('F')}:  ";
        $this->info($title);
        $this->info("------------------------------");
        
        global $updateCount;
        $updateCount = 0;
        $updated = $updatedPlugins->filter()->map(function($update){
            global $updateCount;
            $updateCount++;
            $string = "{$updateCount}. {$update}  ";
            $this->info($string);
            return $string;
        });
        $this->newLine(1);
        $this->info($wpCore);

        if ($this->confirm("Copy updates to clipboard?", true)){
            $imploded = $title.'\n\n'.$updated->implode('\n').'\n\n'.$wpCore;
            exec("echo '{$imploded}' | pbcopy");
            $this->info("Copied.");
        }

    }

    public function workingDirectoryIsDirty(): bool
    {
        return ! ! exec("git status --porcelain");
    }

    /**
     * Define the command's schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
