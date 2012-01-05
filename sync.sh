#!/bin/bash
rsync --compress --exclude '.git' --exclude '*.psd' -av ./  root@vps.quebuenaestoy.com:/var/www/quebuenaestoy.com/
