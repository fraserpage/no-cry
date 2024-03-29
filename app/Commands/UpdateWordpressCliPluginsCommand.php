<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Concerns\ChecksForPluginUpdates;
use App\Commands\Concerns\GetConfigFile;
use App\Commands\Concerns\GivesFinalOutput;
use App\Commands\Concerns\InteractsWithGit;
use App\Commands\Concerns\InteractsWithWpCore;
use App\Commands\Concerns\UpdatesPlugins;

class UpdateWordpressCliPluginsCommand extends Command
{
    use ChecksForPluginUpdates;
    use GivesFinalOutput;
    use InteractsWithGit;
    use InteractsWithWpCore;
    use UpdatesPlugins;
    use GetConfigFile;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'please {ticket?} {--b|branch=} {--l|lando}';

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
        $config = $this->getConfig();

        if(!empty($config->lando)){
            $this->info('"lando" value found in .no-cry.json. Using that setting.');
            $lando = $config->lando ? 'lando' : '';
        }
        else{
            $lando = $this->option('lando') ? 'lando' : '';
        }
        
        // Checkout our branch
        $this->checkoutNewBranchForDate([
            'ticket' => $this->argument('ticket'), 
            'branch' => $this->option('branch'),
            'config' => $config
        ]);

        // Find out what needs updates
        $parsedPlugins = $this->getPluginUpdates($lando);

        // Loop through plugins and update them
        $updatedPlugins = $this->updatePlugins($parsedPlugins, $lando);
        
        // Optionally update WP Core
        $wpCore = $this->updateWPCore($lando);
        
        // Output what was updated
        $this->outputResults($updatedPlugins, $wpCore, $this->argument('ticket'));

    }
}
