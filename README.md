Hevelop Regenerate Category Url for Magento 2.x
=====================

This module "force" the re-generation of category path and url. It's useful to apply edits made directly to the database
or from scripts.


Requirements
------------
- PHP >= 5.6.0

Compatibility
-------------
- Magento >= 2.0 

Installation Instructions
-------------------------
1. Install the extension via modman, composer or copy all the files into your document root;
2. Launch the command `php bin/magento setup:upgrade`

Usage
------
```
Usage:
 hevelop:regenurl -s|--store="..." [cids1] ... [cidsN]

Arguments:
 cids                  Category to regenerate

Options:
 --help (-h)           Display this help message
```

Eg:
```sh
# Regenerate url for all categories
php bin/magento hevelop:regencaturl

# Regenerate url for categories with id (1, 2, 3, 4)
php bin/magento hevelop:regencaturl 1 2 3 4
```

Uninstallation
--------------
1. Remove all extension files from your Magento installation

Developer
---------
Alessandro Pagnin @ [http://hevelop.com](http://hevelop.com)

Licence
-------
[GNU AFFERO GENERAL PUBLIC LICENSE 3.0](https://www.gnu.org/licenses/agpl-3.0.en.html)

Copyright
---------
(c) 2017 Hevelop
