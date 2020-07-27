#!/bin/sh

if [ "$1" != "" ]; then
	echo "Directory used: $1"
else
	echo "You need to set the directory"
	exit
fi

if [ "$2" != "" ]; then
	echo "User: $2"
else
	echo "You need to provide a user"
	exit
fi

echo "Chowning to www-data"

sudo chown -R $2:www-data $1
sudo chown -R $2:www-data $1

echo "Setting file permissions"
sudo find $1/ -type f -exec chmod 644 {} \;
sudo find $1 -type f -exec chmod 644 {} \;

echo "Setting directory permissions"
sudo find $1/ -type d -exec chmod 755 {} \;
sudo find $1 -type d -exec chmod 755 {} \;

echo "Set read write on storage and cache"
cd $1
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache

echo "Done"
