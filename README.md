###############################################
MenuCache
Pre 0.1 proof of concept
###############################################

Developer
-----------------------------------------------
Nicolaas Francken [at] sunnysideup.co.nz

Requirements
-----------------------------------------------
SilverStripe 2.3.0 or greater.

Documentation
-----------------------------------------------
This module allows you to cache the menu and other parts
of your pages. You do this to reduce processing time. For example,
if a menu includes all the pages on your website, then it will
require significant time for PHP + DB to compile the menu.

As a trade-off, it will increase the size of your database.

The way it works is that, the first time, the page is opened,
it will create the page as per usual.  The nex time, the page
is opened it will then retrieve the "marked" parts from the database
rather than creating them from the template.

The first time a page is loaded, it will run the standard menu,
while doing so, it saves the "marked" parts of the page (e.g. menu) to the database
The way it saves it is as an extra field in the SiteTree table.
Everytime the page is loaded after that, it will retrieve
the cached sections (e.g. menu) as one big chunk of html.

Everytime a page in SiteTree is saved, it will CLEAR all the
saved menus and other saved parts.

The code provides several flushing tricks:
http://www.mysite.com/home/showcachedfield/[0-4]/?flush=1
http://www.mysite.com/home/clearfieldcache/[0-4]/?flush=1
http://www.mysite.com/home/showuncachedfield/[0-4]/?flush=1
http://www.mysite.com/home/clearallfieldcaches/?flush=1
http://www.mysite.com/home/clearallfieldcaches/days/100?flush=1 - clear caches created more than 100 days ago
http://www.mysite.com/home/clearallfieldcaches/thispage/?flush=1 - clear caches for home page (or whatever page) only.

Common things to cache are:
* header / footer sections
* menus

It will be particular useful in areas where you do
a lot of processing and database accessing (e.g.
building a LARGE menu).

You can have up to five cached areas on your pages.

Installation Instructions
-----------------------------------------------
1. Find out how to add modules to SS and add module as per usual.

2. copy configurations from this module's _config.php file
into mysite/_config.php file and edit settings as required.
NB. the idea is not to edit the module at all, but instead customise
it from your mysite folder, so that you can upgrade the module without redoing the settings..

3. setup the following files:
/themes/mytheme_menucache/templates/Includes/CachedField0.ss
/themes/mytheme_menucache/templates/Includes/CachedField1.ss
/themes/mytheme_menucache/templates/Includes/CachedField2.ss
/themes/mytheme_menucache/templates/Includes/CachedField3.ss
/themes/mytheme_menucache/templates/Includes/CachedField4.ss

4. within each of these files, include the actual template you want to use
e.g. add <% include Navigation %> to
/themes/mytheme_menucache/templates/Includes/CachedField0.ss

5. within /themes/templates/Page.ss (or wherever), add
$CachedField([0-4]) to add your cached area.
adding $CachedField(0) to your template will show
Navigation.ss in the example above.

6. Consider if there are any parts dataobject that need to include
the "clear cache call" on before write. Those will be data objects
that are cached on pages and shown as such.

*** NOTE: [0-4]: choose a number between 0 and 4, you can have up to
five cached fields: 0, 1, 2, 3, and 4

----------------------- STATIC PUBLISHING -------------------------------

example htaccess file (NOTE .php as the extension)

also see: http://svn.silverstripe.com/open/modules/cms/trunk/code/staticpublisher/
-----------------------------------------------

### SILVERSTRIPE START ###
# Cached content -
RewriteCond %{REQUEST_METHOD} ^GET$
RewriteCond %{QUERY_STRING} ^$
RewriteCond %{REQUEST_URI} ^/(.*)$
RewriteCond %{REQUEST_URI} /(.*[^/])/?$
RewriteCond %{DOCUMENT_ROOT}/cache/%1.php -f
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* /cache/%1.php [L]

# Cached content - homepage
RewriteCond %{REQUEST_METHOD} ^GET$
RewriteCond %{QUERY_STRING} ^$
RewriteCond %{REQUEST_URI} ^/?$
RewriteCond /cache/index.php -f
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* /cache/index.php [L]

# Dynamic content
RewriteCond %{REQUEST_URI} !(\.gif)|(\.jpg)|(\.png)|(\.css)|(\.js)|(\.php)$
RewriteCond %{REQUEST_URI} ^(.*)$
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* /sapphire/main.php?url=%1&%{QUERY_STRING} [L]
### SILVERSTRIPE END ###
