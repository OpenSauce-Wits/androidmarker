# Moodle Plugins for Android Studio services

## Android Marker Assignment Feedback Plugin
This is a plugin that provides feedback for JUnit/Instrumented tests for Android Projects.

### Basic design
- Uses the File Feedback Plugin to accept input from students and lecturers.
- Sends the submissions to the Android Marker Server for evaluation.
- Displays gradle, compilation and syntax errors for failed tests.

### Use cases supported
- JUnit Testing
- Instrumentation Testing


### Plugin dependencies
- File Assignment Feedback Plugin (Comes with moodle)
- It works hand in hand with the Android Marker Server

### Configuration
- Web base for the Android Marker Server

## Installation
- Move file to the mod/assign/feedback/ directory in your Moodle directory 
