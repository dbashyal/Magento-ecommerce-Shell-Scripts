Magento Ecommerce Shell Scripts
===============================

[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=dbashyal&url=https://github.com/dbashyal&title=Github Repos&language=&tags=github&category=software)

##Useful direct SQL queries
#### shhh! I know ... ;)

Update magento widgets theme and package info after you change your design configuration
```
UPDATE widget_instance i, core_layout_link l, widget_instance_page p, widget_instance_page_layout pl SET i.package_theme = 'dse/fluency', l.package = 'dse', l.theme = 'fluency' where pl.page_id = p.page_id and pl.layout_update_id = l.layout_update_id and p.instance_id = i.instance_id and i.package_theme not like '%mvau%' and i.package_theme not like '%mobile%' and i.package_theme not like '%enterprise/default%' and i.store_ids = 1;
```


###visit: http://dltr.org/ for more magento tips, tricks and codes.
