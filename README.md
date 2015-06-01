Wordpress Skeleton
==================

**wp-skeleton** is a very thin wrapper around https://github.com/wemakecustom/wp-skeleton-installer.
The easy way to install WordPress and get it work.

##Features
- All the needed files (including the .gitignore) are installed.
- Dependency management with composer and bower.
- LESS compiler.
- A basic .htacess _(with rewriterule, compression and expires headers)_.

##Installation

Clone the git repo
````
$ git clone git@github.com:wemakecustom/wp-skeleton.git
````
Run composer to install all packages that **wp-skeleton** depends on.
````
$ composer install
````
Run bower to get all recent script up to date.
````
$ bower update
````

##Result
Running *composer* of **wp-skeleton** will install all the following needed packages:
- wp-skeleton-installer (and its dependencies) 
- lessphp
- w3-total-cache
- wordpress-seo
- wp-skeleton-theme
- wp-skeleton-theme-demo (child theme in dev environnement)

Running *bower* will insure that the following scripts are installed in _/wp-content/components_:
- jquery
- bootstrap
- font-awesome
- console-polyfill
- underscore
- modernizr

##Documentation
- https://github.com/wemakecustom/wp-skeleton-installer
- https://github.com/leafo/lessphp
- http://wpackagist.org/
- https://github.com/wemakecustom/wp-skeleton-theme
- https://github.com/wemakecustom/wp-skeleton-theme-demo