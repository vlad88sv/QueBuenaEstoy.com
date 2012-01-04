#!/bin/bash
rsync --compress --exclude '*.psd' -av ./  root@vps.mupi.com.sv:/var/www/quebuenaestoy.com/
