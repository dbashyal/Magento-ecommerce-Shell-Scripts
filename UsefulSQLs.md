View current widget settings.
```
select pl.page_id, pl.layout_update_id, p.instance_id, i.package_theme, l.package, l.theme, i.store_ids, i.title, p.block_reference, p.layout_handle from 
	widget_instance i,
	core_layout_link l,
	widget_instance_page p,
	widget_instance_page_layout pl
where 
	pl.page_id = p.page_id
	and
	pl.layout_update_id = l.layout_update_id
	and
	p.instance_id = i.instance_id
	and
	i.package_theme not like '%mvau%'
	and
	i.package_theme not like '%mobile%'
	and
	i.package_theme not like '%enterprise/default%'
	;
```
