#!/bin/bash -x


$mysqluser;
$mysqlpass;
$sshuser;
$sshpass;
$sship;

echo 'enter in mysql user name (should be root, or have delete database permissions';
read mysqluser;

echo 'mysql user is:';
echo $mysqluser;

#echo 'eunter in mysql user password';
#read mysqlpass;

echo 'enter in ssh user name';
read sshuser;

echo 'ssh user is:';
echo $sshuser;

#echo 'enter in ssh pass';
#read sshpass;

echo 'enter in ssh ip';
read sship;

echo 'ssh ip is:';
echo $sship;

echo 'Connecting to ssh ip and will copy latest database.';

ssh $sshuser@$sship 'cd backups/ && new1=$(ls -t | head --lines=1) && cp $new1 latest_db.sql.gz && exit';


echo 'latest database gzip copied. will pull over to dev now';
scp "$sshuser"'@'"$sship"':~/backups/latest_db.sql.gz' ~/Desktop/latest_db.sql.gz;


mysql -u $mysqluser -p jomi -e 'drop database jomi';

echo 'old database removed';

cd ~/Desktop;

echo 'extracting db';

gunzip -d latest_db.sql.gz;

mysql -u $mysqluser -p < latest_db.sql;

echo 'new database uploaded';

echo 'done';
