Installation
============

I'll show my detailed installation process on OS X, any linux distribution should be similar.

Table of Contents
--------------------

 - [Requirements](#requirements)
 - [Install Nginx](#install-nginx)
 - [Install PHP with intl extension](#install-php-with-intl-extension)
 - [Install MySQL](#install-mysql)
 - [Install Composer](#install-composer)
 - [Install NPM](#install-npm)
 - [Install Grunt And Less](#install-grunt-and-less)
 - [Install GNU Commands (OS X only)](#install-gnu-commands-os-x-only)
 - [Nginx Configuration](#nginx-configuration)
 - [PHP Configuration](#php-configuration)
 - [MySQL Database](#mysql-database)
 - [Download dependencies](#download-dependencies)
 - [Build Bootstrap](#build-bootstrap)
 - [Configurations](#configurations)
 - [Import data from WCA](#import-data-from-wca)

## Requirements

 - **PHP:** >=5.4.0
 - **MySQL:** <5.7(there is a weird problem with the sql running with 5.7)
 - **NPM**
 - **Grunt**
 - **Less**
 - **Composer**
 - **Apache/Nginx/Any webserver supports PHP**
 - **Windows/Unix/Linux/OS X/Any System supports things above-mentioned**
 - **GNU grep, GNU date and wget** for `OS X` if you have to run the update script

## Steps

First I installed a bunch of things...

### Install Nginx

I use [Homebrew][] to install nginx, it's pretty easy

```bash
brew install nginx
```

### Install PHP with intl extension

Again, with `homebrew`. Note that you need the `intl` extension for i18n of the project.

```bash
brew tap homebrew/php
brew install php70 php70-intl
```

### Install MySQL

As you might have guessed, with `homebrew` again. I installed version 5.6 instead of 5.7 because there's a problem calculating ranks with my sql statements and I spent a whole night trying to resolve this issue but failed.

```bash
brew tap homebrew/versions
brew install mysql56
```

### Install Composer

```bash
brew install composer
```

### Install NPM

```bash
brew install npm
```

### Install Grunt and Less

Finally there is something not installed with `homebrew`...I need `grunt` because I want to build the bootstrap framework from source so I can build a minimum sized framework as I need. `less` is for compiling style sheets for the project.

```bash
npm install -g grunt-cli less less-plugin-autoprefix less-plugin-clean-css
```

### Install GNU commands (OS X only)

The `grep` and `date` commands shipped with OS X is quite different with the GNU version so I have to install GNU version for compatibility. Besides, OS X doesn't have a `wget` command.

```bash
brew install coreutils grep wget
```

Then we come to the configurations and preparations. I'll skip the details of configuring `nginx` and `php` to make them work together. There are lots of tutorials on the internet...

### Nginx Configuration

Make sure you have something like this with your webserver, i.e. you have to redirect most requests to the application. **The document root should be the `web` directory in the project.**

```nginx
...
root /path/to/the/project/web;
...
location / {
    # Redirect everything that isn't a real file to index.php
    try_files $uri $uri/ /index.php?$args;
}
...
```

### PHP Configuration

With the php-fpm configuration, make sure the following is configured appropriately to make less compiler work(might be different):

```ini
env[PATH] = /usr/local/bin:/usr/bin:/bin
```

### MySQL Database

To make the website as stable as possible, I'm using two databases to ensure the website works well during the data update process(it usually takes around 10 minutes). One is called `wca_0` and the other is called `wca_1`. Naming them like this make it easier to alter between them automatically, with the following tricks:

```bash
dbConfig="$localDir/../../../config/common/wcaDb"
...
dbNum=`expr \( \`cat $dbConfig\` + 1 \) % 2`
dbName="wca_$dbNum"
...
echo -n $dbNum > $dbConfig
```

And with the application configure: 

```php
return [
...
    'dsn' => 'mysql:host=localhost;dbname=wca_' . trim(@file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'wcaDb')),
...
];
```

An update performs as follows:

 1. The script updates the database not in use to the latest version, then
 2. the script alters the configure file to make the application switch to the new version, then clear caches.

After each update, there's always a database with the older data, but it's not in use.

### Download dependencies

To do this, simply run the following command in the project directory:

```bash
composer install
```

### Build Bootstrap

Because I want to make it as easy as possible to custom the `bootstrap` library, I imported the source files and build scripts to the project, and simplified it to have only dist and watch tasks.

To build it, first go to the `static/bootstrap` directory and run:

```bash
npm install
```

After that, run:

```bash
grunt
```

It should compile the source files into `dist` directory.

### Configurations

To protect some private credentials, it is highly recommended to have a 'local copy' of some config files. In this project, there are three.

 - First one is `config/web/request.php`, copy it to `request.local.php` and add some random string to `cookieValidationKey`;
 - Another one is `config/common/db.php`, copy it to `db.local.php`, it has the credentials for the database, so configure it according to your own situation;
 - The final one is `commands/shell/wca_db/my.cnf`, copy it to `my.local.cnf`, it is used for the update script, similar to `db.php`.

Finally you have to run the following command in the `config/common` directory to create the `wcaDb` file: 

```bash
echo 0 > wcaDb
```

### Import data from WCA

The easiest way is to run the script I provide in the project, namely `commands/shell/wca_db/upWCARes.sh`:

```bash
bash upWCARes.sh
```

Or make it executable and then just run it:

```bash
chmod +x upWCARes.sh
./upWCARes.sh
```

~~**Important: Don't run `sh upWCARes.sh` under OS X, it would put the `-n` into the file. Run `bash upWCARes.sh` instead** (Or you can remove the `-n` because the php script does trim it, which was added afterwards)~~

Now it should work properly. There might be some problems I haven't mentioned above, but just try to resolve it by your self :)


[Homebrew]: http://brew.sh