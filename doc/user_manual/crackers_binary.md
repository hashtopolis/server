# Binaries

## Crackers

Hashtopolis employs distribution mechanism to ensure that every agent will have the correct cracker binary for the associated task. You can define cracker types (e.g. Hashcat) and for every type you can add as many version as you like. Make sure to keep the download URLs of the binaries up-to-date in case they change over time. The URL has to be absolute.

When Hashtopolis was first developed it was designed to distribute tasks to multiple hashcat agent machines. As the Hashtopolis project progressed we wanted to support more than just hashcat. For example, if someone wants to distribute a specific algorithm using custom cracking software.

Version 0.5.0 now supports multiple cracker binaries which can be used in parallel on different tasks. So for each task, you can select a binary that should be used. The client downloads the specified binary to complete the task.

You are also able to store multiple versions of a binary. This means you can specify the exact version of a binary allowing you to run the version that gives the best performance for the hash type you are running.

You must make sure, that the cracker binary version you want to use is compatible with the Hashtopolis agent binary (e.g. the agent binary is version aware by using specific flags/settings). Please consult the Hashtopolis agent repository README for more information on versioning.

## Preprocessors



## Agent Binaries
