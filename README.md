# BW WP plugin update tool 

Creates a CLI tool named bw-lando (though we might want to pick a nicer name!) that makes updating plugins kind of ok.

## Build Requrirements
- PHP 7.4

## Building the tool
1. composer install
2. run `php bw-lando app:build` to build the tool
3. run ` mv builds/bw-lando /usr/local/bin` to move the build to your bin folder

## Using the tool
- run `bw-lando wp:update-plugins` and watch it do its magic

## To do
1. add option to specify ticket number in branch name
1. figure out a nice solution to update paid plugins (Integrate SatisPress??)
1. Figure out a way to update Must Use plugins

## Laravel Zero

This project uses Laravel Zero. For full documentation, visit [laravel-zero.com](https://laravel-zero.com/).
