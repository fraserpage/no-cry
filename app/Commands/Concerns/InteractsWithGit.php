<?php

namespace App\Commands\Concerns;

use Carbon\Carbon;

trait InteractsWithGit
{
    public function checkoutNewBranchForDate($options): string
    {
        ['ticket' => $t, 'branch' => $branchName ] = $options;

        if ($this->workingDirectoryIsDirty()) {
            $this->error('☠️  The working directory is dirty, please commit or stash your changes before running this command.');
            die();
        }

        if (!$branchName){
            $ticket = $t ?: $this->ask('Enter the plugin update ticket number');
            $now = Carbon::now();
            $branchName = "{$ticket}-plugin-updates-{$now->format('Y-m-d')}";
        }

        exec("git checkout -b {$branchName} 2>&1", $output, $result);

        if ($result === 128){
            $this->branchAlreadyExists($branchName);
        }

        return $branchName;
    }

    private function branchAlreadyExists($branchName): void 
    {
        if ($this->confirm("A branch named ".$branchName." already exists. Do you want to use it?", true)){
            exec("git checkout {$branchName}");
        }
        else{
            if ($this->confirm("Want to try a different branch?", true)){
                $newBranch = $this->ask("Ok. Enter a branch to use (new or existing):");
                $this->checkoutNewBranchForDate([
                    'ticket' => null, 
                    'branch' => $newBranch
                ]);
            }
            else{
                $this->line('Goodbye.');
                die();
            }
        }
    }

    private function workingDirectoryIsDirty(): bool
    {
        return ! ! exec("git status --porcelain");
    }
}
