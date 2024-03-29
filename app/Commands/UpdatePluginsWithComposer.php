<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Concerns\ChecksForPluginUpdates;
use App\Commands\Concerns\ComparesPluginLists;
use App\Commands\Concerns\GetConfigFile;
use App\Commands\Concerns\GivesFinalOutput;
use App\Commands\Concerns\Helpers;
use App\Commands\Concerns\InteractsWithGit;
use App\Commands\Concerns\InteractsWithWpCore;

class UpdatePluginsWithComposer extends Command
{
    use ChecksForPluginUpdates;
    use ComparesPluginLists;
    use GivesFinalOutput;
    use InteractsWithGit;
    use InteractsWithWpCore;
    use Helpers;
    use GetConfigFile;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'composer {ticket?} {--b|branch=} {--l|lando} {--t|valet}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run composer update and get a nicely formatted list of updates back';

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

        $valet = $this->option('valet') ? 'valet' : '';

        // Checkout our branch
        $this->checkoutNewBranchForDate([
            'ticket' => $this->argument('ticket'), 
            'branch' => $this->option('branch'),
            'config' => $config
        ]);

        // Get current core and plugin versions
        $preUpdatePlugins = $this->getPluginUpdates($lando);
        exec("{$lando} wp core version --quiet --skip-themes 2>&1", $preUpdateCore);
        $preUpdateCoreVersion = $preUpdateCore[count($preUpdateCore)-1];

        // Run composer update
        $this->info("running '{$valet} composer update'...");
        exec("{$valet} composer update");

        // Get new core and plugin versions
        $postUpdatePlugins = $this->getPluginUpdates($lando);
        exec("{$lando} wp core version --quiet --skip-themes 2>&1", 
        $postUpdateCore);
        $postUpdateCoreVersion = $postUpdateCore[count($postUpdateCore)-1];

        // Get back list of updated plugins
        $updatedPlugins = $this->getUpdatedPlugins($preUpdatePlugins, $postUpdatePlugins, $lando);

        // Get WP Core status
        $wpCoreUpdate = "";
        if($postUpdateCoreVersion !== $preUpdateCoreVersion){
            $wpCoreUpdate = "Wordpress from {$preUpdateCoreVersion} to {$postUpdateCoreVersion}";
        }
        else{
            exec("{$lando} wp core check-update --format=json 2>&1", $updateCheck);
            if (count($updateCheck)){

                $updateResults = $this->getCommandOutput($updateCheck, 'version',"Something went wrong checking for a Wordpress update.");
    
                if (!empty($updateResults['version'])){

                    $this->info("Currently on WP {$postUpdateCoreVersion}. Wordpress version {$updateResults['version']} is available. If WP Core is managed by composer update the version requirements to upgrade.");
                }
            }
        }

        // Give results
        $this->outputResults($updatedPlugins, $wpCoreUpdate, $this->argument('ticket'));
    }
}
