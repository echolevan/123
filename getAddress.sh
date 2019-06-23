#!/bin/bash
while true
do
    /usr/local/php/bin/php /data/wwwroot/www.mstz2019.com/run address >> /data/wwwlogs/mslog/address.log
done
#   chmod +x /data/wwwroot/www.mstz2019.com/getAddress.sh ; nohup /data/wwwroot/www.mstz2019.com/getAddress.sh > /dev/null 2<&1 &