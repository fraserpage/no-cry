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

        exec("git checkout -b {$branchName}");

        return $branchName;
    }
}
