#!/bin/bash

for i in `php ../indexer.php info | awk {'print $1'}`;do time php ../indexer.php --reindex $i;done