# v0.4.3 -> v0.5.0

## Large Update

- Complete task management backend rewritten
- Improved performance when handling cracked hashes
- Added Groups for more detailed access control
- Including new python client
- Compatibility with generic crackers
- More configuration options added
- Cracker version management changed

## Bugfixes

- Fixed issue when agents got deleted which had completed at least one chunk
- Fixed conflicts on $_POST data agent vs. agentId
- Fixed ETA and spent time for tasks
- Error message which was always shown when adding new hash types fixed

# v0.4.2 -> v0.4.3

## New Features

- Added telegram bot notification
- Supertasks can now also be applied when viewing hashlist details (similar to preconfigured tasks)

## Bugfixes

- Notification display fixed
- Updated problem where agents were looping when tasks go over 100%

## Technical

- Fixed warnings during found import
- Fixed edge case where it could happen that agents started to loop after a task when no new task was available
- Pre-crack import warns when too long plaintexts are in the import file
- Implemented missing ownAgentError notification execution

# v0.4.1 -> v0.4.2

## New Features

- Supertask imports can now be set to be small tasks for every subtask

## Bugfixes

- Fixed broken agent download

## Technical

- Typos in constants fixed
- Tasks can also be deleted from the detailed view
- Fixed update to 0.4.0 when adding taskType column
- Supertasks are getting default priority from the subtasks
- Fixed DBA issue with handling invalid input
- Fixed additional vulnerabilities reported
- Fixed remaining fragments when deleting finished supertasks

# v0.4.0 -> v0.4.1

## Bugfixes

- Various vulnerabilities (CVE-2017-11680, CVE-2017-11681, CVE-2017-11682) fixed, see [issue #241](https://github.com/s3inlc/hashtopolis/issues/241)

## Technical

- Improved code handling, constants can be used in templates.

# v0.3.2 -> v0.4.0

## New Features

- Renewed status page, gives now JSON formatted information which can be parsed however the user wants to.
- added search page to search for hashes and plains
- '-r' is now automatically prepended when selecting rule files on task creation
- added help page with some helpful links
- Left hashlists can be downloaded now
- Added Yubikey OTP login
- Supertasks added
- HCmask style can be imported

## Technical

- DB connection details now are stored in a file which is not in repository (a template is provided instead). This avoids conflicts on updates in `inc/load.php`
- Hash length is increased to 1024 (old 512)
- Added special case when handling pre-crack import of WPA as they are not matched via the hash but the ESSID instead.
- Added new hashtypes from Hashcat
- Server hostname can be overridden in config

## Client

- Client updated to version 0.43.19 
- Fixed debug not showing hashcat parameters on calls
- Improve error handling on keyspace measuring, client will now signal the server to pause agent instead of crash
- Added more information for task assigning, client will now display task and hashlist on task get
- Fixed slow file downloading issue
- Changed the way hashcat version is queried (should work properly on linux/mac)

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

- removed old installation code which was used to upgrade Hashtopus to Hashtopolis 0.1.0
- reduced size of task progress image

