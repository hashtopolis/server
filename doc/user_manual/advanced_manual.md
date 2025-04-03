# Deep Dive Manual

This page provides more details on each functionalities described in the basic workflow. Among other things it provides deeper details and advanced functionalities about the hashlists, tasks, or the management of the agents. 

## Hashlists In-Depth

### Hashlists View
Ordered by ID by default. It reports the hashlists created. A tick is accolated to the name of the hashlists if all the passwords have been retrieved. It shows the number of retrieved passwords as well as the total number of hashes. It allows to import *pre-cracked hashes* or export the retrieved passwords (*see below for more details*). The hashlists can also be archived or deleted.

### Hashlists Details
If you click on a Hashlist, either in the hashlists view, in the Tasks overview or inside a task, it brings you to the corresponding Hashlist details page. 

Apart from the parameters specific to this hashlist (i.e. ID, Access Group, Hashlist name, ...), the page displays some information about the total number of hashes, the number of cracked ones and the number of remaining ones to be recovered. Clicking on one of these three values will open a new window displaying information about the Hashes of the Hashlist as detailed below.

#### Hashes of Hashlist X 
This page list all the hashes from the related hashlist. Filters can be applied to show either the cracked, the uncracked or all the hashes. According to the display filter selected, the Hashes only, the plaintext only or both are displayed. Additionally, the cracking position (**to be defined**) can be displayed next to the cracked ones. Only 1000 hashes can be displayed at a time within a page but the user can navigate through the pages. The number of hashes per page can be configured in *Config > UI* settings.

A HEX converter is present at the bottom of the page to convert any HEX values. This can be useful when the reported password is stored in a HEX format.

#### Actions on the hashlist
Several actions are offered to the user which are detailed below. Note that some of the options are logically not available if no password have been retrieved for the specific hashlist. 

- **Download Report**: **will we still have this function**

- **Generate Wordlist**: This action generates a file listing all the retrieved passwords from this hashlist. The file is automatically stored in the *wordlist* section of the *Files* section. The generated file can be easily retrieved as it got assigned to the latest file ID. The filename is *Wordlist_[Hashlist_ID]_[dd.mm.yyyy]_[hh.mm.ss].txt*. 

- **Export Hashes for pre-crack**: This action generates a file listing all the retrieved passwords from this hashlist associated with the corresponding hash value in the format *[hash]:[plaintext]*. The file is automatically stored in the *wordlist* section of the *Files* section. The generated file can be easily retrieved as it got assigned to the latest file ID. The filename is *Pre-cracked_[Hashlist_ID]_[dd-mm-yyyy]_[hh-mm-ss].txt*. 

- **Export Left Hashes**: This action generates a file listing all the hashes for which no password have been retrieved at the moment of the file creation. The file is automatically stored in the *wordlist* section of the *Files* section. The generated file can be easily retrieved as it got assigned to the latest file ID. The filename is *Leftlist_[Hashlist_ID]_[dd-mm-yyyy]_[hh-mm-ss].txt*. 

- **Import pre-cracked Hashes**: This action opens a new page in which the user can upload pre-cracked hashes for the related hashlist. A pre-crack is supposed to be a hash contained in the hashlist associated with a plaintext in the format *[hash](:[salt]):[plaintext]*. Such data can be imported in different ways: *"Paste, Upload, Import, URL downlaod"* such as the option to import the hashes during a hashlist creation (**see XXX**). In case of salted password, the field separator must be indicated, ':' being the default one. When validating by pressing the *Pre-crack hashes* button, the back-end will check if the imported data contains hash values from the targeted hashlist and integrate the plaintext value accordingly. If the option *Overwrite already cracked hashes* is selected, existing retrieved passworda will be overwritten by the new imported ones in case of conflict. The front-end is then reporting to the user how many hashes have been considered as well as how many entries have been updated.  

Pre-cracked management is useful to share results between different instances of hashtopolis. This is especially relevant for salted hashlits as each new recovered plaintext is improving the efficiency of the attack is there is no more hashes associated with the same salt value. 

#### Tasks overview and creation
At the bottom of the page there are three subsections related to task for this hashlist.

- **Tasks cracking this hashlists**: This section lists all the tasks that are related to this hashlist. Note that supertasks will not appear here (**is this something we would like in the future... let see how it will be handled within project**). The details displayed are defined in the *Show Tasks* section as they are the same. Note that not all the infos present in the *Show Tasks* page are displayed here. 

- **Create pre-configured tasks**: this section lists all the existing pre-configured tasks. The user can select a set of pre-configured tasks and create the corresponding task for the current hashlist. See the section on *pre-configured tasks* for more detail on this.

