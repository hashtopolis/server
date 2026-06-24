<?php

namespace Hashtopolis\inc\defines;

class USectionAgent extends UApi {
  const CREATE_VOUCHER = "createVoucher";
  const GET_BINARIES   = "getBinaries";
  const LIST_VOUCHERS  = "listVouchers";
  const DELETE_VOUCHER = "deleteVoucher";
  
  const LIST_AGENTS      = "listAgents";
  const GET              = "get";
  const SET_ACTIVE       = "setActive";
  const CHANGE_OWNER     = "changeOwner";
  const SET_NAME         = "setName";
  const SET_CPU_ONLY     = "setCpuOnly";
  const SET_EXTRA_PARAMS = "setExtraParams";
  const SET_ERROR_FLAG   = "setErrorFlag";
  const SET_TRUSTED      = "setTrusted";
  const DELETE_AGENT     = "deleteAgent";
  
  public function describe($constant) {
    return match ($constant) {
      USectionAgent::CREATE_VOUCHER => "Creating new vouchers",
      USectionAgent::GET_BINARIES => "Get a list of available agent binaries",
      USectionAgent::LIST_VOUCHERS => "List existing vouchers",
      USectionAgent::DELETE_VOUCHER => "Delete an existing voucher",
      USectionAgent::LIST_AGENTS => "List all agents",
      USectionAgent::GET => "Get details about an agent",
      USectionAgent::SET_ACTIVE => "Set an agent active/inactive",
      USectionAgent::CHANGE_OWNER => "Change the owner of an agent",
      USectionAgent::SET_NAME => "Set the name of an agent",
      USectionAgent::SET_CPU_ONLY => "Set if an agent is CPU only or not",
      USectionAgent::SET_EXTRA_PARAMS => "Set extra flags for an agent",
      USectionAgent::SET_ERROR_FLAG => "Set how errors from an agent should be handled",
      USectionAgent::SET_TRUSTED => "Set if an agent is trusted or not",
      USectionAgent::DELETE_AGENT => "Delete agents",
      default => "__" . $constant . "__",
    };
  }
}