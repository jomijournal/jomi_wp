#!/bin/bash

cd ~

echo updating refs...
#sudo apt-get update
echo ...done!

echo INSTALLING GIT...
sudo apt-get install git
echo ...done!

echo INSTALLING MYSQL...
if [ $(dpkg-query -W -f='${Status}' mysql-server 2>/dev/null | grep -c "ok installed") -eq 0 ];
then
	sudo apt-get install mysql-server php5-mysql
	sudo mysql_install_db
	sudo mysql_secure_installation
else
	echo mysql already installed!
fi
echo ...done!

echo INSTALLING NGINX...
if [ $(dpkg-query -W -f='${Status}' nginx 2>/dev/null | grep -c "ok installed") -eq 0 ];
then
	echo "deb http://ppa.launchpad.net/nginx/stable/ubuntu $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/nginx-stable.list
	sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys C300EE8C
	sudo apt-get update
	sudo apt-get install nginx
else
	echo nginx already installed!
fi
echo ...done!

echo INSTALLING PHP...
sudo apt-get install php5 php5-fpm php5-cli php5-curl php5-mysql
echo ...done!

#clone jomi_wp

echo CLONING JOMI_WP...
#sudo rm -f -r jomi_wp
#git clone https://github.com/jomijournal/jomi_wp.git
cd jomi_wp
echo ...done!

#load coreconf

#load nginx
echo LOADING NGINX CONFIG...
sudo cp coreconf/nginx/jomi /etc/nginx/sites-available/jomi
sudo cp coreconf/nginx/default /etc/nginx/sites-available/default

sudo ln -s /etc/nginx/sites-available/jomi /etc/nginx/sites-enabled/jomi
sudo ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
echo ...done!

#load mysql
echo LOADING MYSQL CONFIG...
sudo cp coreconf/mysql/my.cnf /etc/mysql/my.cnf
echo ...done!

#load php
echo LOADING PHP CONFIG...
sudo cp coreconf/php5/fpm/php.ini /etc/php5/fpm/php.ini
echo ...done!

#load wp-config.php
echo LOADING WORDPRESS CONFIG...
#echo Please enter your MySQL username (probably root)
#read UNAME
#echo Please enter your MySQL password
#read PASS
sudo cp coreconf/wordpress/wp-config.php wp-config.php
echo users and passwords need to be entered manually fool
echo ...done!

#restart services
echo STARTING SERVICES...
sudo service nginx restart
sudo service mysql restart
sudo service php5-fpm restart
echo ...done!

#clone jomi_theme
echo CLONING JOMI_THEME
#sudo rm -r -f jomi
#git clone https://github.com/jomijournal/jomi_theme.git jomi
cd jomi
echo ...done!

#node stuff

echo INSTALLING NODEJS...
curl https://raw.githubusercontent.com/creationix/nvm/v0.17.2/install.sh | bash
source ~/.nvm/nvm.sh
nvm install 0.10
nvm use 0.10
echo ...done!


#node dependencies

npm install -g --verbose bower
npm install -g --verbose grunt-cli
npm install --verbose
bower install

#composer

curl -sS https://getcomposer.org/installer | php
php composer.phar install
php composer.phar update

#grunt build

grunt build

#move to nginx folder
sudo rm -r -f /usr/share/nginx/html
cd ~
sudo mv jomi_wp /usr/share/nginx/html

export JWP=/usr/share/nginx/html
export JTH=/usr/share/nginx/html/wp-content/themes/jomi
source ~/.bashrc


#NEED TO CREATE DATABASE AND IMPORT SQL FILE FROM JOMI.COM