- **Create Supertask**: Similarly to the pre-configured tasks, this section lists all the existing supertask that the user can create for the current hashlist. See the section *supertask* for more details on this.


### Super Hashlists

> [!NOTE]
> Should we include pictures in this section that is quite obvious

A Super Hashlist is a virtual hashlist that combines multiple classic hashlists without duplicating data at the database level. It allows you to run a single cracking task on multiple hashlists at once. Since the hashes are only linked, not merged, storage is optimized, and updates to individual hashlists are immediately reflected. This is especially useful when working with related datasets that require the same attack strategies, saving time and resources while keeping everything well-organized.

#### New SuperHashlist

The page displays all the existing hashlists in the database. To create a new superhashlist, you need to do the following:
- select all the hashlists you want to integrate in the superhashlist;
- scroll down to the bottom of the page, and enter a name for the superhashlist in the corresponding field;
- Click on the *create* button.

You can select all the hashlists at once by clicking on the button *select all*. However, keep in mind that a superhashlist should only contains hash of the same type to work. **We should probably introduce a check at the creation of the super list, and also allow to search or filters to only display those of a specific type to select all in a controlled manner** 

#### Overview

Once you have created a superhashlist or if you open the *SuperHashlist* menu, the overview page of SuperHaslist is open. Such page diplays all the information about the superhashlists created so far. It is very similar to the hashlist overview page, the only difference being that you cannot archive a superhashlist.

If you click on a superhashlist, the superhashlist detail page will be open. Again this page is very similar to the hashlist page. The only difference is that it contains the following details about the hashlist(s) contained in the superhashlist:
- ID of each hashlist
- Name of each hashlist
- Cracked percentage of each hashlist


### Search Hash

This page displays a free text zone in which the user can type multiple hashes, one per line, to check if they are present in the database or not. The hashes do not need to be of the same type. Furthermore, the hash does not need to be complete.

The result will display all the hashes that correspond to the given entry/ies. It will display one block for each entry specifying either:
- NOT FOUND: if the hash is present in no entries;
- A list of all the hashes that contains the given entry, specifying in which hashlist(s) they are contained and the cleartext password if they have been cracked already.

