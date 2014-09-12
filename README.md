Magento Ecommerce Shell Scripts
===============================

[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=dbashyal&url=https://github.com/dbashyal&title=Github Repos&language=&tags=github&category=software)

Repo for various Magento ecommerce Shell Scripts

Where to upload?
- {magento-root}/app/shell/
 
How to run?
Go to shell folder location and run:

`$: php <filename>`

e.g.

`$: php update_attributes.php`

#Magento custom shell scripts:

##attributegroup.php
create product attributes, create attribute set group to all attribute sets and assign new attribute to this new group. it works for existing attributes too.

##changestatus.php

##fixSpecialDate.php
this script finds special dates with default 1970 and unsets dates, so special date is valid forever. You can modify for your requirement.

##inventory.php

##magento_cleanup.sh

##product.php

##showlayer.php

##suspendCustomers.php
Move user to suspended customer group (moves customers to suspended customer group if not logged-in for more than 10 months).

##update_attributes.php
Update mass attribute values. sample csv (/var/import/google_shopping.csv):

```
sku,rw_google_base_skip_submi,rw_google_base_product_type,rw_google_base_product_categ,rw_google_base_12_digit_sku,rw_google_base_adw_grouping,rw_google_base_adw_labels
back_freezedried_singleserve ,0,"Sporting Goods > Outdoor Recreation > Camping, Backpacking & Hiking > Freeze-Dried Food","Sporting Goods > Outdoor Recreation > Camping, Backpacking & Hiking > Freeze-Dried Food",back_freezedried_singleserve ,,
```

###visit: http://dltr.org/ for more magento tips, tricks and codes.
