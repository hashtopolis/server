# Deep Dive Manual

This page provides more details on each functionalities described in the basic workflow. Among other things it provides deeper details and advanced functionalities about the hashlists, tasks, or the management of the agents. 
## Hashlists In-Depth

### Hashlists View
Ordered by ID by default. It reports the hashlists created. A tick is accolated to the name of the hashlists if all the passwords have been retrieved. It shows the number of retrieved passwords as well as the total number of hashes. It allows to import *pre-cracked hashes* or export the retrieved passwords (*see below for more details*). THe hashlists can also be archived or deleted.

### Hashlists Details
If you click on a Hashlist, either in the hashlists view, in the Tasks overview or inside a task, it brings you to the corresponding Hashlist details page. 

Appart from the parameters specific to this hashlist (i.e. ID, Access Group, Hashlist name, ...), the page displays some information about the total number of hashes, the number of cracked ones and the number of remaining ones to be recovered. Clicking on one of these three values will open a new window displaying information about the Hashes of the Hashlist as detailed below.

#### Hashes on the Hashlist
This page list all the hashes from the related hashlist. Filters can be applied to show either the cracked, the uncracked or all the hashes. According to the display filter selected, the Hashes only, the plaintext only or both are displayed. Additionnally, the cracking position (**to be defined**) can be displayed next to the cracked ones. Only 1000 hashes can be displayed at a time within a page but the user can navigate through the pages. The number of hashes per page can be configured in *Config > UI* settings.

A HEX converter is present at the bottom of the page to convert any HEX values. This can be useful when the reported password is stored in a HEX format.

#### Actions on the hashlist
Several actions are offered to the user which are detailed below.

- **Download Report**: **will we still have this function**

- **Generate Wordlist**:

- **Export Hashes for pre-crack**:

- **Export Left Hashes**:

- **Import pre-cracked Hashes**:

#### Tasks overview and creation
At the bottom of the page there are three subsections related to task for this hashlist.

- **Tasks cracking this hashlists**: This section lists all the tasks that are related to this hashlist. Note that supertasks will not appear here (**is this something we would like in the future... let see how it will be handled within project**). The details displayed are defined in the *Show Tasks* section as they are the same. Note that not all the infos present in the *Show Tasks* page are displayed here. 

- **Create pre-configured tasks**: this section lists all the existing pre-configured tasks. The user can select a set of pre-configured tasks and create the corresponding task for the current hashlist. See the section on *pre-configured tasks* for more detail on this.

- **Create Supertask**: Similarly to the pre-configured tasks, this section lists all the existing supertask that the user can create for the current hashlist. See the section *supertak* for more details on this.


### Super Hashlists

#### Creation

#### Overview


### Search Hash

### Show Crack



## Tasks in Depth

### Advanced option during task creation
Several options were not covered in the basic workflow related to the creation of a task. The remaining options are described below. 

- Set as preprocessor task

- Skip a given keyspace at the beginning of the task

- Use Static Chunking

- Enforce Piping (to apply rules before reject): **will be removed soon***

### Preconfigured tasks (including from existing task)
A preconfigured tasks is a basic template for a task that is not assigned yet to a hashlist. This is particularly useful to predefine task(s) that are often use such as generic mask attack or commonly used dictionnary attack. A preconfigured task can later be assigned to a hashlist avoiding the user to redefine the same task every time. This section gives more details about this topic.

When the user goes to the menu *New Preconfigured TasksThe properties of a pre-configured tasks are a subset of those of a regular task and are therefore not re-defined here. THe reader can refer to the dedicated section for reference (**put a ref here**).  

Once the pre-configured task is created, the user is brought to the *Preconfigured tasks* page that lists all the existing preconfigured tasks. Here the user can set the default priority as well as the max number of agents for this preconfigured tasks (**NOTE I believe these two options should already appear in the template of a preconfigured task**). Those values will be used as defaults upon creation of a task from this template. 

In addition to the possibility to delete a preconfigured task, two additional actions are offered to the user and are defined below.

- **Copy to task**: This action opens a *new task* creaction page where all the pre-defined values of the preconfigured task are already prefilled. The user must select the hashlist for which the task should be created. All the other values can be modified by the user if needed. Note that there is the possibility to create a task from a pretask for a specific hashlist directly from the corresponding *Hashlist details* page.  

- **Copy to Pretask**: This action open a *New Preconfigured Tasks* Page where all the value of the corresponding pretask are duplicated. The user can then modify those values to create a new Preconfigured tasks. This is particularly useful if one want to slightly modify an existing preconfigured task, for example by adding a new placeholder in a mask or changing a rule file in a dictionnary attack. Note that while it is possible to create a perfect duplicate of a pretask there is no added-value in doing-so. 

#### Creating a preconfigured task from a task
In the *Show Tasks* page, there is an action offered for each task, namely **Copy to Pretask**. This option will create a template from the corresponding task by extracting all the required information. The default name extracted will be the current one from the task. The user can modify at will those values and finally create the preconfigured task from it. This is useful in case you have defined an attack that you want to store for future reuse.   


### Super Task


### Import Super task

## New Binary

- New Hashmode
