Wordpress Skeleton
==================

**wp-skeleton** is a very thin wrapper around [wp-skeleton-installer](https://github.com/wemakecustom/wp-skeleton-installer).
The easy way to install WordPress and get it works.

## What it does

If you use this wrapper, you will get :
- Packages dependencies management with composer :
  * wemakecustom/wp-skeleton-installer (needed for this wrapper to work properly) 
  * wemakecustom/wp-skeleton-theme
  * wemakecustom/wp-skeleton-theme-demo (child theme in dev environnement)
  * leafo/lessphp
  * wpackagist-plugin/w3-total-cache
  * wpackagist-plugin/wordpress-seo
- Libraries dependencies management with bower :
  * jquery (version <2.0)
  * bootstrap (version ~3.0)
  * font-awesome
  * console-polyfill
  * underscore
  * modernizr
- a LESS compiler (LESSC.php).
- a basic .HTACCESS (that include rewriterules for LESS files, compression, expires headers).

## Installation

Clone this git repo.
````
$ git clone git@github.com:wemakecustom/wp-skeleton.git
````
Run composer to install.
````
$ composer install
````
Or if you doesn't need the demo theme
````
$ composer install --no-dev
````
Run bower to get all recent script up to date in _/wp-content/components_.
````
$ bower update
````

## Documentation

- https://github.com/wemakecustom/wp-skeleton-installer
- https://github.com/wemakecustom/wp-skeleton-theme
- https://github.com/wemakecustom/wp-skeleton-theme-demo
- https://github.com/leafo/lessphp
- http://wpackagist.org/
