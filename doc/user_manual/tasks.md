# Tasks

Tasks are the core of Hashtopolis operations — they define how password cracking jobs are executed. Each task specifies an attack configuration, including the hashlist to use, files like wordlists or rules, and the command line for Hashcat. This page explains how to create, configure, and manage tasks.

## Task Creation

To create a new task, click on the button "+ New Task" in the page *Tasks > Show Task*. You will get the following window in which you can create a new task. Some of the fields are mandatory, some others are filled with default values. 

### Basic Parameters

1. **Name**: provide a name for the task you want to create. This is how the task will be referenced with during the monitoring phase (see **link**) therefore it should be relatively explicit to facilitate its monitoring.

2. **Hashlist**: select the hashlist you want to target in this specific task. Tasks are ordered by their IDs. Supertasks (**see ref to advanced usage**) are at the bottom of the list ordered by their respective IDs.

3. **Command Line**: provide in this field the attack command that will be executed by the agent on the targeted hashlist using the selected binary (see below). Note that *#HL#* is filled in by default in the command line. It is a placeholder for the hashlist and will be replaced automatically at execution time by the agent with the correct path to the hashlist file. Therefore you should not remove it nor include the filename for the hashlist. If for example you want to perform a mask attack of 6 digits, the command line would look like ```#HL# -a3 ?d?d?d?d?d?d```.
In case you want to perform a dictionary attack with rules, you have to select the corresponding files in the right table. If it is a wordlist, select it within the right column corresponding to T/Task. The Preprocessor part is explained in the advanced section. If it is a rule file, select first the rule tab (see **ref to the picture**) and then select the desired rule file. Note that upon selection of a rule file, the name of the file is included in the command line and automatically include the required '-r' flag.

4. **Priority**: Assign a priority number to the task. The expected value has to be an integer. Agents will be assigned to tasks in decreasing order of priority. A task with a priority 0 will not be processed even if agents are available - except if an agent is manually assigned to it and no other task with higher priority that the agent may join are existing. Default value is 0.

5. **Maximum number of agents**: Specify the maximum agents that can be assigned to the task. If this amount is reached, future available agents will be assigned to the next task available with a lower priority even if not all the chunks of the task have been distributed. The default value of 0 means that there is no maximum and therefore, all available agents are assigned to this tasks until all the chunks have been distributed. This functionality is helpful to only use a portion of the cluster for a specific task, and therefore allowing to split the workers on different tasks.

6. **Task Notes** - *optional*: This field allows the user to indicate some details about the tasks, the command line or any other details the user can find relevant. 

7. **Color** - *optional*: Can assign a color in a Hex color code format #RRGGBB. Default value is white #FFFFFF. This can be useful in the monitoring part to visually recognise a task or a set of tasks.

### Advanced Parameters

Several options were not covered in the basic workflow related to the creation of a task. The remaining options are described below. 

