JoMI UI Core
=======

JoMI Core User Interface

--------
Installation
------------

Requires
+ PHP (5.5.x)
+ Node.JS (>0.10.24) <http://nodejs.org/>

Optional
+ nvm <https://github.com/creationix/nvm>

PHPBrew

Seriously, consider using PHPBrew:
+ phpbrew <https://github.com/phpbrew/phpbrew>

OS X prereqs

    $ brew install automake autoconf curl pcre re2c mhash libtool icu4c gettext jpeg libxml2 mcrypt gmp libevent
    $ brew link icu4c # may give you a warning, ignore
    $ brew tap josegonzalez/php
    $ brew tap homebrew/dupes
    $ brew install php55 # you may want to run `brew options php55` to see some compile time options, but not necessary for this gig

Ubuntu/Debian prereqs


    $ sudo apt-get install php5


Installation (OS X, Ubuntu/Debian)

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
    $ cp lib/scripts-template.php lib/scripts.php # if this fails, check if
                                                  # lib/scripts.php exists.
                                                  # If it does, ignore the
                                                  # error.  Otherwise, contact
                                                  # the authors
    $ npm install
    $ grunt build
    $ popd
    $ php -S localhost:8080


Credits
-------

Copyright (c) 2014 Journal of Medical Insight.
All rights reserved.

NOT FOR REDISTRIBUTION
