# BW WP plugin update tool 

Creates a CLI tool named no-cry that makes updating plugins and WP Core (hopefully) not want to make you cry.

Creates a new branch, runs updates, individually commits them and gives you a nice output of what it did.

## Build Requrirements
- PHP ^7.3|^8.0

## Installing the tool
1. the built application is included in this repo in the `/builds/` folder
1. from the root of the project directory run `mv builds/no-cry /usr/local/bin` to move the build to your bin folder (you might need to add `sudo` to that command.)

## Building the tool
1. composer install
2. run `php no-cry app:build` to build the tool
3. run `mv builds/no-cry /usr/local/bin` to move the build to your bin folder (you might need to add `sudo` to that command.)

## Using the tool

### Before you start
1. make sure the local `master` branch of the site you want to update is up to date with the remote and the working directory is clean
1. if you're using Lando start it up
1. if you're using a different local server (e.g. Valet) make sure you've got the [WP-CLI](https://wp-cli.org/) installed
1. make sure that all paid plugins are licenced locally in order to recieve updates

### Basic usage
- Run `no-cry please` and follow the prompts

### Options
#### Run on Lando
- Add the lando flag: `-l` or `--lando` to run the the commands on Lando (`no-cry please -l`)

#### Specify ticket # in the command
- Optionally add the plugin update ticket number as an argument like so `no-cry please 545`. The tool will create a new branch in the following format for you: `{$ticket}-plugin-updates-{$now->format('Y-m-d')}`

#### Specify branch to run on
- Specify a branch (new or existing) to run the updates on like `no-cry please -b branch-name` or `no-cry please --branch branch-name`

### Config file
Add a .no-cry.json file in the root of your project to save you from entering arguments each time you run the tool. 

```
{
  "lando": true,
  "ticket": "999"
}
```

### Prompts
1. if you didn't enter the plugin update ticket number as an argument you'll be prompted to -- it'll be appended to the branch name like: `{$ticket}-plugin-updates-{$now->format('Y-m-d')}`
1. the tool will ask if you'd like to update WP Core
1. the tool will ask if you'd like to add the output to your clipboard. This clipboard content will include GitLab quick actions

### Output
1. the tool will let you know what it's doing. 
1. after it's done the tool will print out a list of what was updated

### Clean up
1. pay attention to any errors in the plugin updates. You may need up update some mannually. 
1. after all plugins are up to date, and each update committed:
1. push your changes, merge and deploy to staging for testing


## To do
1. ~~add option to specify ticket number in branch name~~
1. ~~include update wp core~~
1. ~~optionally specify/select branch to commit to?~~
1. ~~Code cleanup: Spin out some of the main Command file into Concerns~~
1. Figure out a nice solution to update paid plugins (Integrate SatisPress??) --> new Composer based workflow
1. Figure out a way to update Must Use plugins (Composer)

## Laravel Zero

This project uses Laravel Zero. For full documentation, visit [laravel-zero.com](https://laravel-zero.com/).
