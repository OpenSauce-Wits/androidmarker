#!/bin/bash
# This script create a template, from the LecturerZip.zip and StudentZip,
# to be used for marking
# It assumes there is already an emulator running in the adb

################################################################################
if [ "$ANDROID_SDK_ROOT" == "" ];
then
  echo "No sdk root"
  exit 1
fi

# This code searches for available emulators that are running and stores them in
# a list
count=0
numDevices=0
deviceName=""
AvailableDevices=()
for device in $(adb devices)
do
 count=$(($count + 1))
 # Each device listed has its device name and device state
 # This if condition checks for device state
 if [ $(($count%2)) == 0 ];
 then
  numDevices=$(($numDevices + 1))
  # The first 4 values in the list are from the sentence "List of devices attached"
  if [ $(( numDevices > 2 )) ];
  then
   # Checks if the emulator is available
   if [ "$device" == "device" ];
   then
    # If it is available it adds it to the list of available devices
    AvailableDevices[${#AvailableDevices[@]}]="$deviceName"
    # Tests may fail because of the wrong version of the tests being installed
    # on the emulator
    #adb -s $deviceName uninstall ${packageName}.debug
    #adb -s $deviceName uninstall "com.bookings.test"
   fi
  fi
 else
  # else condition checks for device name
  deviceName=$device
 fi
done
# the first two are from the string "List of devices attached"
numDevices=$(( $numDevices - 2))

#php -r "\core\notification::warning(Number of devices: $numDevices);"
if [ "0" == "$numDevices" ];
then
  echo "No devices"
  exit 1
fi

# Declares Variables
textFile="RequiredDocuments.txt"
RequiredDocuments=()
rootDir=$(pwd)
androidProject=""

################################################################################
# FUNCTIONS
################################################################################

# Deletes all files that aren't "RequiredDocuments", "PrepareTemplate.sh",
# "LecturerZip.zip" and "StudentZip.zip"
clear_files () {
  for file in $(ls)
  do
    if [ "$file" != "RequiredDocuments.txt" ] && [ "$file" != "Mark.php" ] && [ "$file" != "getHtmlResults.sh" ] && [ "$file" != "runTestOnEmulator.sh" ] && [ "$file" != "LecturerZip.zip" ] && [ "$file" != "StudentZip.zip" ] && [ "$file" != "MarkProject.sh" ] && [ "$file" != "SetupOfAndroidEmulator.txt" ];
    then
      rm -rf "$file"
    fi
  done
}

#Opens the project
open_project () {
  for file in $(ls)
  do
    if [ "$file" != "RequiredDocuments.txt" ] && [ "$file" != "Mark.php" ] && [ "$file" != "getHtmlResults.sh" ] && [ "$file" != "runTestOnEmulator.sh" ] && [ "$file" != "LecturerZip.zip" ] && [ "$file" != "StudentZip.zip" ] && [ "$file" != "MarkProject.sh" ] && [ "$file" != "SetupOfAndroidEmulator.txt" ];
    then
    	androidProject="$file"
      cd "$file"
    fi
  done
}

################################################################################
# Starts by clearing all files that are not Required
clear_files

# Reads in the RequiredDocuments lines from the RequiredDocuments text file and
# stores them in the RequiredDocuments array.
while IFS= read -r line
do
  RequiredDocuments[${#RequiredDocuments[@]}]="$line"
  # echo "${RequiredDocuments[NumReqDocs]}"
done < "$textFile"
write "Required Documents Read"

# Unzip the LecturerZip.zip
unzip LecturerZip.zip >/dev/null
write "Lecturer Project Extracted"

# Checks if all the RequiredDocuments exist and delete them
# Also stores the parent directories of the documnets
dir=""
ParentDirectories=()
for doc in ${RequiredDocuments[@]}
do
  dir=$(find -name *\\$doc)
  if [ "$dir" == "" ];
  then
    # Should terminate script
    write "$doc doesn't exist. Please check RequiredDocuments.txt."
    clear_files
    exit 1
  else
    ParentDirectories[${#ParentDirectories[@]}]="$(dirname "$dir")"
    rm -f "$dir"
  fi
done
write "Required Documents Deleted From Template"
write "Required Documents Directories Extracted"

# By now the documnets in RequiredDocuments.txt have been deleted.
# Searching for them will only bring up the files in the StudentZip

# Make directory to store student Code
mkdir StudentCode

# Unzip the StudentZip.zip
unzip StudentZip.zip -d StudentCode >/dev/null
write "Student Code Extracted"

# Checks if all the RequiredDocuments exist in the student's code and copies
# them into the template.
count=0
for doc in ${RequiredDocuments[@]}
do
  dir=$(find -name *\\$doc)
  if [ "$dir" == "" ];
  then
    # Should terminate script
    write "$doc doesn't exist in the student's zip. Please check StudentZip.zip"
    clear_files
    exit 1
  else
    cp -R "$dir" ${ParentDirectories[$count]}
    count=$(($count + 1))
  fi
done
write "Required Documents Copied From StudentZip"

# Removes the Student's code folder
rm -rf StudentCode

write "Marking Project"

open_project
cd "$(dirname "$(find -name *\\gradlew)")"
chmod +x gradlew
bash gradlew assembleDebugAndroidTest

# could not change permissions of SDK root from server. All of them were denied
#chmod -R +x $ANDROID_SDK_ROOT

# Check that searches for ready emulators
bash gradlew installDebug

# numDevices is the number of shards we have
# looping through the list will give us the shard id for each emulator
# The command to run the shards needs specific values that can only be obtained
# in sequence.
# So i will obtain a string of the commands first
count=0
ParallelCommands=()
for device in ${AvailableDevices[@]}
do
 # Now without the & at the end of the command, the marking would not be done in parallel. The shards only split the tests
 ParallelCommands[${#ParallelCommands[@]}]="$device $numDevices $count"
 count=$(($count + 1))
done

for comm in "${ParallelCommands[@]}"
do
 cd "$rootDir"
 bash runTestOnEmulator.sh $comm 3>&2 &
done

# The wait makes sure that the computer doesn't carry on with this script before all the tasks are complete
wait

rm -rf "$androidProject"
write "Report Generated"
# basename "$filename" : strips away the previous directories