<figure markdown="span">
    ![screenshot_import_file](https://upload.wikimedia.org/wikipedia/commons/8/80/Comingsoon.png?20120228065200){ width="300" }
</figure>

### Show Crack

This page displays all the cracked passwords that have been retrieved and that are stored in the database. It shows the following fields.
- **Time Found**: Indicates when the password has been retrieved
- **Plaintext**: Password that has been retrieved
- **Hash**: Hash for which the password was retrieved
- **Hashlist**: ID of the hashlist that contains this hash
- **Agent**: ID of the agent that has retrieved the password
- **Task**: ID of the task that has retrieved the password
- **Chunk**: ID of the chunk that has retrieved the password 
- **Type**: Hashmode related to the hash
- **Salt**: Salt associated to the hash if relevant.

1.000 entries are displayed per page and there is a search functionalities that is applied on all the field of the table.

## Tasks in Depth

### Advanced option during task creation
Several options were not covered in the basic workflow related to the creation of a task. The remaining options are described below. 

- **Set as preprocessor task**: Such option allows the usage of a preprocessor. By default hashtopolis is installed with a single preprocessor, namely [*Prince*](https://github.com/hashcat/princeprocessor). $Additional preprocessors can be defined in the *Config* page (see [XXX]() for more details). The command that should be used for this preprocessor must be defined in the free text zone below. A task defined with a preprocessor will result in the execution of the preprocessor redirecting the output as stdin for the command line defined above in the same task. This allows the usage of "external" candidate generator such as Prince.

- **Skip a given keyspace at the beginning of the task**: Any value X inserted here will result in ignoring the first X values of the keyspace as it would be done with the flag "-s X" inserted in the command line. The rest of the keyspace will be processed normally. This can be useful to ignore a portion of the keyspace that has been already explored during a different process, for example on a local machine.

- **Use Static Chunking**: If this option is enabled, the regular division in chunk (based on the chunktime and the benchmark of the agent) will be ignored. An alternative division is used depending of the choice made.
  - *Fixed chunk size*: Each chunk will have a portion of the keyspace where the length is the value assigned (an integer) in the associated field. The last chunk of the task may be smaller than the defined length for completion.
  - *Fixed number of chunks*: The keyspace will be divided in as many chunks as the number specified in the associated field.

- Enforce Piping (to apply rules before reject): **will be removed soon** and is therefore not explained here.

### Preconfigured tasks (including from existing task)
A preconfigured tasks is a basic template for a task that is not assigned yet to a hashlist. This is particularly useful to predefine task(s) that are often use such as generic mask attack or commonly used dictionary attack. A preconfigured task can later be assigned to a hashlist avoiding the user to redefine the same task every time. This section gives more details about this topic.

When the user goes to the menu *New Preconfigured TasksThe properties of a pre-configured tasks are a subset of those of a regular task and are therefore not re-defined here. THe reader can refer to the dedicated section for reference (**put a ref here**).  

Once the pre-configured task is created, the user is brought to the *Preconfigured tasks* page that lists all the existing preconfigured tasks. Here the user can set the default priority as well as the maximum number of agents for this preconfigured tasks (**NOTE I believe these two options should already appear in the template of a preconfigured task**). Those values will be used as defaults upon creation of a task from this template. 

In addition to the possibility to delete a preconfigured task, two additional actions are offered to the user and are defined below.

- **Copy to task**: This action opens a *new task* creaction page where all the pre-defined values of the preconfigured task are already prefilled. The user must select the hashlist for which the task should be created. All the other values can be modified by the user if needed. Note that there is the possibility to create a task from a pretask for a specific hashlist directly from the corresponding *Hashlist details* page.  

- **Copy to Pretask**: This action open a *New Preconfigured Tasks* Page where all the value of the corresponding pretask are duplicated. The user can then modify those values to create a new Preconfigured tasks. This is particularly useful if one want to slightly modify an existing preconfigured task, for example by adding a new placeholder in a mask or changing a rule file in a dictionnary attack. Note that while it is possible to create a perfect duplicate of a pretask there is no added-value in doing-so. 

#### Creating a preconfigured task from a task
In the *Show Tasks* page, there is an action offered for each task, namely **Copy to Pretask**. This option will create a template from the corresponding task by extracting all the required information. The default name extracted will be the current one from the task. The user can modify at will those values and finally create the preconfigured task from it. This is useful in case you have defined an attack that you want to store for future reuse.   


### Super Task

A SuperTask is a group of pre-configured tasks. A supertask can be directly applied to a hashlist resulting in the creation of all the underlying pre-configured tasks applied to this hashlist. 

> [!CAUTION] 
> A supertask cannot be applied to a superhashlist. 

This is particularly useful when applying the same attack strategy to different hashlists.

#### New SuperTask

Similarly to the superhashlists, this page will display all the existing pre-configured tasks. The user needs to select all the pre-configured tasks that should be included in the supertask, give it a name, and press the *create supertask* button. 

#### Overview
Once a new supertask is created, or if you open the *SuperTask* menu, the overview page of SuperTask is open. It displays the ID of all the superhashlists and their names. Three options are proposed.

- **Apply to Hashlist**: This option open a new page in which you can select the hashlist to which you want to apply the set of pre-configured tasks as well as the binary to use. 
- **Show/Hide**: This option unfolds the supertask and displays the included preconfigured task(s) with the following information/options.
  - **ID**: ID of the pre-configured task
  - **Name**: Name of the pre-configured task. Clicking on it opens the corresponding pre-configured task page. 
  - **SubTask Priority**: define the order in which the pre-configured tasks will be executed when an agent is assigned to the supertask. Similarly to tasks, priority is given to the highest number.
  - **SubTask Max Agents**: similarly to tasks, specifies the maximum agents that can be assigned to the task.
  - **Remove**: remove the pre-configured task from the supertask. Note that the pre-configured task is only removed from the supertask but not deleted from the system except if the related pre-configured task was generated via the *Import Super Task* functionality (see below for more details).

#### SuperTask in the *ShowTasks* Menu

Supertask are not displayed as regular tasks in the *Show Task* menu as displayed in the picture below. 


<figure markdown="span">
    ![screenshot_import_file](https://upload.wikimedia.org/wikipedia/commons/8/80/Comingsoon.png?20120228065200){ width="300" }
</figure>

The same information than those of a task are displayed. The *copy to Pretask* and *copy to task* options are not available. There is instead an information button which open a pop-up window displaying the list of subtasks of the supertask. This window is identical to the ShowTasks page apart that only the subtasks of the supertask are diplayed in it as shown in the figure below. 

<figure markdown="span">
    ![screenshot_import_file](https://upload.wikimedia.org/wikipedia/commons/8/80/Comingsoon.png?20120228065200){ width="300" }
</figure>

### Import Super Task

The Import Super Task menu offers functionalities to create SuperTasks and the related pre-configured task in an easy manner. There exist two different ways to create those supertasks, *Masks* and *Wordlist/Rule bulk*.

#### Masks

This functionality allows the user to create a supertask from a mask file or a set of masks. It is a good alternative to replace the --increment option of hashcat that cannot be used in hashtopolis.

- **Name**: Defines the name that will be given at the created SuperTask
- **Are small tasks**: If this parameter is set to yes, a single agent can be assigned to the tasks that will be created when the resulting supertask is apply to a hashlist. This is relevant for small tasks or to assign the full keyspace in a single chunk to an agent. Note that this is **NOT** equivalent to define the *Maximum number of agents* to 1. Indeed, in this latter case, the task will still be divided in chunks according to the *chunk size* parameter. The parameter is set to No by default.
- **Max Agents**: Specify the maximum agents that can be assigned to the tasks that will be created when the resulting supertask is apply to a hashlist. If this amount is reached, future available agents will be assigned to the next task available with a lower priority even if the all the chunks of the task have been distributed. The default value of 0 means that there is no maximum and therefore, all available agents are assigned to this tasks until all the chunks have been distributed. This functionality is helpful to only use a portion of the cluster for a specific task, and therefore allowing to split the workers on different tasks.
- **Are CPU tasks**: If this parameter is set to yes, only the agents that are declared as CPU only can be assigned to this task. More details can be found in **ref to advanced agents**. The parameter is set to No by default. 
- **Use Optimized flag (-O)**: If this parameter is set to Yes, the optimized flag -O will be added to the command line of all the sub-tasks of this supertask. The -O flag in Hashcat enables the use of optimized kernels for better performance. This improves cracking speed yet it has an impact on some aspects such as limiting the maximum length of the candidates to be tested, e.g. from 256 to 55 in the case of MD5 or from 256 to 27 for NTLM. 
- **Benchmark Type**: Select which benchmarking type should be used for the subtasks of the supertask. It is recommended to use the default *Speed Test* for mask attack. Only in few cases, such as tasks with big salted lists, the *Runtime* may be used.
- **Cracker Binary which is used to run this task**: This parameter specifies the binary type to use for this specific task. 
- **Insert Masks**: The mask lines that will generate the subtask should be written here. The expected format is the one of a *.hcmask" file for hashcat. In a nutshell, there should be one mask per line following the format **[?1,][?2,][?3,][?4,]mask**, where [?x] specifies the optional charset that can be used in the mask. More details can be found [here](https://hashcat.net/wiki/doku.php?id=mask_attack).

A subtask will be created for each line of the the *Insert masks* text zone and they will be grouped in a supertask. The subtasks are pre-configured task from the database point of view, however they are not diplayed in the *Preconfigured Tasks* page. The subtasks that will be generated in this supertasks will be ordered accordingly to their order in the *Insert masks* text zone giving the highest priority to the first line.

> [!NOTE]
> Note that the options above will be applied to all the pre-configured tasks that will be created during the generation of the supertaks from this import.

#### Wordlist/Rule bulk

The wordlist/Rule bulk functionality allows to create a set of subtasks for an iteration of several files selected by the user. It allows for example to create an attack strategy of a succession of wordlists to be applied one after the other or to use different rule files with a single wordlist. 

Most of the options are identical to those of the Mask supertask creation. The main difference is that the *Insert Masks* is obviously not present and is replaced by the *Base Command* option. In this text zone the user is expected to type the command line that should be iterated. Similarly to the *New Task* page, *#HL#* is filled in by default in the command line. It is a placeholder for the hashlist and will be replaced automatically at execution time by the agent with the correct path to the hashlist file. The user then need to select the Rules and Wordlist to use in the supertask. When selecting a file as a base - wether a Rule file, a wordlist or other - the file is immediately added at the command line like in a regular task creation. 

Multiple files are expected to be selected as "Iterate". They should be of the same type (rules/wordlists/other), yet this functionality allows to select different type of files. The placeholder **FILE** should be manually placed by the user. During creation of the supertask, one subtask is created for each file selected as iterate replacing the FILE placeholder by one of the "Iterate File". 

Similarly to a regular task, any hashcat parameter can be added to the command line. For example, if the user wants that the Optimized Kernel option (-O) is used, it should be added. That is the reason why this option is not offered to the user among the options contrary to the *Import Masks*.


**MAKE AN EXAMPLE WITH SOME FIGURES**

> [!CAUTION]
> If the iteration is done over rule files, the flag **-r** will not be added when FILE is replaced by the rule file. It should therefore be added in the command line as displayed in the example above. 

## New Binary

- New Hashmode
