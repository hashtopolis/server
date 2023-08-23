# v0.14.0 -> x.x.x

## Bugfixes
- Clicking pretask in Supertask create screen now directs correctly to the pretask and not a task with the same id (#945)

## Benchmark cache system 
A cache for the benchmarks so that benchmarks can be reused and the benchmark process can be skipped when the benchmark has already been cached.
Key components:

- Added a benchmark model. The benchmark model got a field for all the dependencies of the benchmark output. These are the benchmark type (speed or runtime), the cracker binary, the hardware that is used, the attack parameters and the hashmode.
- Agents are split up into hardware groups, so that benchmark can be linked to a hardwaregroup and not a single agent.
- Benchmarks get invalidated by a time to live.
- frontend for the benchmarks in /benchmark.php to show what has been cached and to manually remove values.

# v0.13.1 -> v0.14.0

## Tech Preview New API
Release 0.14.0 comes with a tech preview of APIv2. This is the starting point of the seperating of the frontend and the backend and gives 
insight into what the future brings for Hashtopolis. We invite you to test it with the new web-ui and provide us with feedback. Be aware, 
it is a preview, it contains bugs and it will change; also it does not contain any permission checking. To use it, please see 
https://github.com/hashtopolis/server/wiki/Installation.

## Default installation method changed to Dockerimage
With the release 0.14.0 the default installation method changed to Docker. Docker images are now available at https://hub.docker.com/u/hashtopolis

## Bugfixes
- Setting 'Salt is in hex' during Hashlist creation will not set the --hex-salt flag (#892)

# v0.13.0 -> v0.13.1

## Bugfixes

- When deleting a supertask that was created from an import, pretasks that were removed from this supertask should also be deleted (issue #865).
- Setting config values to false using the user API now works as intended.
- When using the rulesplit function an internal server error was thrown. (#836)
- Deleting the last Hashlist resulted in an fatal error issue #888.

## Enhancements

- Hash.hash is now of type MEDIUMTEXT to avoid issues with longer hashes (e.g. LUKS, issue #851).

# v0.12.0 -> v0.13.0

## Features

- Added monitoring of CPU utilization of agents.
- Cracked hashes for all hashlists can be shown together (caution: only use when having smaller hashlists).
- Allow abort all chunks of a specific access group from the User API.
- Tasks can be set to top priority (to be first in the list) by the User API.
- Supertask runtime can be estimated on the supertask detail page by entering expected attack speeds for hashcat wordlist and bruteforce attacks.
- Number of agents per task can be limited (pull request #764).
- Hashlists can be archived.
- Added hashtype dropdown autocompletion for creating new hashlists (pull request #781).
- Allow agents to register as CPU agents only (feature request #805).

## Bugfixes

- Fixed search hash function.
- Fixed possible path traversal vulnerability on filename check.
- Fixed pre-crack import of lists with >1000 lines.
- Fixed availability of cracked hashes link on restrained permissions.
- Fixed access controls for owners of agents.
- Fixed improper updating of superhashlist counts on deletion of hashlists.
- Fixed missing .map files for javascript dependencies.
- Fixed users being able to access tasks with hashlists they would not be allowed to view.
- Fixed users being able to access hashlists they are not allowed to see.
- Adjusted handling to be able to deal with changed mode 22000 output.
- Fixed pagination of hashes on cracks page.
- Time of Zaps inserted is now saved.
- Fixed unable to unassign agent from the task detail screen.
- Fixed speed graph incorrect when status timer is different from servers default.
- Fixed sending two to headers when sending emails (issue #751).
- Fixed access group not being changed on Hashlist detailed screen (issue #765).
- Fixed missing check on permissions for sending notifications (issue #757).
- Fixed unassignable agents are shown as assignable (issue #777).
- Fixed not deleting all references (related to zaps) when deleting hashlist (issue #747).
- Added check for max length of the attack command (issue #668).
- Fixed missing flag isArchived on User API getTask requests (issue #794).

## Enhancements

- Cracker version and name are shown on task details.
- Task notes and cracker version are copied.
- Agent activity is also shown on the agent status page.
- Chunks for a task can be all view, instead of only the last 100.
- Allow changing the status interval for created tasks.
- Permissions for managing access groups is separate from the permission to manage users.
- The agent status page shows more detailed information on temperature and usage.
- JQuery updated to v3.6.0.
- Print database connection error in UI theme.
- Agent detail page now has a hide/show button for the config parameters.
- Agents overview page and agent detail page now show counter for repeating devices.
- Increase size of database column for storing agentstats.

# v0.11.0 -> v0.12.0

## Features

- Generic preprocessor integration to allow inclusion of any preprocessor supporting chunking.
- Dark mode added.

## Bugfixes

- Fixed increasing the superhashlist cracked count if there are cracks running one of the hashlists alone.
- Fixed hidden superhashlists on task creation page due to filtering.
- Fixed reporting result of health check which resulted in endless loop depending on the used IDs.
- Fixed reporting outdated speed on tasks page when agent is put inactive directly.
- Fixed recalculation of benchmark when changing chunk time.
- Fixed discord notification to work again.
- Fixed missing index structure on speed measurements table.

## Enhancements

- Agents can be assigned to tasks via user API.
- Server can be configured to provide 'isComplete' flag on the user API when requesting all tasks.
- Certain agent errors can be whitelisted to be completely ignored (for such who don't affect the running).
- Hashlists can be moved to other Access Groups after creation.
- Health checks can now be deleted.
- API keys can get masked if admin is not assigned to them.
- Agent data for temperature and util are split into separate graphs and have more different colors.
- Files can now be selected for either the cracker task or the preprocessor and are filled in the corresponding field.
- Included new Hashcat modes included in newest beta.
- Adjusted to new format of Hashcat printing cracked WPA hashes.
- Adjusted to PMKID handling of Hashcat.

# v0.10.1 -> v0.11.0

## Bugfixes

- Fixed wrong task speed summation for task overview page.
- Fixed error on hashlist hash retrieval.
- Fixed XSS on hashes view page when printing a hashlist.
- Fixed missing check for blacklisted characters when editing task.
- Fixed issue with creating a preconfigured task from the API.
- Fixed wrong rendering of forms when showing supertasks on hashlist pages.
- Fixed wrong reporting of speed on tasks overview due to cached speeds.
- Fixed wrong search value of tasks list on hashlist details page.
- Fixed missing update of cracked count for superhashlists.
- Fixed listing of hashlists and hashes of lists which should not be accessible by user.

## Enhancements

- Temperature and util thresholds for agent status page can be configured.
- User API can provide all cracks for a given task.
- User API provides information if task is complete or not.
- User API can provide all cracks for a given hashlist.
- Support for new Hashcat versions without 32/64-bit naming.

# v0.10.0 -> v0.10.1

## Bugfixes

- Fixed createHashlist API call with wrong brain parameter conversion.
- Fixed createUser API call with wrong amount of parameters.
- Fixed applying supertasks directly from hashlist view.
- Fixed wrong saving of build number if it didn't exist.

# v0.9.0 -> v0.10.0

## Features

- Integration of Hashcat Brain feature.
- Speed data is kept and can be shown in graphs for tasks.
- Agents can automatically de-register if allowed on the server.
- Agent updates can now automatically be retrieved, based on selected update track.
- Update scripts in the future can be handled differently. Applying updates is easier as there is a build number.

## Bugfixes

- Fixed wrong percentage in case of big tasks where percentage was close to 0.
- Rule splitting can only happen if at least two subparts get created afterwards.
- Fixed filesize calculation for temporary files after rule splitting.

## Enhancements

- In case of client errors the corresponding chunk now also is saved if available.
- Make more clear naming on rule splitting tasks, rules have an empty line at the end to increase readability.

# v0.8.0 -> v0.9.0

## Features

- The server saves the crackpos for hash founds given by hashcat.
- Trimming of chunks can be disabled so a chunk is always run fully again (or splitted if it is too large).
- Supertasks can now can be created by specifying a base command and iterate over a selection of files to be placed in the command.
- Notes can be added to hashlists.
- Added optional trace logging of actions from the client API to get more information in case of failures.
- Slow hashes are marked, so the client can decide if piping could make sense for this hash type.
- Agents can run health checks to determine if all agents are running correctly.

## Bugfixes

- Fixed GPU data graph when having multiple agents.
- Fixed assignment issue with subtasks of supertasks if they were in the same supertask.
- Fixed that cracker types cannot be deleted when there are supertasks using this type.

## Enhancements

- Telegram notifications can now completely be configured via server config and also can be used through proxies.
- Peppers of Encryption.class.php and CSRF.class.php were moved out of the files to make updating easier.
- When importing supertasks it can be selected if they should use the optimized flag and which benchmark type should be used.
- Subtasks are only loaded when being viewed to speed up loading of the tasks page.
- Changed type of the hash column to TEXT to make sure to handle all the long hashes. It should not affect speed as long as there is not a multi-million hashlist.
- Preconfigured task attack commands can be edited after creation.
- If needed it can be set that the server should also distribute tasks with priority 0.

# v0.7.1 -> v0.8.0

## Features

- The server can store sent debug output from Hashcat sent by the agent.
- Files now also are associated to an Access Group to control the visibility of files.
- Agent data about device temperature and util is collected and can be viewed on the server.
- Notes can be added to tasks.
- Static chunking (if for some reasone a fixed number of chunks or static chunk size should be used for a task)
- The server can provide a list of deleted filenames to the agent when he asks for.
- Tasks can now be copied to preconfigured tasks and preconfigured tasks can also be copied to preconfigured tasks.
- A test framework was added to run automated tests on Travis.
- To make sure rules are applied before rejecting, piping can be enforced.
- Added Notification type for Slack.

## Enhancements

- Task attack commands can be changed after creation, e.g. to fix typos
- Switch between tasks and archived ones is easier
- Archived tasks can be deleted at once
- Task priority can now be set directly in the task creation form.

## Bugfixes

- New task creation page now also shows the other file type.
- New file creation with the user API now takes the right file type.
- Vouchers are tested for uniqueness on creation to avoid duplicated ones.
- Disabling rule splitting when having a prince task.
- Fixed non-working secret checkbox for hashlists.

# v0.7.0 -> v0.7.1

## Bugfixes

- Fixed permission check for file downloads with URLs from the user API
- Fixed issue with creating supertasks from preconfigured task list
- Fixed creation of tasks from preconfigured tasks out of the hashlist view
- Fixed mask import
- Fixed hiding of mask imports in preconfigured task list on hashlist page

# v0.6.0 -> v0.7.0

## Features

- Tasks which are recognized containing large rule files and not giving good benchmarks result in splitting into subtasks
- Most of the tables can now be easily ordered and searched with the datatables plugin
- Agent Errors can be handled better
- New User API allowing access to all functions without the webinterface via simple JSON commands.
- Added new filetype (Other) for all non rules/wordlist files like hashcat charsets etc.
- File types can be edited of existing files.
- Tasks can now be archived instead of being deleted.

## Enhancements

- Width of the container is increased to have more space on large screens.
- Standard buttons have now icons instead of text to use less space.
- Hashcat is configured already as crack to make it easier for users to get started.

## Bugfixes

- Using correct function to get superhashlistId on zapping from webinterface.
- Zapping from the website will now also issue zaps for non-salted hashlists.
- Fixed zapping querying on progress sending from agent to also match for agent null values.

# v0.5.1 -> v0.6.0

## Features

- Added autofocus for login field
- Added fine grained permission management
- Updated Bootstrap and jQuery to newest versions
- Added Icons instead of images

## Bugfixes

- Export of founds of binary hashlists fixed
- DB Connection check during installation is now tested correctly

# v0.5.0 -> v0.5.1

## Bugfixes

- Fixed missing file assignments when applying preconfigured tasks from hashlists view (issue #354)
- Fixed cracker binary relation error when applying supertasks from hashlist view
- Fixed XSS vulnerability with the login forward variable
- Session cookies have the httpOnly flag set
- Fixed file upload which allowed upload of file:// data and reading it
- Fixed renaming of files which allowed renaming them to other directories and execute them
- Fixed renaming/uploading of files which allowed to override hidden files (e.g. .htaccess file)

# v0.4.3 -> v0.5.0

## Large Update

- Complete task management backend rewritten
- Improved performance when handling cracked hashes
- Added Groups for more detailed access control
- Including new python client
- Compatibility with generic crackers
- More configuration options added
- Cracker version management changed

## New Features

- Tasks now have a cracks per minute performance based on total spent time

## Bugfixes

- Fixed dependency problem on user deletion
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

- Various vulnerabilities (CVE-2017-11680, CVE-2017-11681, CVE-2017-11682) fixed, see [issue #241](https://github.com/hashtopolis/server/issues/241)

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

