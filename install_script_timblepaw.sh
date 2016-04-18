#! /bin/bash -x

### DO NOT USE - THIS IS NOT TESTED

###Debian or Ubuntu only!

#https://github.com/jomijournal/jomi_wp/raw/master/install.sh

#is original. Here is my walkthrough.
#Note: You must be logged into git and have an ssh key uploaded to the site in order to git clone through ssh. This should be done before running the script on new machines. Be sure to verify that your git is working by running ssh -T git@github.com before running this script.

####Basic Setup
sudo apt-get update 
sudo apt-get -y dist-upgrade
sudo apt-get -y install git
sudo apt-get -y install nginx
sudo apt-get -y install php5-fpm php5-cli php5-curl
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
sudo apt-get -y install mysql-server php5-mysql
sudo apt-get -y install curl
sudo curl -sL https://deb.nodesource.com/setup | sudo bash -
sudo apt-get install -y nodejs
sudo apt-get install -y build-essential
#npm should already be installed by the nodejs package and if you try to install separately through apt-get it will give an error that can be ignored.

####Repository Downloading

cd /usr/share/nginx/html/
sudo git clone https://github.com/jomijournal/jomi_wp.git
cd jomi_wp/

####Config Files
#note: the config files may still need some fine tuning after this.
#e.g. you have to set root to be jomi_wp folder in nginx.conf

#Nginx
sudo cp ./coreconf/nginx/jomi /etc/nginx/sites-available/jomi
sudo cp ./coreconf/nginx/default /etc/nginx/sites-available/default
sudo ln -s /etc/nginx/sites-available/jomi /etc/nginx/sites-enabled/jomi
sudo rm /etc/nginx/sites-enabled/default
sudo ln -s /etc/nginx/sites-availabel/default /etc/nginx/sites-enabled/default

#Mysql
#not necessary to copy config for dev installations

#PHP
#Differences are small. use this to see differences:
#diff /usr/share/nginx/html/jomi_wp/php5/fpm/php.ini /etc/php5/fpm/php.ini
sudo cp ./coreconf/php5/fpm/php.ini /etc/php5/fpm/php.ini

#Wordpress
sudo cp ./coreconf/wordpress/wp-config.php ./wp-config.php
sudo sed -i "s/'pass'/'root'/" ./wp-config.php

#Restart
sudo service nginx restart
sudo service mysql restart
sudo service php5-fpm restart

echo time to go manual for the rest of the instructions...



#At this point, you need to git the submodule manually as a submodule
#Then finish with npm and bower and bootstrap before ending with imported the sql database.

#Because these dependancies change from time to time, it cant be expected to make a reliable bash script for all but the most stable programs.

##### SUBMODULE

# Nikita: may need to adjust permissions on .git directory for this to work
# do not use sudo - that uses a different SSH key
 
# 184  git submodule sync
# 185  git submodule init
# 186  git submodule --list
# 187  git config --list |egrep ^submodule
# 188  git submodule update




#"Don’t forget to checkout the branch inside the submodule (remember? submodules have a detached HEAD):"
#$ cd lib/supercoolib (example)(in jomi's case this would be the theme/jomi folder under wp-content)
# so make sure you are in the jomi theme folder for below steps
#$ git checkout master
# 191  git pull -u origin master

# cd wp-content/themes/jomi



####NPM
 #193  sudo npm install -g bower
 #194  sudo npm install -g grunt-cli
 #196  sudo npm install
 #198  bower install
 #199  sudo npm install
 #sudo grunt build
 
 # curl -sS https://getcomposer.org/installer | php # QUESTION: where should this be downloaded?
 # 200  php composer.phar install
 # 203  php composer.phar update

###Permissions  todo

#note: need to change settings in nginx.conf to specify jomi sites-available
# need to change settings in jomi in sites available to specify to listen to port 80 and server_name is localhost

##Mysql
#mysql -u root -p  < jomi.sql


### NOTE: IMPORTANT:
# In order to get mail to work correctly, you must manually set the template for mandril
# in the wordpress. As wordpress pulls from mandrill, this can't be automated.
# Login to the admin account under Jomi, navigate to the mandrill settings page, 
# And set the template to the currently used one (at the moment is newbert).
# Can cross reference the current jomi site to see what template is being used.
# 




#todo:
#name changes so its difficult, as well we have to specify the path where the sql file is, which is also difficult unless you download it somewhere and rename it beforehand...

#need to change mysql password after the installation so it isn't under root or called root.

# need to set proper permissions on directories.













### Possible Errors
# q.The home page works, but other pages are 404!
# a.you have to set the root to be the folder where jomi_wp is. or just move all jomi_wp into the root (I prefer the former as it doesn't mess with directory settings).


# q.The homepage is white, and all messed up,  but some text appears!
# a. the installation of npm / bower / submodules didn't go through right. make sure you install the submodule correctly as a submodule, and make sure you install npm, grunt, bower, and they all go through without errors.



# q. Database connection error!
# a. make sure the sql database is imported and the wp-config points to a working mysql password. debug by running as the user for mysql in command line that wpress will use manually. If necessary, make a new user and reset password.



#the above script will work. it has been tested although some steps must be manual. Maybe if I find more motivation I will write it correctly.
