Intro
=====
- Current Group API and User API are not consistant can compatible with each-other.
- Permissions seems very fine-grain e.g. set for certain action, request to make more generic.



Suggestion
==========
4 Base groups
 - VIEW: View/Search/Qeuery list of objects. View object details.
 - CREATE: Create new object.
 - MANAGE: Alter existing object.
 - DELETE: Delete existing object.

all set per API endpoint


Behaviour in dual-setup (old and new UI)
----------------------------------------
 - Option A:
    - Changing (old) permissions will set new permissions if-and-only-if all required old permissions are set.
    - Changing (new) permissions will set old permissions of this mapping.
 - Option B:
    - Yet-an-other-permission schema which only applies to the new UI.


Current permission scheme to new permission scheme mapping
==========================================================
Current permission scheme and in brackets the proposed new permissions layout


- Access Management (GUI - group)
  - Can view Hashlists [VIEW_HASHLIST]
  - Can manage hashlists [MANAGE_HASHLIST]
  - Can create hashlists [CREATE_HASHLIST]
  - Can create superhashlists [CREATE_SUPERHASHLIST]
  - User can view cracked/uncracked hashes 	[VIEW_HASHES]
  - Can view agents [VIEW_AGENTS]
  - Can manage agents [MANAGE_AGENTS]	
  - Can create agents [CREATE_AGENTS]
  - Can view tasks [VIEW_TASKS]
  - Can run preconfigured tasks [CREATE_TASK]
  - Can create/delete tasks [CREATE_TASK,DELETE_TASK]
  - Can change tasks (set priority, rename, etc.) [CHANGE_TASK]
  - Can view preconfigured tasks [VIEW_PERCONFIGURED_TASK]
  - Can create/delete preconfigured tasks [CREATE_PRECONFIGURED_TASK]
  - Can manage preconfigured tasks [MANAGE_PRECONFIGURED_TASK]
  - Can view preconfigured supertasks [VIEW_PRECONFIGURED_SUPERTASK]
  - Can create/delete supertasks [MANGE_SUPERTASK]
  - Can manage preconfigured supertasks. [MANAGE_PRECONFIGURED_SUPERTASK]
  - Can view files [VIEW_FILES]
  - Can manage files [MANAGE_FILES, DELETE_FILES]
  - Can add files [MANAGE_FILES] 	
  - Can configure cracker binaries [MANGE_CRACKER_BINARIES]	
  - Can access server configuration [MANAGE_CONFIG]	
  - Can manage users [MANAGE_USERS]
  - Can manage access groups. [MANAGE_GROUPS, MANAGE_PERMISSIONS]


