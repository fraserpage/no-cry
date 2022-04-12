<?php

namespace App\Commands\Concerns;

use Carbon\Carbon;

trait GivesFinalOutput
{
    public function outputResults($updatedPlugins, $wpCore): void
    {
      $this->newLine(1);
      $this->info("------------------------------");
      $now = Carbon::now();
      $title = "## Updated Plugins for {$now->format('F')}  ";
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
          $gitHubActions = '\n\n@\n/assign @\n/unassign @\n/label ~"PM Review" \n/unlabel ~"To Do"';
          $imploded = $title.'\n\n'.$updated->implode('\n').'\n\n'.$wpCore.$gitHubActions;
          exec("echo '{$imploded}' | pbcopy");
          $this->info("Copied.");
      }

    }
}
