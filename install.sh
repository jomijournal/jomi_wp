#!/bin/bash

echo updating refs...
sudo apt-get update
echo ...done!

#echo INSTALLING APTITUDE...
#sudo apt-get install aptitude
#echo ...done!

echo INSTALLING GIT...
sudo apt-get install git
echo ...done!

echo INSTALLING MYSQL...
if [ $(dpkg-query -W -f='${Status}' mysql-server 2>/dev/null | grep -c "ok mysql installed") -eq 0 ];
then
	sudo apt-get install mysql-server php5-mysql
	sudo mysql_install_db
	#flip executable bit
	#sudo chmod +x mysql_secure.sh
	#run secure install
	#sudo mysql_secure.sh
	sudo mysql_secure_installation
else
fi
echo ...done!

echo INSTALLING NGINX...
echo "deb http://ppa.launchpad.net/nginx/stable/ubuntu $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/nginx-stable.list
sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys C300EE8C
sudo apt-get update
sudo apt-get install nginx
echo ...done!

echo INSTALLING PHP...
sudo apt-get install php5 php5-fpm php5-cli php5-curl
echo ...done!

#clone jomi_wp

echo CLONING JOMI_WP...
cd /usr/share/nginx
rm -r html
git clone https://github.com/jomijournal/jomi_wp.git html
cd html
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

#clone jomi_theme
echo CLONING JOMI_THEME
cd /usr/share/nginx/html/wp-content/themes
git clone https://github.com/jomijournal/jomi_theme.git jomi
cd jomi
echo ...done!

#node stuff

echo INSTALLING NODEJS...
#grab nvm and run install script
curl https://raw.githubusercontent.com/creationix/nvm/v0.17.2/install.sh | bash
#automatically source upon login
echo "source ~/.nvm/nvm.sh" >> .bashrc
source ~/.bashrc

nvm install 0.10

echo ...done!


#echo INSTALLING NPM...
#sudo apt-get install npm
#need to do failure detection here
#echo ...done!

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

