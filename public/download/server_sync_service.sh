#!/bin/bash

###主要目的用于开机启动服务
SERVER_SYNC=/usr/local/server/server_sync.sh

SERVER_SYNC_SCRIPT_LOG=/usr/local/server/runtime.log

#指令
action=$1

#pid
pid=`ps -ef | grep "${SERVER_SYNC_SCRIPT}" | grep -v 'grep'| awk '{print $2}'| wc -l`
if [ "$action" = "start" ];then
  if [ $pid -gt 0 ];then
      echo 'server synchronization script has already running...'
  else
    if [ ! -d $JENKINS_HOME ];then
        mkdir -p $JENKINS_HOME
    fi
    ### 启动sh
    nohup ${SERVER_SYNC_SCRIPT} >${SERVER_SYNC_SCRIPT_LOG} 2>&1 &
    echo 'server synchronization script is starting...'
  fi
elif [ "$action" = "stop" ];then
  exec ps -ef | grep jenkins | grep -v 'grep' | awk '{print $2}'| xargs kill -9
  echo 'server synchronization script has been stopped...'
else
  echo "Please input like this:"./${SERVER_SYNC_SCRIPT} start" or "./${SERVER_SYNC_SCRIPT} stop""
fi
