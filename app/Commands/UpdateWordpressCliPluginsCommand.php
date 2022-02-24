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
    protected $signature = 'wp:update-plugins';

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

        $branchName = $this->checkoutNewBranchForDate();

        // ask wordpress cli for the list of current plugins in json format
        exec('wp plugin list --format=json', $plugins);

        // parse the json into an array
        $parsedPlugins = collect(json_decode($plugins[0], true));

        // loop through the array and update the plugins
        $updatedPlugins = $parsedPlugins->map(function (array $plugin) {
            exec('wp plugin update '.$plugin['name'].' --format=json', $output);

            $updatedPlugin = array_key_exists(0, $output) ? json_decode($output[0], true)[0] : [];

            if (empty($updatedPlugin)) {
                $this->line("❌ {$plugin['name']} did not require updates.");

                return;
            }

            $updated = "{$plugin['name']} from {$updatedPlugin['old_version']} to version {$updatedPlugin['new_version']}";
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
