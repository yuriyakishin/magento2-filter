# Layered Navigation - MultiSelect filter for simple product with multiselect colors
Magento2 Yu_Filter is multifiltration module with colors attribute for simple products. 
## Installation

```
$ composer require "yuriyakishin/magento2-filter"
```
or copy files of module to folder `app/code/Yu/Filter`.

Run these commands in your terminal:

```
php bin/magento module:enable Yu_Filter
php bin/magento setup:upgrade
```

## The filter has two usage modes, AND and OR.

![alt text](/docs/yu_filter_config.jpg "Configuration of Yu_Filter")
Configuration of Yu_Filter `Stores - Configuration - Catalog - Catalog - Layered Navigation`.

## Color Filtering for Simple Products.

![alt text](/docs/yu_filter_1.jpg "Configuration of yu_colors attribute")
Configuration of yu_colors attribute.

![alt text](/docs/yu_filter_2.jpg "Product color settings")
Product color settings.

![alt text](/docs/yu_filter_3.jpg "Product color settings")
Frontend color filter.