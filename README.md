<!--- [![Build Status](https://travis-ci.org/NEU-Libraries/drs-toolkit-wp-plugin.svg?branch=develop)](https://travis-ci.org/NEU-Libraries/drs-toolkit-wp-plugin)

[![Coverage Status](https://coveralls.io/repos/github/NEU-Libraries/drs-toolkit-wp-plugin/badge.svg?branch=master)](https://coveralls.io/github/NEU-Libraries/drs-toolkit-wp-plugin?branch=master)
--->

Plugin that provides functionality and templates for bringing DRS data into WP for the CERES: Exhibit Toolkit.

Should be cloned into wp-content/plugins with
```
  git clone https://github.com/NEU-Libraries/drs-toolkit-wp-plugin.git drs-tk
```
Relies on Javascript being enabled and AJAX - Should work in IE10 and up, Chrome, Firefox, and Safari

[Corresponding theme](https://github.com/NEU-Libraries/drs-toolkit-wp-theme)

### For developers
If you are _not_ using the CERES library, rename `ceres_adapters.php` to `ceres_adapters.php.dist`, unless that is already in the distribution.

### New in version v1.2

First, it has a version in git. This will allow for better tracking and debugging. It also cleans up many code and logic issues, and moves toward a more generalized error-reporting mechanism. See [Release Notes](https://github.com/NEU-Libraries/drs-toolkit-wp-plugin/releases) for details.


### Setup

1. Ask Karl for clean install of WP on correct server (will need to provide a directory name and domain info)

1. On the server, any files ending in `.dist` need to be copied to their non-dist equivalents, and settings in those files added. Contact @patrickmj for the private data there for now (new in v1.2)

2. Log in to new Wordpress site as admin user

3. Install Quest theme (go to /wp-admin/theme-install.php and search for quest then click install)

4. SSH into correct directory on server and git clone this repo into wp-content/plugins

  ```
    git clone https://github.com/NEU-Libraries/drs-toolkit-wp-plugin.git drs-tk
  ```

5. Also clone the theme into wp-content/themes

  ```
    git clone https://github.com/NEU-Libraries/drs-toolkit-wp-theme.git quest-child
  ```

6. Copy a valid `config.php` file with the correct credentials into the root folder

7. Optionally, register your site on google analytics and add your code to a git-ignored file called analytics.php

  ```
    cd /wp-content/themes/quest-child
    vi analytics.php
  ```

  Example file contents:

  ```
    <?php
      echo "<script>[YOUR GOOGLE ANALYTICS CODE]
      function add_google_tracking(){
        jQuery('.button').on('click', function() {
          ga('send', 'event', jQuery(this).data('label'), 'click', jQuery(this).data('pid'));
          console.log('send', 'event', jQuery(this).data('label'), 'click', jQuery(this).data('pid'));
        });
      }
      </script>
      ";
  ```

8. If you would like to override some of the functionality and styles or this child theme you may create a sub-directory named `overrides`.  This directory will be ignored by git and your changes won't be overwritten by future git pulls from the main repo.  Additionally, you can initialize this repository as a git-submodule and track your own changes in your own repo.

  ```
    cd wp-content/themes/quest-child
    mkdir overrides
    touch overrides/style.css
    echo "<?php //silence is golden" > overrides/functions.php
  ```

9. Delete extra themes to avoid user confusion

  ```
    cd wp-content/themes
    rm -rf twentyfifteen
    rm -rf twentysixteen
    rm -rf twentyseventeen
  ```

10. Go to /wp-admin/plugins.php in your browser. Install dependent plugins: Relevanssi, Page Builder by SiteOrigin, Black Studio TinyMCE Widget, and Widget Context and activate them.

11. Go to /wp-admin/plugins.php in your browser. Activate CERES: Exhibit Toolkit Plugin

12. Go to Settings > Reading and set Front Page Displays to a static page then choose a static page.

13. Go to Settings > Discussion and uncheck the box that says 'Allow people to post comments on new articles' to disable comments by default

14. Go to Settings > CERES: Exhibit Toolkit and enter collection URL and add modify any other settings then click Update.

15. Go to Appearance > Themes and activate CERES: Exhibit Toolkit (Quest Child Theme)

16. Go to Appearance > Customize > Layout > Search Results. Change sidebar to none.

17. Go to Appearance > Customize > Colors > Global. Change Accent Color and  Accent Shade Color to #c00

18. Click the '<' arrow then Header. Change Secondary Header > Background Color to #494949, Text Color and Social Icon Color to #AFAFAF, Top Border Color to #3C3C3C and Social Icon Hover Color and Social Icon Hover background Color to #EFEFEF

19. Click the '<' arrow then Main Menu. Change Menu Items > Text Hover/Focus Color and SubMenu Items Hover/Focus Text Color to #c00.

20. Click the '<' arrow then Footer. Change Social Icon Hover Color and Social Icon Hover background color to #c00. Change Secondary Footer Background Color to #3C3C3C.

21. Go to Pages > Delete the 'Sample Page'. Go to Posts > Delete the 'Hello World'.

22. Add site to deploy script (deploy.sh) - see [secret gist](https://gist.github.com/elizoller/2412436c2148d694efc1)

23. Add users based on requests from project team

---
If you would like breadcrumbs on single pages/posts (not drs items) that reflect hierarchy, simply drag and drop the pages in the wp-admin pages screen to nest.

---
Moving a site to Production

1. Install additional plugins - WP Super Cache, Google XML Sitemaps, Warm Cache and enable all three

2. Go to Settings > WP Super Cache and select Caching on then Update Status

3. Go to Settings > XML-Sitemap and copy the URL for the sitemap

4. Go to Plugins > Warm cache and paste in the URL from above. This will generate a URL something like `http://domain.library.northeastern.edu/?warm_cache=jfido908234` Copy this URL and place it in the warm_cache.sh script located in /mnt/wordpressdata/warm_cache.sh on the production server. This is triggered on a cron job to warm the cache on production sites. (as root user - `00 1 * * * /mnt/wordpressdata/warm_cache.sh`)

---
If you have trouble with the loading DRS content after initial install. Here are a few things to check:

* Check to make sure you have mod_rewrite enable (enable (`a2enmod rewrite`) and apache2/httpd restart(`sudo service apache2 restart`)). If you aren't sure if mod_rewrite is enabled, check `phpinfo()`

* Make sure the apache conf settings are correct. For your WP directory, you must have `Options FollowSymlinks` and `AllowOverride FileInfo` or `AllowOverride All`

* Make sure php5-curl library is installed. `sudo apt-get install php5-curl` and `sudo service apache2 restart`

* Check to make sure permalink settings are on and correct (ie. no index.php inside the path). If saving/resaving permalink settings does not work, make sure your .htaccess file in the root directory of your WP install is present and writable. For more on permalinks see [https://codex.wordpress.org/Using_Permalinks](https://codex.wordpress.org/Using_Permalinks).

* Check File permissions. In general WP directories should be 775 and files should be 664, with the exception of wp-config.php which should be 660. See [https://www.smashingmagazine.com/2014/05/proper-wordpress-filesystem-permissions-ownerships/](https://www.smashingmagazine.com/2014/05/proper-wordpress-filesystem-permissions-ownerships/) for more info.

---
Optional Steps for Updating

1. Install [Composer](https://getcomposer.org) ([Install Directions](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx))

2. Add a `composer.json` file to the root directory of your Wordpress install with the following content:

  ```
      {
    	"repositories":[
    		{
    			"type":"vcs",
    			"url":"https://github.com/NEU-Libraries/drs-toolkit-wp-plugin"
    		}
    	],
    	"require": {
    		"drs-tk/drs-tk":"dev-master"
    	},
    	"extra" : {
            	"installer-paths" : {
                		"wp-content/plugins/{$name}/": ["type:wordpress-plugin"]
            	}
        	}
    }
  ```
3. Update the plugin by running `php composer.phar update` in the root directory of your wordpress install.

---
To Setup Mirador

1. Make a IIIF compliant manifest.json file and place it under the root of the install like http://example.northeastern.edu/manifest.json

2. Copy the file in plugins/drs-tk/assets/mirador/sample_manifest.js to plugins/drs-tk/assets/mirador/mirador_manifest.js

   ```
      cd wp-content/plugins/drs-tk
      cp assets/mirador/sample_manifest.js assets/mirador/mirador_manifest.js
   ```
3. Modify assets/mirador/mirador_manifest.js to point the manifest.json file you created
   change [wordpress_url] to the url for the wordpress install like http://example.northeastern.edu
4. Go to /wp-admin in your browser and update the CERES settings to enable Mirador, set a page title, and custom URL

---
For Performing Updates to Plugins en mass

1. Make sure you have wp-cli installed on your server (See http://wp-cli.org/ for more info on installing if you do not have it installed)

2. Go to the root of your wordpress install
  ```
    wp plugin list --path="/var/www/html/drstest"
  ```
  where the path is the correct path to the root of your wordpress install. This will show you all of the plugins installed and which have updates available.

3. Run the Updates
  ```
    wp plugin update --path="/var/www/html/drstest --all"
  ```
  You can use --all to update all plugins which have updates or you can specify certain plugins

4. For more info on using wp-cli, go to https://make.wordpress.org/cli/handbook/

---
To migrate a toolkit site to a new VM

1. Copy over entire WP directory (including hidden files like .htaccess) to new VM

2. Export MySQL database

3. Create new database and import the export of the old database

4. Modify wp-config.php to point to new database

5. Modify DNS to point to new VM