- User API (API Management)
  - Test
     - Connection testing [VIEW_TEST]
     - Verifying the API key and test if user has access to the API [VIEW_TEST]
   - Agent
     - Creating new vouchers [CREATE_VOUCHER]
     - Get a list of available agent binaries [VIEW_AGENT_BINARIES]
     - List existing vouchers 	[VIEW_VOUCHER]
     - Delete an existing voucher [DELETE_VOUCHER]
     - List all agents [VIEW_AGENTS]
     - Get details about an agent [VIEW_AGENTS]	
     - Set an agent active/inactive [MANAGE_AGENTS]
     - Change the owner of an agent [MANAGE_AGENTS]	
     - Set the name of an agent [MANAGE_AGENTS]	
     - Set if an agent is CPU only or not [MANAGE_AGENTS]	
     - Set extra flags for an agent [MANAGE_AGENTS]	
     - Set how errors from an agent should be handled [MANAGE_AGENTS] 	
     - Set if an agent is trusted or not [MANAGE_AGENTS]	
     - Delete agents [DELETE_AGENTS]
   - Task
     - List all tasks [VIEW_TASKS]	
     - Get details of a task [VIEW_TASKS]	
     - List subtasks of a running supertask [VIEW_TASKS]
     - Get details of a chunk [VIEW_CHUNKS]	
     - Retrieve all cracked hashes by a task [VIEW_HASHES]
     - Create a new task [CREATE_TASKS]
     - Run an existing preconfigured task with a hashlist [CREATE_TASKS]
     - Run a configured supertask with a hashlist [CREATE_TASKS]
     - Set the priority of a task [MANAGE_TASKS]
     - Set task priority to the previous highest plus one hundred [MANAGE_TASKS]	
     - Set the priority of a supertask 	[MANAGE_TASKS]
     - Set supertask priority to the previous highest plus one hundred 	[MANAGE_TASKS]
     - Rename a task [MANAGE_TASKS]	
     - Set the color of a task 	[MANAGE_TASKS]
     - Set if a task is CPU only or not [MANAGE_TASKS]	
     - Set if a task is small or not [MANAGE_TASKS]	
     - Set max agents for tasks [MANAGE_TASKS]	
     - Unassign an agent from a task [MANAGE_AGENTS]	
     - Assign agents to a task 	MANAGE_AGENTS]
     - Delete a task [DELETE_TASKS]	
     - Purge a task [MANAGE_TASKS]	
     - Set the name of a supertask 	[MANAGE_SUPERTASKS]
     - Delete a supertask [MANAGE_SUPERTASKS]
     - Archive tasks [MANAGE_TASKS]
     - Archive supertasks [MANAGE_SUPERTASKS]
   - Pretask 
     - List all preconfigured tasks [VIEW_PRETASKS]	
     - Get details about a preconfigured task [VIEW_PRETASKS]	
     - Create preconfigured tasks [CREATE_PRETASKS]	
     - Set preconfigured tasks priorities [MANAGE_PRETASKS]	
     - Set max agents for a preconfigured task [MANAGE_PRETASKS]	
     - Rename preconfigured tasks [MANAGE_PRETASKS]	
     - Set the color of a preconfigured task [MANAGE_PRETASKS]	
     - Change the chunk size for a preconfigured task [MANAGE_PRETASKS]
     - Set if a preconfigured task is CPU only or not [MANAGE_PRETASKS]	
     - Set if a preconfigured task is small or not [MANAGE_PRETASKS]	
     - Delete preconfigured tasks [DELETE_PRETASKS]
   - Supertask 
     - List all supertasks 	[VIEW_SUPERTASKS]
     - Get details of a supertask [VIEW_SUPERTASKS]	
     - Create a supertask [CREATE_SUPERTASKS]	
     - Import a supertask from masks [CREATE_SUPERTASKS]	
     - Rename a configured supertask [MANAGE_SUPERTASKS]	
     - Delete a supertask [DELETE_SUPERTASKS]	
     - Create supertask out base command with files [CREATE_SUPERTASKS]
   - Hashlist
     - List all hashlists [VIEW_HASHLISTS]
     - Get details of a hashlist [VIEW_HASHLISTS]	
     - Create a new hashlist [CREATE_HASHLISTS]	
     - Rename hashlists [MANAGE_HASHLISTS]	
     - Set if a hashlist is secret or not [MANAGE_HASHLISTS]	
     - Query to archive/un-archie hashlist [MANAGE_HASHLISTS]
     - Import cracked hashes [MANAGE_HASHLISTS]	
     - Export cracked hashes [VIEW_HASHES]	
     - Generate wordlist from founds [VIEW_HASHES]	
     - Export a left list of uncracked hashes 	
     - Delete hashlists [DELETE_HASHLISTS]	
     - Query for specific hashes [VIEW_HASHES]	
     - Query cracked hashes of a hashlist [VIEW_HASHES]
   - Superhashlist
     - List all superhashlists 	[VIEW_SUPERHASHLISTS]
     - Get details about a superhashlist [VIEW_SUPERHASHLISTS]	
     - Create superhashlists [CREATE_SUPERHASHLISTS]	
     - Delete superhashlists [DELETE_SUPERHASHLISTS]
   - File
     - List all files [VIEW_FILES]
     - Get details of a file [VIEW_FILES]	
     - Add new files [CREATE_FILES]	
     - Rename files [MANAGE_FILES]
     - Set if a file is secret or not [MANAGE_FILES]	
     - Delete files [DELETE_FILES]	
     - Change type of files [MANAGE_FILES]
   - Cracker
     - List all crackers [VIEW_CRACKERS]
     - Get details of a cracker [VIEW_CRACKERS]
     - Delete a specific version of a cracker [MANAGE_CRACKERS]	
     - Deleting crackers [DELETE_CRACKERS]	
     - Create new crackers [CREATE_CRACKERS] 	
     - Add new cracker versions [MANAGE_CRACKERS]
     - Update cracker versions [MANAGE_CRACKERS]
   - Config 
     - List available sections in config [VIEW_CONFIGS]
     - List config options of a given section [VIEW_CONFIGS]	
     - Get current value of a config [VIEW_CONFIGS]
     - Change values of configs [MANAGE_CONFIGS]
   - User
     - List all users [VIEW_USERS]
     - Get details of a user [VIEW_USERS]
     - Create new users [CREATE_USERS]	
     - Disable a user account [MANAGE_USERS]	
     - Enable a user account [MANAGE_USERS]
     - Set a user's password [MANAGE_USERS]
     - Change the permission group for a user [MANAGE_USERS]
   - Group
     - List all groups 	[VIEW_GROUPS]
     - Get details of a group [VIEW_GROUPS]	
     - Create new groups [CREATE_GROUPS]
     - Abort all chunks dispatched to agents of this group [MANAGE_AGENTS]
     - Delete groups [DELETE_GROUPS]	
     - Add agents to groups [MANAGE_AGENTS]	
     - Add users to groups [MANAGE_USERS]	
     - Remove agents from groups [MANAGE_AGENTS]	
     - Remove users from groups [MANAGE_USERS]
   - Access
     - List permission groups [VIEW_PERMISSIONS]
     - Get details of a permission group [VIEW_PERMISSIONS]	
     - Create a new permission group [CREATE_PERMISSIONS]	
     - Delete permission groups [DELETE_PERMISSIONS]
     - Update permissions of a group [MANAGE_PERMISSIONS]
   - Account
     - Get account information [MANAGE_USERS]
     - Change email [MANAGE_USERS]
     - Update session length [MANAGE_USERS]	
     - Change password [MANAGE_USERS]