<?php

namespace Hashtopolis\inc\defines;

class USectionTask extends UApi {
  const LIST_TASKS    = "listTasks";
  const GET_TASK      = "getTask";
  const LIST_SUBTASKS = "listSubtasks";
  const GET_CHUNK     = "getChunk";
  const GET_CRACKED   = "getCracked";
  
  const CREATE_TASK   = "createTask";
  const RUN_PRETASK   = "runPretask";
  const RUN_SUPERTASK = "runSupertask";
  
  const SET_TASK_PRIORITY          = "setTaskPriority";
  const SET_TASK_TOP_PRIORITY      = "setTaskTopPriority";
  const SET_SUPERTASK_PRIORITY     = "setSupertaskPriority";
  const SET_SUPERTASK_MAX_AGENTS   = "setSupertaskMaxAgents";
  const SET_SUPERTASK_TOP_PRIORITY = "setSupertaskTopPriority";
  const SET_TASK_NAME              = "setTaskName";
  const SET_TASK_COLOR             = "setTaskColor";
  const SET_TASK_CPU_ONLY          = "setTaskCpuOnly";
  const SET_TASK_SMALL             = "setTaskSmall";
  const SET_TASK_MAX_AGENTS        = "setTaskMaxAgents";
  const TASK_UNASSIGN_AGENT        = "taskUnassignAgent";
  const TASK_ASSIGN_AGENT          = "taskAssignAgent";
  const DELETE_TASK                = "deleteTask";
  const PURGE_TASK                 = "purgeTask";
  
  const SET_SUPERTASK_NAME = "setSupertaskName";
  const DELETE_SUPERTASK   = "deleteSupertask";
  
  const ARCHIVE_TASK      = "archiveTask";
  const ARCHIVE_SUPERTASK = "archiveSupertask";
  
  public function describe($constant) {
    return match ($constant) {
      USectionTask::LIST_TASKS => "List all tasks",
      USectionTask::GET_TASK => "Get details of a task",
      USectionTask::LIST_SUBTASKS => "List subtasks of a running supertask",
      USectionTask::GET_CHUNK => "Get details of a chunk",
      USectionTask::CREATE_TASK => "Create a new task",
      USectionTask::RUN_PRETASK => "Run an existing preconfigured task with a hashlist",
      USectionTask::RUN_SUPERTASK => "Run a configured supertask with a hashlist",
      USectionTask::SET_TASK_PRIORITY => "Set the priority of a task",
      USectionTask::SET_TASK_TOP_PRIORITY => "Set task priority to the previous highest plus one hundred",
      USectionTask::SET_SUPERTASK_PRIORITY => "Set the priority of a supertask",
      USectionTask::SET_SUPERTASK_TOP_PRIORITY => "Set supertask priority to the previous highest plus one hundred",
      USectionTask::SET_TASK_NAME => "Rename a task",
      USectionTask::SET_TASK_COLOR => "Set the color of a task",
      USectionTask::SET_TASK_CPU_ONLY => "Set if a task is CPU only or not",
      USectionTask::SET_TASK_SMALL => "Set if a task is small or not",
      USectionTask::TASK_UNASSIGN_AGENT => "Unassign an agent from a task",
      USectionTask::DELETE_TASK => "Delete a task",
      USectionTask::PURGE_TASK => "Purge a task",
      USectionTask::SET_SUPERTASK_NAME => "Set the name of a supertask",
      USectionTask::DELETE_SUPERTASK => "Delete a supertask",
      USectionTask::ARCHIVE_TASK => "Archive tasks",
      USectionTask::ARCHIVE_SUPERTASK => "Archive supertasks",
      USectionTask::GET_CRACKED => "Retrieve all cracked hashes by a task",
      USectionTask::SET_TASK_MAX_AGENTS => "Set max agents for tasks",
      USectionTask::TASK_ASSIGN_AGENT => "Assign agents to a task",
      default => "__" . $constant . "__",
    };
  }
}