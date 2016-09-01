#!/bin/bash

while true
do
    cat /proc/meminfo | grep MemFree
    cat /proc/loadavg
    ps ax
    sleep 60
done