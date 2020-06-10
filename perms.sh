#!/bin/sh

cd ..

echo "Chowning to www-data"

sudo chown -R arn:www-data rollespilsfabrikken_backend/
sudo chown -R arn:www-data rollespilsfabrikken_backend

echo "Setting file permissions"
sudo find rollespilsfabrikken_backend/ -type f -exec chmod 644 {} \;
sudo find rollespilsfabrikken_backend -type f -exec chmod 644 {} \;

echo "Setting directory permissions"
sudo find rollespilsfabrikken_backend/ -type d -exec chmod 755 {} \;
sudo find rollespilsfabrikken_backend -type d -exec chmod 755 {} \;

echo "Set read write on storage and cache"
cd rollespilsfabrikken_backend
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache

echo "Done"
