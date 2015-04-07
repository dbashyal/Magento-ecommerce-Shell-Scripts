#Solution for unable to delete Magento products issue 

[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=dbashyal&url=https://github.com/dbashyal&title=Github Repos&language=&tags=github&category=software)

##quoteFixer.php
If you are seeing sql error "SQLSTATE[22003]: Numeric value out of range: 1690 BIGINT UNSIGNED value is out of range in ‘(database.q.items_count – 1)’" when deleting magento products then run this quote fixer to solve this issue.
```
php quoteFixer.php
```
\[[download script](shell/quoteFixer.php)\]

[<< Go Back](https://github.com/dbashyal/Magento-ecommerce-Shell-Scripts)

Some Tricks I found through google that I didn't test are: I believe You don't need to go through these hassles though.

```
// source: http://www.learnmagento.org/magento-bug-fixes/magento-fix-sqlstate22003-numeric-value-range-1690-bigint-unsigned-value-range/

// step 1
DELETE FROM sales_flat_quote WHERE updated_at < DATE_SUB(Now(),INTERVAL 30 DAY);

// step 2 (if step 1 doesn't help)
DELETE FROM sales_flat_quote WHERE customer_is_guest = 0;

// step 3 (if step 2 doesn't help)
SET FOREIGN_KEY_CHECKS=0;
#truncate customer_sales_flat_quote;
#truncate customer_sales_flat_quote_address;
truncate flat_quote;
truncate flat_quote_item;
truncate flat_quote_address;
truncate flat_quote_shipping_rate;
SET FOREIGN_KEY_CHECKS=1;
```

## How to Fix: (source: [golocalexpert.com](https://www.golocalexpert.com/resolving-the-infamous-magento-deleting-issue-sqlstate22003-numeric-value-out-of-range-1690-bigint-unsigned-value-is-out-of-range-in-yourdbname-q-items_count-1/))

1. Go into your database using PhpMyAdmin.
2. Do a backup (always a good practice to back-up first).
3. Select the Magento database you’re using.
4. Select table sales_flat_quote (on second page).
5. Select structure tab
6. Select ‘change‘ on row called ‘items_count‘
7. Go to the drop-down on the column named ‘Attributes‘ and change value to the very top value which is blank ‘(no value)‘, as opposed to the default selection ‘UNSIGNED‘ .
8. Click save, and you’re good to go!

You should now be able to delete your products with no more error.

For [magento tips and tricks](http://dltr.org/) visit dltr.org.
