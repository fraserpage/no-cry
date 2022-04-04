# BW WP plugin update tool 

Creates a CLI tool named no-cry that makes updating plugins (hopefully) not want to make you cry.

## Build Requrirements
- PHP 7.4

## Building the tool
1. composer install
2. run `php no-cry app:build` to build the tool
3. run `mv builds/no-cry /usr/local/bin` to move the build to your bin folder (you might need to add `sudo` to that command.)

## Using the tool
1. make sure the local `master` branch of the site you want to update is up to date with the remote and the working directory is clean
1. startup lando
1. make sure that all paid plugins are licenced locally in order to recieve updates
1. run `no-cry please` (optionally add the plugin update ticket number as an argument like so `no-cry please 545` or wait for the prompt described below)
1. if you didn't enter the plugin update ticket number as an argument do so when prompted -- it'll be appended to the branch name
1. you'll get an output of plugins that were updated, skipped or had errors
1. fix any errors
1. at this point all plugins should be up to date, and each update committed
1. push your changes, merge and deploy to staging for testing

## To do
1. ~~add option to specify ticket number in branch name~~
1. ~~include update wp core~~
1. ~~optionally specify/select branch to commit to?~~
1. Code cleanup: Spin out some of the main Command file into Concerns
1. Figure out a nice solution to update paid plugins (Integrate SatisPress??)
1. Figure out a way to update Must Use plugins

## Laravel Zero

This project uses Laravel Zero. For full documentation, visit [laravel-zero.com](https://laravel-zero.com/).
