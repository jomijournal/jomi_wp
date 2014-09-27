JoMI WP
=======
-------------------------------
JoMI WordPress Installation
-------------------------------

The site installation, in a nutshell, involves 4 basic steps:

 * get and configure server stack (lamp, wemp, etc)
 * get our repos (jomi_wp, jomi_theme)
 * get third party libraries
 * get the jomi database

--------
Let's Get Started
------------

## WINDOWS ##

Requires:

 * Git SCM download from <http://git-scm.com/> and follow install instructions
     * #strongly# recommend you use git bash, the shell that comes with the git install. this README will be geared towards            linux-like shell commands
 * WT-NMP (windows + (e)nginx + mysql + php5)

     * download from <http://sourceforge.net/projects/wtnmp/> and follow install instructions
     * don't create a "project" for jomi. somehow putting the site in its own directory will break (need to fix, but low priority). we will just dump jomi's files into the WWW folder later.
     * copy the contents coreconf/wt-nmp (from this repo) into c:\wt-nmp\conf. 
     * use the wt-nmp tool to kill/start all 3 processes. if any of them fail, check the error logs and diagnose.

~~ WAMP (windows + apache + mysql + php5) ~~ try using WT-NMP instead. it will make things easier in the long run:

* Download WAMP installer <http://www.wampserver.com/en/> **32 BITS ONLY**
      * follow installation instructions
      * test by going to <http://localhost/> in your browser. should show a landing page.
* apache server configuration
      * go to /wamp/bin/apache/apache2.4.9/conf/
      * replace httpd.conf with https://files.slack.com/files-pri/T02CD21SV-F02CJMYDH/httpd.conf
      * this enables permalinks with WP
* php configuration
      * add C:\wamp\bin\php\php5.5.12 (or similar) to your system's path


Node.JS:

* installer <http://nodejs.org/download/>
* nodist <https://github.com/marcelklehr/nodist>
* to install:
```
#!bash
$ cd [wherever_you_put_your_git_repos]
$ git clone git@github.com:marcelklehr/nodist.git
$ export PATH=$PATH:[absolute_path_to_nodist/bin]   # for example, export PATH=$PATH:"C:\Users\user\git\nodist\bin"
                                                    # or, add it through this pc->properties->advanced settings->environment variables
                                                    # when done, source .bashrc or restart bash
$ nodist 0.10 # install latest 0.10 version of node
$ nodist ls # check for success
```


Optional:

* [sublime text](http://www.sublimetext.com/3) (text editor)
      * [wbond package manager](https://sublime.wbond.net/installation)
      * DocBlockr
      * LESS (LESS syntax highlighting)
      * Emmet (code completion)


    

## OS X prereqs ##

PHPBrew

Seriously, consider using PHPBrew:

* phpbrew <https://github.com/phpbrew/phpbrew>

 * [nvm](https://github.com/creationix/nvm) (node version manager) - this lets us get node
```
#!bash
$ brew install automake autoconf curl pcre re2c mhash libtool icu4c gettext jpeg libxml2 mcrypt gmp libevent
$ brew link icu4c # may give you a warning, ignore
$ brew tap josegonzalez/php
$ brew tap homebrew/dupes
$ brew install php55 # you may want to run `brew options php55` to see some compile time options, but not necessary for this gig
```

## Ubuntu/Debian prereqs ##

Git:
```
#!bash
$ sudo apt-get install git
```

Node:
```
#!bash
$ sudo apt-get install nodejs
$ sudo apt-get install npm
```

PHP:
```
#!bash
$ sudo apt-get install php5
```
## Installation (Windows) ##
```
#!bash
$ cd C:\WT-NMP/ # OR "cd C:\wamp/" if using wamp
$ git clone git@bitbucket.org:jomi_ci/jomi-wp.git
$ rm -r www # remove old www directory
$ mv jomi-wp www # move jomi-wp into new www directory
$ git checkout master # switch onto master branch if not already on it
$ git submodule init
$ git submodule update # initialize jomi-theme subrepository/submodule
$ cd wp-content/themes/jomi/
$ git checkout master
$ git pull --rebase # submodule update points the HEAD to a commit.
                    # do this so HEAD points to origin/master for the latest and greatest
$ npm install -g grunt-cli  # install npm utilities
                            # if this fails, verify your installation of node 
                            # (make sure you have version>=0.10.24)
$ npm install -g bower 
$ npm install   # if this fails, verify node installation.
                # also, the contextify plugin needs Visual Studio Redist > 2012 
                # and Python >= 2.7 in order to build. this is another
                # common reason why npm install fails
$ php composer.phar install
$ php composer.phar update
$ php composer.phar require geoip2/geoip2:~0.7.0
$ bower install # front-end dependencies
$ grunt build
```
## Installation (OS X, Ubuntu/Debian) ##
```
#!bash
$ pushd <working path>
$ git clone git@bitbucket.org:jomi_ci/jomi-wp.git
$ git checkout master
$ git submodule init
$ git submodule update # if this fails, check if 
                       # <working path>/jomi-wp/wp-content/themes/jomi
                       # is empty
$ pushd wp-content/themes/jomi
$ git pull -u origin master
$ npm install -g bower
$ npm install -g grunt-cli
$ npm install
$ bower install # if this fails, ignore it
$ npm install
$ php composer.phar install
$ php composer.phar update
$ php composer.phar require geoip2/geoip2:~0.7.0
$ grunt build
$ popd
$ php -S localhost:8080
```

Credits
-------

Copyright (c) 2014 Journal of Medical Insight.
All rights reserved.

NOT FOR REDISTRIBUTION
