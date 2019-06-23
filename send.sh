#!/bin/bash
while true
do
    /usr/local/php/bin/php /data/wwwroot/www.mstz2019.com/run send >> /data/wwwlogs/mslog/send.log
done
#   chmod +x /data/wwwroot/www.mstz2019.com/send.sh ; nohup /data/wwwroot/www.mstz2019.com/send.sh > /dev/null 2<&1 &