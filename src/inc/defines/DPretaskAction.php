<?php

namespace Hashtopolis\inc\defines;

class DPretaskAction {
  const SET_PRIORITY      = "setPriority";
  const SET_PRIORITY_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const SET_MAX_AGENTS      = "setMaxAgents";
  const SET_MAX_AGENTS_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const DELETE_PRETASK      = "deletePretask";
  const DELETE_PRETASK_PERM = DAccessControl::CREATE_PRETASK_ACCESS;
  
  const RENAME_PRETASK      = "renamePretask";
  const RENAME_PRETASK_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const SET_TIME      = "setTime";
  const SET_TIME_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const SET_COLOR      = "setColor";
  const SET_COLOR_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const SET_CPU_TASK      = "setCpuTask";
  const SET_CPU_TASK_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const SET_SMALL_TASK      = "setSmallTask";
  const SET_SMALL_TASK_PERM = DAccessControl::MANAGE_PRETASK_ACCESS;
  
  const CREATE_TASK      = "createTask";
  const CREATE_TASK_PERM = DAccessControl::CREATE_PRETASK_ACCESS;
  
  const CHANGE_ATTACK      = "changeAttack";
  const CHANGE_ATTACK_PERM = DAccessControl::CREATE_PRETASK_ACCESS;
}
