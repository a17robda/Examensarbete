#!/bin/bash
/usr/local/spark-2.4.4-bin-hadoop2.7/bin/spark-submit \
  --class "SparkTests" \
  --master local[4] \
 ~/Documents/Examensarbete/sparkTests/target/scala-2.11/sparktests_2.11-1.0.jar

