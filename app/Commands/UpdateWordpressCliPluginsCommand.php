<?php

namespace App\Commands;

use App\Commands\Concerns\InteractsWithGit;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class UpdateWordpressCliPluginsCommand extends Command
{
    use InteractsWithGit;

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

        $branchName = $this->checkoutNewBranchForDate( $this->argument('ticket') );

        // ask wordpress cli for the list of current plugins in json format
        exec('lando wp plugin list --format=json', $plugins);

        if (!is_array($plugins)){
            $this->line("🚨🚨🚨🚨🚨🚨🚨 Oh boy, something's wrong. If wordpress is showing a 'PHP Notice' turning off WP_DEBUG should fix it. Here's a what 'lando wp plugin list --format=json' returned: ");
            var_dump($plugins);
            return;
        }

        // parse the json into an array
        $parsedPlugins = collect(json_decode($plugins[0], true));

        // loop through the array and update the plugins
        $updatedPlugins = $parsedPlugins->map(function (array $plugin) {

            if ($plugin['update'] === 'none'){
                $this->line("❌ {$plugin['name']} did not require updates.  ");
                return;
            }
            
            if ($plugin['update'] !== 'available'){
                $this->line("🚨 {$plugin['name']}: {$plugin['update']}  ");
                return;
            }

            exec('lando wp plugin update '.$plugin['name'].' --format=json', $output);

            if (!is_array($output)){
                $this->line("🚨 {$plugin['name']}: {$output}  ");
                return;
            }
            
            $updatedPlugin = json_decode($output[0], true);

            if (!is_array($updatedPlugin)){
                $this->line("🚨🚨🚨 {$plugin['name']}: Something went wrong. Here's what we know: ");
                var_dump($updatedPlugin);
                return;
            }

            $updated = "{$plugin['name']} from {$updatedPlugin[0]['old_version']} to version {$updatedPlugin[0]['new_version']}  ";
            $commitMessage = "deps(plugin): {$updated}";

            /**
             * @todo limit the scope of the `git add -A` to the current plugin path
             */
            exec('git add -A');
            exec("git commit -m '{$commitMessage}'", $output);

            $this->info("✅ {$updated}");
        });
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
