# v0.3.2 -> v0.4.0

## New Features

- Renewed status page, gives now JSON formatted information which can be parsed however the user wants to.

# v0.3.1 -> v0.3.2

## Client

- Client updated to version 0.43.13

## Bugfixes

- fixed not sending notifications when using pre-task creation from hashlist details view
- 'Delete Finished' button now deletes also tasks of hashlists which are completely cracked
- on user deletion depending sessions now also get deleted
- fixed problems on task assignment where priorities were compared wrong
- clear all doesn't fail anymore when task list is empty
- fixed problem that on small tasks multiple agents got assigned and assignments were deleted immediately
- fixed issue that some agents suddenly got a very large chunk

## Features

- Added possibility to change isCpuOnly and isSmall on tasks after creation

## Technical

- removed old installation code which was used to upgrade Hashtopus to Hashtopussy 0.1.0
- reduced size of task progress image