8. **Chunk size**: This parameter defines the duration that each agent should take to process a chunk<span title="A portion of the keyspace assigned to an agent for cracking. If an agent fails or a chunk times out, it will be reassigned.">ℹ️</span> for this task. The default value is defined in the [Settings](/user_manual/settings_and_configuration/#benchmark-chunk).

9. **Status timer**: Defines the frequency with which each agent report its progress for this task to the server. The default value is defined in the [Settings](/user_manual/settings_and_configuration/#activity-registration).

10. **Benchmark Type**: Select which benchmarking type should be used for this task. In most of the cases, it is recommended to use the default *Speed Test*. Only in few cases, such as tasks with big salted lists, the *Runtime* may be used.

11. **Task is CPU only**: If this flag is enabled, only the agents that are declared as CPU only can be assigned to this task. More details can be found in the [agent overview section](/user_manual/agents/#agent-overview). 

12. **Task is small**: If this flag is enabled, a single agent can be assigned to this task. This is relevant for small tasks or to assign the full keyspace in a single chunk to an agent. Note that this is **NOT** equivalent to define the *Maximum number of agents* to 1. Indeed, in this latter case, the task will still be divided in chunks according to the *chunk size* parameter. The flag is disabled by default.

13. **Binary type to run the task**: This pair of parameters specify the binary type as well as the version of the binary to use for this specific task. It will by default use the latest uploaded version of the first binary type defined in the *Binaries* section (**see binaries for more details**).  

14. **Set as preprocessor task**: Such option allows the usage of a preprocessor. By default hashtopolis is installed with a single preprocessor, namely [*Prince*](https://github.com/hashcat/princeprocessor). Additional preprocessors can be defined in the [*preprocessors*](/user_manual/crackers_binary/#preprocessors) page. The command that should be used for this preprocessor must be defined in the free text zone below. A task define with a preprocessor will result in the execution of the preprocessor redirecting the output as stdin for the command line defined above in the same task. This allows the usage of "external" candidate generator such as Prince.  

15. **Skip a given keyspace at the beginning of the task**: Any value X inserted here will result in ignoring the first X values of the keyspace as it would be done with the flag "-s X" inserted in the command line. The rest of the keyspace will be processed normally. This can be useful to ignore a portion of the keyspace that has been already explored during a different process, for example on a local machine.

16. **Use Static Chunking**: If this option is enabled, the regular division in chunk (based on the chunktime and the benchmark of the agent) will be ignored. An alternative division is used depending of the choice made.
  - *Fixed chunk size*: Each chunk will have a portion of the keyspace where the length is the value assigned (an integer) in the associated field. The last chunk of the task may be smaller than the defined length for completion.
  - *Fixed number of chunks*: The keyspace will be divided in as many chunks as the number specified in the associated field.

- Enforce Piping (to apply rules before reject): **will be removed soon** and is therefore not explained here.

## Preconfigured tasks

A preconfigured tasks is a basic template for a task that is not assigned yet to a hashlist. This is particularly useful to predefine task(s) that are often use such as generic mask attack or commonly used dictionnary attack. A preconfigured task can later be assigned to a hashlist avoiding the user to redefine the same task every time. This section gives more details about this topic.

When the user creates a *New Preconfigured Tasks*, the fields to create one are a subset of those of a regular task and are therefore not re-defined here. The reader can refer to the above section for reference.  

Once the pre-configured task is created, the user is brought to the *Preconfigured tasks* page that lists all the existing preconfigured tasks. Here the user can set the default priority as well as the maximum number of agents for this preconfigured tasks (**NOTE I believe these two options should already appear in the template of a preconfigured task**). Those values will be used as defaults upon creation of a task from this template. 

In addition to the possibility to delete a preconfigured task, two additional actions are offered to the user and are defined below.

- **Copy to task**: This action opens a *new task* creaction page where all the pre-defined values of the preconfigured task are already prefilled. The user must select the hashlist for which the task should be created. All the other values can be modified by the user if needed. Note that there is the possibility to create a task from a pretask for a specific hashlist directly from the corresponding *Hashlist details* page.  

- **Copy to Pretask**: This action open a *New Preconfigured Tasks* Page where all the value of the corresponding pretask are duplicated. The user can then modify those values to create a new Preconfigured tasks. This is particularly useful if one want to slightly modify an existing preconfigured task, for example by adding a new placeholder in a mask or changing a rule file in a dictionnary attack. Note that while it is possible to create a perfect duplicate of a pretask there is no added-value in doing-so. 

#### Creating a preconfigured task from a task
In the *Show Tasks* page, there is an action offered for each task, namely **Copy to Pretask**. This option will create a template from the corresponding task by extracting all the required information. The default name extracted will be the current one from the task. The user can modify at will those values and finally create the preconfigured task from it. This is useful in case you have defined an attack that you want to store for future reuse.   


## Super Task

A SuperTask is a group of pre-configured tasks. A supertask can be directly applied to a hashlist resulting in the creation of all the underlying pre-configured tasks applied to this hashlist. 

> [!CAUTION] 
> A supertask cannot be applied to a superhashlist. 

This is particularly useful when applying the same attack strategy to different hashlists.

### New SuperTask

Similarly to the superhashlists, this page will display all the existing pre-configured tasks. The user needs to select all the pre-configured tasks that should be included in the supertask, give it a name, and press the *create supertask* button. 

### Overview
Once a new supertask is created, or if you open the *SuperTask* menu, the overview page of SuperTask is open. It displays the ID of all the superhashlists and their names. Three options are proposed.

- **Apply to Hashlist**: This option open a new page in which you can select the hashlist to which you want to apply the set of pre-configured tasks as well as the binary to use. 
- **Show/Hide**: This option unfolds the supertask and displays the included preconfigured task(s) with the following information/options.
  - **ID**: ID of the pre-configured task
  - **Name**: Name of the pre-configured task. Clicking on it opens the corresponding pre-configured task page. 
  - **SubTask Priority**: define the order in which the pre-configured tasks will be executed when an agent is assigned to the supertask. Similarly to tasks, priority is given to the highest number.
  - **SubTask Max Agents**: similarly to tasks, specifies the maximum agents that can be assigned to the task.
  - **Remove**: remove the pre-configured task from the supertask. Note that the pre-configured task is only remove from the supertask but not deleted from the system except if the related pre-configured task was generated via the *Import Super Task* functionality (see below for more details).

### SuperTask in the *ShowTasks* Menu

Supertask are not displayed as regular tasks in the *Show Task* menu as displayed in the picture below. 


<figure markdown="span">
    ![screenshot_showtask_supertask](/assets/images/supertasks_showtasks.png)
</figure>

The same information than those of a task are displayed. The *copy to Pretask* and *copy to task* options are not available. There is instead an information button which open a pop-up window displaying the list of subtasks of the supertask. This window is identical to the ShowTasks page apart that only the subtasks of the supertask are displayed in it as shown in the figure below. 

<figure markdown="span">
    ![screenshot_import_file](/assets/images/)
</figure>

## Import Super Task

The Import Super Task menu offers functionalities to create SuperTasks and the related pre-configured task in an easy manner. There exist two different ways to create those supertasks, *Masks* and *Wordlist/Rule bulk*.

### Masks

This functionality allows the user to create a supertask from a mask file or a set of masks. It is a good alternative to replace the --increment option of hashcat that cannot be use in hashtopolis.

- **Name**: Defines the name that will be given at the created SuperTask
- **Are small tasks**: If this parameter is set to yes, a single agent can be assigned to the tasks that will be created when the resulting supertask is apply to a hashlist. This is relevant for small tasks or to assign the full keyspace in a single chunk to an agent. Note that this is **NOT** equivalent to define the *Maximum number of agents* to 1. Indeed, in this latter case, the task will still be divided in chunks according to the *chunk size* parameter. The parameter is set to No by default.
- **Max Agents**: Specify the maximum agents that can be assigned to the tasks that will be created when the resulting supertask is apply to a hashlist. If this amount is reached, future available agents will be assigned to the next task available with a lower priority even if the all the chunks of the task have been distributed. The default value of 0 means that there is no maximum and therefore, all available agents are assigned to this tasks until all the chunks have been distributed. This functionality is helpful to only use a portion of the cluster for a specific task, and therefore allowing to split the workers on different tasks.
- **Are CPU tasks**: If this parameter is set to yes, only the agents that are declared as CPU only can be assigned to this task. More details can be found in the [agent section](/user_manual/agents/) of this manual. The parameter is set to No by default. 
- **Use Optimized flag (-O)**: If this parameter is set to Yes, the optimized flag -O will be added to the command line of all the sub-tasks of this supertask. The -O flag in Hashcat enables the use of optimized kernels for better performance. This improves cracking speed yet it has an impact on some aspects such as limiting the maximum length of the candidates to be tested, e.g. from 256 to 55 in the case of MD5 or from 256 to 27 for NTLM. 
- **Benchmark Type**: Select which benchmarking type should be used for the subtasks of the supertask. It is recommended to use the default *Speed Test* for mask attack. Only in few cases, such as tasks with big salted lists, the *Runtime* may be used.
- **Cracker Binary which is used to run this task**: This parameter specifies the binary type to use for this specific task. 
- **Insert Masks**: The mask lines that will generate the subtask should be written here. The expected format is the one of a *.hcmask" file for hashcat. In a nutshell, there should be one mask per line following the format **[?1,][?2,][?3,][?4,]mask**, where [?x] specifies the optional charset that can be used in the mask. More details can be found [here](https://hashcat.net/wiki/doku.php?id=mask_attack).

A subtask will be created for each line of the the *Insert masks* text zone and they will be grouped in a supertask. The subtasks are pre-configured task from the database point of view, however they are not diplayed in the *Preconfigured Tasks* page. The subtasks that will be generated in this supertasks will be ordered accordingly to their order in the *Insert masks* text zone giving the highest priority to the first line.

> [!NOTE]
> Note that the options above will be applied to all the pre-configured tasks that will be created during the generation of the supertaks from this import.

### Wordlist/Rule bulk

The wordlist/Rule bulk functionality allows to create a set of subtasks for an iteration of several files selected by the user. It allows for example to create an attack strategy of a succession of wordlists to be applied one after the other or to use different rule files with a single wordlist. 

Most of the options are identical to those of the Mask supertask creation. The main difference is that the *Insert Masks* is obviously not present and is replaced by the *Base Command* option. In this text zone the user is expected to type the command line that should be iterated. Similarly to the *New Task* page, *#HL#* is filled in by default in the command line. It is a placeholder for the hashlist and will be replaced automatically at execution time by the agent with the correct path to the hashlist file. The user then need to select the Rules and Wordlist to use in the supertask. When selecting a file as a base - wether a Rule file, a wordlist or other - the file is immediately added at the command line like in a regular task creation. 

Multiple files are expected to be selected as "Iterate". They should be of the same type (rules/wordlists/other), yet this functionality allows to select different type of files. The placeholder **FILE** should be manually placed by the user. During creation of the supertask, one subtask is created for each file selected as iterate replacing the FILE placeholder by one of the "Iterate File". 

Similarly to a regular task, any hashcat parameter can be added to the command line. For example, if the user wants that the Optimized Kernel option (-O) is used, it should be added. That is the reason why this option is not offered to the user among the options contrary to the *Import Masks*.


**MAKE AN EXAMPLE WITH SOME FIGURES**

> [!CAUTION]
> If the iteration is done over rule files, the flag **-r** will not be added when FILE is replaced by the rule file. It should therefore be added in the command line as displayed in the example above. 
