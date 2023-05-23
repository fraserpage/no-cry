<?php

namespace App\Commands\Concerns;

use Carbon\Carbon;

trait InteractsWithGit
{
    public function checkoutNewBranchForDate($options): string
    {
        ['ticket' => $ticket_argument, 'branch' => $branch_name, 'config' => $config] = $options;

        if ($this->workingDirectoryIsDirty()) {
            $this->error('☠️  The working directory is dirty, please commit or stash your changes before running this command.');
            die();
        }

        if (empty($branch_name)) {
            // config[ticket] found & ticket_argument arg specified
            if (!empty($config->ticket) && !empty($ticket_argument)) {
                $ticket = $this->menu("You entered ticket value {$ticket_argument}. A ticket value '{$config->ticket}' was found in config. Which should we use?", [
                    $ticket_argument,
                    $config->ticket,
                ])->open();
            }
            // config[ticket] found and ticket_argument arg not specified
            else if (!empty($config->ticket) && empty($ticket_argument)) {
                $ticket = $this->ask("A ticket value '{$config->ticket}' was found in config. Press enter to use that or enter a new value.", $config->ticket);
            } 
            else {
                $ticket = $ticket_argument ?: $this->ask('Enter the plugin update ticket number');
            }

            $now = Carbon::now();
            $branch_name = "{$ticket}-plugin-updates-{$now->format('Y-m-d')}";
        }


        exec("git checkout -b {$branch_name} 2>&1", $output, $result);

        if ($result === 128) {
            $this->branchAlreadyExists($branch_name);
        }

        return $branch_name;
    }

    private function branchAlreadyExists($branch_name): void
    {
        if ($this->confirm("A branch named " . $branch_name . " already exists. Do you want to use it?", true)) {
            exec("git checkout {$branch_name}");
        } else {
            if ($this->confirm("Want to try a different branch?", true)) {
                $new_branch = $this->ask("Ok. Enter a branch to use (new or existing):");
                $this->checkoutNewBranchForDate([
                    'ticket' => null,
                    'branch' => $new_branch
                ]);
            } else {
                $this->line('Goodbye.');
                die();
            }
        }
    }

    private function workingDirectoryIsDirty(): bool
    {
        return !!exec("git status --porcelain");
    }
}
