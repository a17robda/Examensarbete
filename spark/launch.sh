#!/bin/bash
/usr/local/spark-2.4.5-bin-hadoop2.7/bin/spark-submit \
  --class "SparkApp" \
  --master spark://robin:7077 \
 ~/Documents/Examensarbete/spark/target/scala-2.11/sparkapp_2.11-1.0.jar $1 $2 $3


