# BW WP plugin update tool 

Creates a CLI tool named bw-lando (though we might want to pick a nicer name!) that makes updating plugins kind of ok.

## Build Requrirements
- PHP 7.4

## Building the tool
1. composer install
2. run `php bw-lando app:build` to build the tool
3. run ` mv builds/bw-lando /usr/local/bin` to move the build to your bin folder (you might need to add `sudo` to that command.)

## Using the tool
1. make sure the local `master` branch of the site you want to update is up to date with the remote
1. make sure that all paid plugins are licenced locally in order to recieve updates
1. run `bw-lando wp:update-plugins` and watch it do its magic
1. you'll get an output of plugins that were updated, skipped or had errors
1. fix any errors
1. at this point all plugins should be up to date, and each update committed
1. push your changes, merge and deploy to staging for testing

## To do
1. add option to specify ticket number in branch name
1. figure out a nice solution to update paid plugins (Integrate SatisPress??)
1. Figure out a way to update Must Use plugins

## Laravel Zero

This project uses Laravel Zero. For full documentation, visit [laravel-zero.com](https://laravel-zero.com/).
