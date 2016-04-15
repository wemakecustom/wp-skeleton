Wordpress Skeleton
==================

Thin wrapper around [wp-skeleton-installer][1] to easly install WordPress and get it works.


## What it does

Install selected plugins and a custom theme for a clean starting WordPress:
* [wemakecustom/wp-skeleton-installer][1] (version ^3.0) is needed for this wrapper to work properly
* [wpackagist-plugin/w3-total-cache][4] (version ^0.9)
* [wpackagist-plugin/wordpress-seo][5] (version ^3.0)
* [wemakecustom/wp-skeleton-theme][2]
* [wemakecustom/wp-skeleton-theme-demo][3]
* [leafo/lessphp][6] (version ^0.5) to compile LESS files
* and an ``.htaccess`` that include rewriterules for LESS files, compression and expires headers.

Install libraries for an easy custom integration:
* [bootstrap][7] (version ~3.0) is a front-end framework for faster and easier web development.
* [font-awesome][8] (version ~4.0) is an iconic font for easy scalable vector graphics.
* [jquery][9] (version <2.0) is the most used javascript library.
* [modernizr][10] (version ~3.0) is a javascript library that detects HTML5 and CSS3 in user browser.
* [underscore][11] provides a lot of useful javascript helpers.
* [console-polyfill][12] is a light library to makes it safe to do ``console.log()``.

[1]: https://github.com/wemakecustom/wp-skeleton-installer
[2]: https://github.com/wemakecustom/wp-skeleton-theme
[3]: https://github.com/wemakecustom/wp-skeleton-theme-demo
[4]: https://en-ca.wordpress.org/plugins/w3-total-cache
[5]: https://en-ca.wordpress.org/plugins/wordpress-seo
[6]: https://github.com/leafo/lessphp
[7]: https://github.com/twbs/bootstrap
[8]: https://github.com/FortAwesome/Font-Awesome
[9]: https://github.com/jquery/jquery
[10]: https://github.com/modernizr/modernizr
[11]: https://github.com/jashkenas/underscore
[12]: https://github.com/paulmillr/console-polyfill


## Installation

````
$ git clone git@github.com:wemakecustom/wp-skeleton.git <project>
$ cd <project>
$ composer install
````
If you doesn't need the ``wp-skeleton-theme-demo`` theme:

````
$ composer install --no-dev
````
By default, at the end, you will get WordPress and all configs files installed in ``<project>/htdocs/`` subfolder. To configure another subfolder, please see section _configuration_ bellow.

After that, you have to create the WordPress tables in the database with [wp-cli][13], a command-line tools for managing WordPress.
You can read [more information here][14] about ``wp core install`` command.

````
$ bin/wp core install --path=htdocs/ --url=<url> --title=<site-title> --admin_user=<username> --admin_password=<password> --admin_email=<email>
````
Then, finish by running bower to get all recent script up to date in ``<project>/htdocs/wp-content/components/``.

````
$ bower install
````

[13]: http://wp-cli.org/
[14]: http://wp-cli.org/commands/core/install/
[15]: https://github.com/wemakecustom/wp-skeleton-installer#configuration


## Configuration

The following section in ``composer.json`` allow you to change the folder where WordPress and the config files are installed.
For details about this section, please see [wp-skeleton-installer][15].

```json
{
    "extra": {
        "wordpress-install-dir": "vendor/wordpress/wordpress",
        "web-dir": "htdocs",
        "installer-paths": {
            "htdocs/wp-content/plugins/{$name}/": ["type:wordpress-plugin"],
            "htdocs/wp-content/mu-plugins/{$name}/": ["type:wordpress-muplugin"],
            "htdocs/wp-content/themes/composer/{$name}/": ["type:wordpress-theme"]
        }
    }
}
```
