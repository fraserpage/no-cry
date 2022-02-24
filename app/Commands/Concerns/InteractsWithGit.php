<?php

namespace App\Commands\Concerns;

use Carbon\Carbon;

trait InteractsWithGit
{
    public function checkoutNewBranchForDate(): string
    {
        $now = Carbon::now();
        $branchName = "plugin-updates/{$now->format('Y-m-d')}";

        exec("git checkout -b {$branchName}");

        return $branchName;
    }
}
