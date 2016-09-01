#!/bin/bash

while true
do
    cat /proc/meminfo | grep MemFree
    cat /proc/cpuinfo | grep processor
    cat /proc/loadavg
    sleep 300
done