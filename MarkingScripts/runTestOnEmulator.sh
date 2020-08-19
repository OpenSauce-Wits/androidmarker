#!/bin/bash

rootDir=$(pwd)

# Makes the directory where the report will be stored
mkdir "$3"

# Changes to the directory with the gradlew file
cd "$(dirname "$(find -name *\\gradlew)")"

# Marks a shard on a device
ANDROID_SERIAL="$1" bash gradlew connectedDebugAndroidTest -Pandroid.testInstrumentationRunnerArguments.numShards="$2" -Pandroid.testInstrumentationRunnerArguments.shardIndex="$3" > myLogs.txt 2> logErrors.txt

# > myLogs.txt 2> logErrors.txt
#This command will redirect all standard output from Gradle logs to the myLogs.txt and all error messages to logErrors.txt

#Copies Report to the root
cd app/build/reports/androidTests/connected
report=$(find *InstrumentedTest.html)
mv $report report.html
cp -r report.html "$rootDir/$3"
