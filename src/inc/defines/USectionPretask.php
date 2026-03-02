<?php

namespace Hashtopolis\inc\defines;

class USectionPretask extends UApi {
  const LIST_PRETASKS  = "listPretasks";
  const GET_PRETASK    = "getPretask";
  const CREATE_PRETASK = "createPretask";
  
  const SET_PRETASK_PRIORITY   = "setPretaskPriority";
  const SET_PRETASK_MAX_AGENTS = "setPretaskMaxAgents";
  const SET_PRETASK_NAME       = "setPretaskName";
  const SET_PRETASK_COLOR      = "setPretaskColor";
  const SET_PRETASK_CHUNKSIZE  = "setPretaskChunksize";
  const SET_PRETASK_CPU_ONLY   = "setPretaskCpuOnly";
  const SET_PRETASK_SMALL      = "setPretaskSmall";
  const DELETE_PRETASK         = "deletePretask";
  
  public function describe($constant) {
    return match ($constant) {
      USectionPretask::LIST_PRETASKS => "List all preconfigured tasks",
      USectionPretask::GET_PRETASK => "Get details about a preconfigured task",
      USectionPretask::CREATE_PRETASK => "Create preconfigured tasks",
      USectionPretask::SET_PRETASK_PRIORITY => "Set preconfigured tasks priorities",
      USectionPretask::SET_PRETASK_NAME => "Rename preconfigured tasks",
      USectionPretask::SET_PRETASK_COLOR => "Set the color of a preconfigured task",
      USectionPretask::SET_PRETASK_CHUNKSIZE => "Change the chunk size for a preconfigured task",
      USectionPretask::SET_PRETASK_CPU_ONLY => "Set if a preconfigured task is CPU only or not",
      USectionPretask::SET_PRETASK_SMALL => "Set if a preconfigured task is small or not",
      USectionPretask::DELETE_PRETASK => "Delete preconfigured tasks",
      USectionPretask::SET_PRETASK_MAX_AGENTS => "Set max agents for a preconfigured task",
      default => "__" . $constant . "__",
    };
  }
}