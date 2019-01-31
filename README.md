# site_works
PHP, MySQL, Javascript, and CSS framework

# Getting Started with my setup, adapt to your desires
    Ubuntu     18.04
    Nginx      1.14.0 (Ubuntu)
    PHP        7.2+
    uglifyjs AND uglifycss ( optional )
        sudo apt update
        sudo apt install nodejs npm
        npm install uglify-js -g
        npm install uglifycss -g
    PHP APCu (optional - but makes it a small amount faster)
        Example Install: 
        sudo apt-get update
        sudo apt-get install php7.2-apcu -y (Note 7.x based on yoru version)
        sudo service php7.2-fpm restart
        sudo systemctl restart nginx
        Additional Install Help:
        - https://guides.wp-bullet.com/install-apcu-object-cache-for-php7-for-wordpress-ubuntu-16-04/

# Quick and Dirty Nginx Setup Examples
    Your server is dedicated to your project:
        server {
	        listen 80;
	        listen [::]:80;
            root /var/www/html/site_works/public;
            index index.php;
            server_name MYDOMAIN.com www.MYDOMAIN.com;
            # Note: try_files will change our url, but we want to know the origional.
            set $holduri $uri;
            location / {
                # Note: You handle everything through index.php if not found, so 404 errors dont really exist
                try_files $uri $uri/ /index.php?$args;
            }
            location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
                include fastcgi_params;
                fastcgi_param DOCUMENT_URI $holduri;
                fastcgi_param SCRIPT_FILENAME $request_filename;
                fastcgi_param SCRIPT_NAME $fastcgi_script_name;
            }
        } # End Nginx Server Example

    Your server serves multipul projects:
        server {
            listen 80;
            listen [::]:80;
            root /var/www/html;
            index index.php index.html;
            server_name  MYDOMAIN.com www.MYDOMAIN.com;
            # Handle Your Other Normal Servers
            location / {
                try_files $uri $uri/ =404;
            }
            # Handle Your Other Normal PHP Requests
            location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
            }
            # This is the important part, you can try adding this to your current nginx setup.
            location ^~ /site_works/ {
                root /var/www/html/;
                index index.php;
                # Note: try_files will change our url, but we want to know the origional.
                set $holduri $uri;
                try_files $uri $uri/ /site_works/public/index.php?$args;
                location ~ \.php$ {
                    fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
                    include fastcgi_params;
                    fastcgi_param DOCUMENT_URI $holduri;
                    fastcgi_param SCRIPT_FILENAME $request_filename;
                    fastcgi_param SCRIPT_NAME $fastcgi_script_name;
                }
            }
        } # End Nginx Server Example

# Folder Permissions Example
        sudo chmod -R 775 conf
        sudo chgrp -R www-data conf

        sudo chmod -R 775 private
        sudo chgrp -R www-data private

        sudo chmod -R 775 public
        sudo chgrp -R www-data public

# site_works first server load:
    On first load, site_works will attempt to build your servers individual config file.
    You can find the file in site_works/conf/siteworks.YOUR_SERVER_WITHOUT_DOTS.pconf.php
    pconf.php files have been added to the root directories .gitignore file.

# Once your template config file has been written, open it and let's adjust it to your needs
    $this->dbc - Use this array to set up the connection information to your database(s).
        Important: The arrays 'default' key needs to be the one you want the site_works framework to use.
    $this->theme - If you want to use multipul css and js themes, you can select a default.
    $this->language - This is the default language, but you can manipulate $_SESSION['language'] to handle users choices.
    $this->debugMode - Enable Debugger, This allows us to send info to your debug_server app. Usage: $this->_tool->dmsg("debug_server output");
    $this->allowDebugColors - linux debug_server app can use colors on some systems, set to true if you want to try it.
    $this->showPHPErrors - sends php errors to your web browser, like normal php error enabled scripts.
    $this->showPHPErrors_debug - sends php error messages to the debug_server.
    $this->printSQL - Do you like seeing what your MySQL commands are doing? Enable this.
    $this->css_js_minify - minifys css and js. Typically, you would turn this on just before pushing to your live server so you can serve minified files.
    $this->APCuTimeoutMinutes - number of minutes for the apcu cache to refresh $this->mem and $this->admin db records.
    $this->admin_level_options - Enumerated array of user permission levels. $_SESSION['admin_level'] to control user levels.
    $this->tail_array - Want to see tail of a file in the debugger? Add the file path and number of lines to show.
    $this->default_module - This determins what dev/modual will be used if none are found in the URL.
    $this->modualLocks - Disable access for unpriviledged users to visit an entire modual.
    $this->controllerLocks - Disable access for unpriviledged users to visit a speific moduals controller.
    $this->routes - Yes, you can let someone type something odd in your url, then redirect it to a good path.
    $this->debug_server - The IP of the server running your debug_server app.
    $this->debug_server_port - the default port I use is 9200, whatever you set make sure you port forward.
    $this->cPaths - tell the system some basics about your server and asset server paths.




    // You could set things like meta tags or load jquery here or in yoru personal server
    // Example: $this->out['meta'][] = '<meta property="og:title" content="OG EXAMPLE META YOUR TITLE" />';
    // Example: $this->out['js'][] = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';


