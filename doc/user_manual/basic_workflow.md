# Basic Workflow

In this manual and in Hashtopolis itself, we use several terms. So let's make them clear:

Agent: A computer running a Hashtopolis client and Hashcat doing the cracking itself.
Hashlist: A list of hashes saved in the database. Hashlist can be TEXT, HCCAPX or BINARY with most hashlists being the first category.
Task: A specific attack. Every task has a command line defining how Hashcat will be executed. Files can be assigned to a task (wordlists, rules, ...).
Supertask: A grouped number of subtasks. This task itself is not really a task, it just puts all the subtasks together so they can be viewed as one "whole" task.
Subtask: Is inside a supertask. It has the same properties like a normal task, except that it's priority is only relevant inside the supertask.
Keyspace: Every task has a predefined key space which says how big set of keys will be searched. Important note: They keyspace shown on the UI is NOT indicative of the ACTUAL keyspace for a particular attack. To find out more about how the keyspace value is derived please see the hashcat wiki.
Chunk: A chunk is a part of a keyspace assigned to a specific agent. If a chunk times out, it (or its part) will be reassigned to next free agent.
Access Management: This manages the access to functions and actions. It is used to apply a fine-grain access management.
Groups: Used to separate hashlists/tasks/agents from each other if needed. It can be used to have separate independent user groups not interfering with each other.


This page describes the basic workflow required to launch your first cracking task.
It provides a high-level overview of the key steps needed to get started:

- Uploading hash lists;
- Uploading files;
- Creating a task;
- Monitoring the task and the results.
Each of these steps is covered in more detail in the advanced section **link**, but for now, this guide will walk you through the essentials to get your first task up and running. 

> [!NOTE]
> It is assumed that you have already access to a fully functional hashtopolis installation with at least one agent up and running. If it is not the case, please refer to the installation section **link**.



Do and Don't
- multiple wordlists do not work
- increment do not work
- --slow-candidates may not be a good idea
- others?