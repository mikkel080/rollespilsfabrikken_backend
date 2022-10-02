# Rollespilsfabrikken backend

## Frontend deployment
First build the frontend locally, using node version 16: https://github.com/avborup/rollespilsfabrikken-forum-calendar-frontend

Then logon to the Simply.com dashboard and navigate to the forum.rollespilsfabrikken/public folder, here you shall insert the contents of the dist folder of the build.

This can be done with FTP and with a zip file.

After the above copy the contents of the built index.html file into welcome.blade.php in the forum.rollespilsfabrikken.dk/resources/views folder.

Go to SSH, navigate to forum.rollespilsfabrikken.dk folder and run `php artisan view:cache`

Deployed!
