#Solution for unable to delete Magento products issue 

[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=dbashyal&url=https://github.com/dbashyal&title=Github Repos&language=&tags=github&category=software)

##quoteFixer.php
If you are seeing sql error "SQLSTATE[22003]: Numeric value out of range: 1690 BIGINT UNSIGNED value is out of range in ‘(database.q.items_count – 1)’" when deleting magento products then run this quote fixer to solve this issue.
```
php quoteFixer.php
```

[<< Go Back](https://github.com/dbashyal/Magento-ecommerce-Shell-Scripts)

Some Tricks I found through google that I didn't test are: I believe You don't need to go through these hassles though.

```
DELETE FROM sales_flat_quote WHERE updated_at < DATE_SUB(Now(),INTERVAL 30 DAY);
```

```
DELETE FROM sales_flat_quote WHERE customer_is_guest = 0;
```

```
SET FOREIGN_KEY_CHECKS=0;
#truncate customer_sales_flat_quote;
#truncate customer_sales_flat_quote_address;
truncate flat_quote;
truncate flat_quote_item;
truncate flat_quote_address;
truncate flat_quote_shipping_rate;
SET FOREIGN_KEY_CHECKS=1;
```

For [magento tips and tricks](http://dltr.org/) visit dltr.org.
