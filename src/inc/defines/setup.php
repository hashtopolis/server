<?php

class DSetupType {
  const INSTALLATION   = "installation";  // these setups require an "empty" installation or can be done during installation
  const EXTENSION      = "extension";  // these install something additional, mostly independent of existing entries
  const PART_EXTENSION = "partExtension";  // these are extensions which insert data for a specific entry
  const REMOVAL        = "removal";  // they delete/clean specific things
}

class DSetupAction {
  const EXECUTE_SETUP      = "executeSetup";
  const EXECUTE_SETUP_PERM = DAccessControl::SERVER_CONFIG_ACCESS;
}
