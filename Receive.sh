#!/bin/bash
while true
do
    /usr/local/php/bin/php /data/wwwroot/www.mstz2019.com/run receive >> /data/wwwlogs/mslog/receive.log
done
#   chmod +x /data/wwwroot/www.mstz2019.com/Receive.sh ; nohup /data/wwwroot/www.mstz2019.com/Receive.sh > /dev/null 2<&1 &