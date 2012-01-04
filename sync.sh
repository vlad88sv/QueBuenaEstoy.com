#!/bin/bash
rsync --compress --exclude '.git' --exclude '*.psd' -av ./  root@vps.mupi.com.sv:/var/www/quebuenaestoy.com/
