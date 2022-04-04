<?php

namespace App\Commands\Concerns;

use Carbon\Carbon;

trait InteractsWithGit
{
    public function checkoutNewBranchForDate($t): string
    {
        $ticket = $t ? $t : $this->ask('Enter the plugin update ticket number');
        $now = Carbon::now();
        $branchName = "{$ticket}-plugin-updates-{$now->format('Y-m-d')}";

        exec("git checkout -b {$branchName} 2>&1", $output, $result);

        if ($result === 128){
            $this->branchAlreadyExists($branchName);
        }

        return $branchName;
    }

    private function branchAlreadyExists($branchName){
        if ($this->confirm("A branch named ".$branchName." already exists. Do you want to use it?", true)){
            exec("git checkout {$branchName}");
        }
        else{
            $now = Carbon::now();
            $branchPrefix = $this->ask("Ok, lets start over. Let's make a new branch named [WHATEVER]-plugin-updates-{$now->format('Y-m-d')}. What would you like [WHATEVER] to be?");
            $this->checkoutNewBranchForDate($branchPrefix);
        }
    }
}
