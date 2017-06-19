# v0.3.2 -> v0.x.x

## New Features

- Renewed status page, gives now JSON formatted information which can be parsed however the user wants to.
- added search page to search for hashes and plains
- '-r' is now automatically prepended when selecting rule files on task creation
- added help page with some helpful links

## Technical

- DB connection details now are stored in a file which is not in repository (a template is provided instead). This avoids conflicts on updates in `inc/load.php`
- Hash length is increased to 1024 (old 512)
- Added special case when handling pre-crack import of WPA as they are not matched via the hash but the ESSID instead.

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
- DB details are now saved separately to the other loading part, so conflicts on updates are avoided

## Technical

- removed old installation code which was used to upgrade Hashtopus to Hashtopussy 0.1.0
- reduced size of task progress image

