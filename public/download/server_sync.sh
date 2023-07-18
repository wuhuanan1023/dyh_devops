#!/bin/bash

API_URL="http://devops.dyhculture.com/api/server-sync"

#POST_DATA='{"key1":"value1", "key2":"value2"}'
declare -A POST_DATA

# 构建数据
format_data() {
    key=$1
    value=$2
    POST_DATA[${key}]=${value}
}

# 发送请求
http_post() {
    POST_DATA=$1




    curl -X POST -H "Content-Type: application/json" -d "${POST_DATA}" ${API_URL}
}

#获取硬盘信息
get_hard_disk() {
    log_file=/tmp/hard_disk.tmp
    df / |tail -n +2 > ${log_file}
    disk_total=$( cat ${log_file} |tail -n +1 |tr -s " " "%"|cut -d% -f2 )
    disk_used=$( cat ${log_file} |tail -n +1 |tr -s " " "%"|cut -d% -f3 )
    disk_available=$( cat ${log_file} |tail -n +1 |tr -s " " "%"|cut -d% -f4 )
    disk_used_percent=$( cat ${log_file} |tail -n +1 |tr -s " " "%"|cut -d% -f5 )
    disk_path=$( cat ${log_file} |tail -n +1 |tr -s " " "%"|cut -d% -f6 )


    echo $disk_total
    echo $disk_used
    echo $disk_available
    echo $disk_used_percent
    echo $disk_path
}





get_hard_disk
exit 1


