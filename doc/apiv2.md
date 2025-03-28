# Hashtopolis API

> Version v2

## Path Table

| Method | Path | Description |
| --- | --- | --- |
| GET | [/api/v2/ui/accessgroups](#getapiv2uiaccessgroups) |  |
| POST | [/api/v2/ui/accessgroups](#postapiv2uiaccessgroups) |  |
| GET | [/api/v2/ui/accessgroups/count](#getapiv2uiaccessgroupscount) |  |
| GET | [/api/v2/ui/accessgroups/{id:[0-9]+}/{relation:userMembers}](#getapiv2uiaccessgroupsid0-9relationusermembers) |  |
| GET | [/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:userMembers}](#getapiv2uiaccessgroupsid0-9relationshipsrelationusermembers) |  |
| PATCH | [/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:userMembers}](#patchapiv2uiaccessgroupsid0-9relationshipsrelationusermembers) |  |
| POST | [/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:userMembers}](#postapiv2uiaccessgroupsid0-9relationshipsrelationusermembers) |  |
| DELETE | [/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:userMembers}](#deleteapiv2uiaccessgroupsid0-9relationshipsrelationusermembers) |  |
| GET | [/api/v2/ui/accessgroups/{id:[0-9]+}/{relation:agentMembers}](#getapiv2uiaccessgroupsid0-9relationagentmembers) |  |
| GET | [/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:agentMembers}](#getapiv2uiaccessgroupsid0-9relationshipsrelationagentmembers) |  |
| PATCH | [/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:agentMembers}](#patchapiv2uiaccessgroupsid0-9relationshipsrelationagentmembers) |  |
| POST | [/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:agentMembers}](#postapiv2uiaccessgroupsid0-9relationshipsrelationagentmembers) |  |
| DELETE | [/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:agentMembers}](#deleteapiv2uiaccessgroupsid0-9relationshipsrelationagentmembers) |  |
| GET | [/api/v2/ui/accessgroups/{id:[0-9]+}](#getapiv2uiaccessgroupsid0-9) |  |
| PATCH | [/api/v2/ui/accessgroups/{id:[0-9]+}](#patchapiv2uiaccessgroupsid0-9) |  |
| DELETE | [/api/v2/ui/accessgroups/{id:[0-9]+}](#deleteapiv2uiaccessgroupsid0-9) |  |
| GET | [/api/v2/ui/agentassignments](#getapiv2uiagentassignments) |  |
| POST | [/api/v2/ui/agentassignments](#postapiv2uiagentassignments) |  |
| GET | [/api/v2/ui/agentassignments/count](#getapiv2uiagentassignmentscount) |  |
| GET | [/api/v2/ui/agentassignments/{id:[0-9]+}/{relation:agent}](#getapiv2uiagentassignmentsid0-9relationagent) |  |
| GET | [/api/v2/ui/agentassignments/{id:[0-9]+}/relationships/{relation:agent}](#getapiv2uiagentassignmentsid0-9relationshipsrelationagent) |  |
| PATCH | [/api/v2/ui/agentassignments/{id:[0-9]+}/relationships/{relation:agent}](#patchapiv2uiagentassignmentsid0-9relationshipsrelationagent) |  |
| GET | [/api/v2/ui/agentassignments/{id:[0-9]+}/{relation:task}](#getapiv2uiagentassignmentsid0-9relationtask) |  |
| GET | [/api/v2/ui/agentassignments/{id:[0-9]+}/relationships/{relation:task}](#getapiv2uiagentassignmentsid0-9relationshipsrelationtask) |  |
| PATCH | [/api/v2/ui/agentassignments/{id:[0-9]+}/relationships/{relation:task}](#patchapiv2uiagentassignmentsid0-9relationshipsrelationtask) |  |
| GET | [/api/v2/ui/agentassignments/{id:[0-9]+}](#getapiv2uiagentassignmentsid0-9) |  |
| DELETE | [/api/v2/ui/agentassignments/{id:[0-9]+}](#deleteapiv2uiagentassignmentsid0-9) |  |
| GET | [/api/v2/ui/agentbinaries](#getapiv2uiagentbinaries) |  |
| POST | [/api/v2/ui/agentbinaries](#postapiv2uiagentbinaries) |  |
| GET | [/api/v2/ui/agentbinaries/count](#getapiv2uiagentbinariescount) |  |
| GET | [/api/v2/ui/agentbinaries/{id:[0-9]+}](#getapiv2uiagentbinariesid0-9) |  |
| PATCH | [/api/v2/ui/agentbinaries/{id:[0-9]+}](#patchapiv2uiagentbinariesid0-9) |  |
| DELETE | [/api/v2/ui/agentbinaries/{id:[0-9]+}](#deleteapiv2uiagentbinariesid0-9) |  |
| GET | [/api/v2/ui/agents](#getapiv2uiagents) |  |
| GET | [/api/v2/ui/agents/count](#getapiv2uiagentscount) |  |
| GET | [/api/v2/ui/agents/{id:[0-9]+}/{relation:accessGroups}](#getapiv2uiagentsid0-9relationaccessgroups) |  |
| GET | [/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:accessGroups}](#getapiv2uiagentsid0-9relationshipsrelationaccessgroups) |  |
| PATCH | [/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:accessGroups}](#patchapiv2uiagentsid0-9relationshipsrelationaccessgroups) |  |
| POST | [/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:accessGroups}](#postapiv2uiagentsid0-9relationshipsrelationaccessgroups) |  |
| DELETE | [/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:accessGroups}](#deleteapiv2uiagentsid0-9relationshipsrelationaccessgroups) |  |
| GET | [/api/v2/ui/agents/{id:[0-9]+}/{relation:agentStats}](#getapiv2uiagentsid0-9relationagentstats) |  |
| GET | [/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:agentStats}](#getapiv2uiagentsid0-9relationshipsrelationagentstats) |  |
| PATCH | [/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:agentStats}](#patchapiv2uiagentsid0-9relationshipsrelationagentstats) |  |
| POST | [/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:agentStats}](#postapiv2uiagentsid0-9relationshipsrelationagentstats) |  |
| DELETE | [/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:agentStats}](#deleteapiv2uiagentsid0-9relationshipsrelationagentstats) |  |
| GET | [/api/v2/ui/agents/{id:[0-9]+}](#getapiv2uiagentsid0-9) |  |
| PATCH | [/api/v2/ui/agents/{id:[0-9]+}](#patchapiv2uiagentsid0-9) |  |
| DELETE | [/api/v2/ui/agents/{id:[0-9]+}](#deleteapiv2uiagentsid0-9) |  |
| GET | [/api/v2/ui/agentstats](#getapiv2uiagentstats) |  |
| GET | [/api/v2/ui/agentstats/count](#getapiv2uiagentstatscount) |  |
| GET | [/api/v2/ui/agentstats/{id:[0-9]+}](#getapiv2uiagentstatsid0-9) |  |
| DELETE | [/api/v2/ui/agentstats/{id:[0-9]+}](#deleteapiv2uiagentstatsid0-9) |  |
| GET | [/api/v2/ui/chunks](#getapiv2uichunks) |  |
| GET | [/api/v2/ui/chunks/count](#getapiv2uichunkscount) |  |
| GET | [/api/v2/ui/chunks/{id:[0-9]+}/{relation:agent}](#getapiv2uichunksid0-9relationagent) |  |
| GET | [/api/v2/ui/chunks/{id:[0-9]+}/relationships/{relation:agent}](#getapiv2uichunksid0-9relationshipsrelationagent) |  |
| PATCH | [/api/v2/ui/chunks/{id:[0-9]+}/relationships/{relation:agent}](#patchapiv2uichunksid0-9relationshipsrelationagent) |  |
| GET | [/api/v2/ui/chunks/{id:[0-9]+}/{relation:task}](#getapiv2uichunksid0-9relationtask) |  |
| GET | [/api/v2/ui/chunks/{id:[0-9]+}/relationships/{relation:task}](#getapiv2uichunksid0-9relationshipsrelationtask) |  |
| PATCH | [/api/v2/ui/chunks/{id:[0-9]+}/relationships/{relation:task}](#patchapiv2uichunksid0-9relationshipsrelationtask) |  |
| GET | [/api/v2/ui/chunks/{id:[0-9]+}](#getapiv2uichunksid0-9) |  |
| GET | [/api/v2/ui/configs](#getapiv2uiconfigs) |  |
| GET | [/api/v2/ui/configs/count](#getapiv2uiconfigscount) |  |
| GET | [/api/v2/ui/configs/{id:[0-9]+}/{relation:configSection}](#getapiv2uiconfigsid0-9relationconfigsection) |  |
| GET | [/api/v2/ui/configs/{id:[0-9]+}/relationships/{relation:configSection}](#getapiv2uiconfigsid0-9relationshipsrelationconfigsection) |  |
| PATCH | [/api/v2/ui/configs/{id:[0-9]+}/relationships/{relation:configSection}](#patchapiv2uiconfigsid0-9relationshipsrelationconfigsection) |  |
| GET | [/api/v2/ui/configs/{id:[0-9]+}](#getapiv2uiconfigsid0-9) |  |
| PATCH | [/api/v2/ui/configs/{id:[0-9]+}](#patchapiv2uiconfigsid0-9) |  |
| GET | [/api/v2/ui/configsections](#getapiv2uiconfigsections) |  |
| GET | [/api/v2/ui/configsections/count](#getapiv2uiconfigsectionscount) |  |
| GET | [/api/v2/ui/configsections/{id:[0-9]+}](#getapiv2uiconfigsectionsid0-9) |  |
| GET | [/api/v2/ui/crackers](#getapiv2uicrackers) |  |
| POST | [/api/v2/ui/crackers](#postapiv2uicrackers) |  |
| GET | [/api/v2/ui/crackers/count](#getapiv2uicrackerscount) |  |
| GET | [/api/v2/ui/crackers/{id:[0-9]+}/{relation:crackerBinaryType}](#getapiv2uicrackersid0-9relationcrackerbinarytype) |  |
| GET | [/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:crackerBinaryType}](#getapiv2uicrackersid0-9relationshipsrelationcrackerbinarytype) |  |
| PATCH | [/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:crackerBinaryType}](#patchapiv2uicrackersid0-9relationshipsrelationcrackerbinarytype) |  |
| GET | [/api/v2/ui/crackers/{id:[0-9]+}/{relation:tasks}](#getapiv2uicrackersid0-9relationtasks) |  |
| GET | [/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:tasks}](#getapiv2uicrackersid0-9relationshipsrelationtasks) |  |
| PATCH | [/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:tasks}](#patchapiv2uicrackersid0-9relationshipsrelationtasks) |  |
| POST | [/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:tasks}](#postapiv2uicrackersid0-9relationshipsrelationtasks) |  |
| DELETE | [/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:tasks}](#deleteapiv2uicrackersid0-9relationshipsrelationtasks) |  |
| GET | [/api/v2/ui/crackers/{id:[0-9]+}](#getapiv2uicrackersid0-9) |  |
| PATCH | [/api/v2/ui/crackers/{id:[0-9]+}](#patchapiv2uicrackersid0-9) |  |
| DELETE | [/api/v2/ui/crackers/{id:[0-9]+}](#deleteapiv2uicrackersid0-9) |  |
| GET | [/api/v2/ui/crackertypes](#getapiv2uicrackertypes) |  |
| POST | [/api/v2/ui/crackertypes](#postapiv2uicrackertypes) |  |
| GET | [/api/v2/ui/crackertypes/count](#getapiv2uicrackertypescount) |  |
| GET | [/api/v2/ui/crackertypes/{id:[0-9]+}/{relation:crackerVersions}](#getapiv2uicrackertypesid0-9relationcrackerversions) |  |
| GET | [/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:crackerVersions}](#getapiv2uicrackertypesid0-9relationshipsrelationcrackerversions) |  |
| PATCH | [/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:crackerVersions}](#patchapiv2uicrackertypesid0-9relationshipsrelationcrackerversions) |  |
| POST | [/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:crackerVersions}](#postapiv2uicrackertypesid0-9relationshipsrelationcrackerversions) |  |
| DELETE | [/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:crackerVersions}](#deleteapiv2uicrackertypesid0-9relationshipsrelationcrackerversions) |  |
| GET | [/api/v2/ui/crackertypes/{id:[0-9]+}/{relation:tasks}](#getapiv2uicrackertypesid0-9relationtasks) |  |
| GET | [/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:tasks}](#getapiv2uicrackertypesid0-9relationshipsrelationtasks) |  |
| PATCH | [/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:tasks}](#patchapiv2uicrackertypesid0-9relationshipsrelationtasks) |  |
| POST | [/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:tasks}](#postapiv2uicrackertypesid0-9relationshipsrelationtasks) |  |
| DELETE | [/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:tasks}](#deleteapiv2uicrackertypesid0-9relationshipsrelationtasks) |  |
| GET | [/api/v2/ui/crackertypes/{id:[0-9]+}](#getapiv2uicrackertypesid0-9) |  |
| PATCH | [/api/v2/ui/crackertypes/{id:[0-9]+}](#patchapiv2uicrackertypesid0-9) |  |
| DELETE | [/api/v2/ui/crackertypes/{id:[0-9]+}](#deleteapiv2uicrackertypesid0-9) |  |
| GET | [/api/v2/ui/files](#getapiv2uifiles) |  |
| POST | [/api/v2/ui/files](#postapiv2uifiles) |  |
| GET | [/api/v2/ui/files/count](#getapiv2uifilescount) |  |
| GET | [/api/v2/ui/files/{id:[0-9]+}/{relation:accessGroup}](#getapiv2uifilesid0-9relationaccessgroup) |  |
| GET | [/api/v2/ui/files/{id:[0-9]+}/relationships/{relation:accessGroup}](#getapiv2uifilesid0-9relationshipsrelationaccessgroup) |  |
| PATCH | [/api/v2/ui/files/{id:[0-9]+}/relationships/{relation:accessGroup}](#patchapiv2uifilesid0-9relationshipsrelationaccessgroup) |  |
| GET | [/api/v2/ui/files/{id:[0-9]+}](#getapiv2uifilesid0-9) |  |
| PATCH | [/api/v2/ui/files/{id:[0-9]+}](#patchapiv2uifilesid0-9) |  |
| DELETE | [/api/v2/ui/files/{id:[0-9]+}](#deleteapiv2uifilesid0-9) |  |
| GET | [/api/v2/ui/globalpermissiongroups](#getapiv2uiglobalpermissiongroups) |  |
| POST | [/api/v2/ui/globalpermissiongroups](#postapiv2uiglobalpermissiongroups) |  |
| GET | [/api/v2/ui/globalpermissiongroups/count](#getapiv2uiglobalpermissiongroupscount) |  |
| GET | [/api/v2/ui/globalpermissiongroups/{id:[0-9]+}/{relation:userMembers}](#getapiv2uiglobalpermissiongroupsid0-9relationusermembers) |  |
| GET | [/api/v2/ui/globalpermissiongroups/{id:[0-9]+}/relationships/{relation:userMembers}](#getapiv2uiglobalpermissiongroupsid0-9relationshipsrelationusermembers) |  |
| PATCH | [/api/v2/ui/globalpermissiongroups/{id:[0-9]+}/relationships/{relation:userMembers}](#patchapiv2uiglobalpermissiongroupsid0-9relationshipsrelationusermembers) |  |
| POST | [/api/v2/ui/globalpermissiongroups/{id:[0-9]+}/relationships/{relation:userMembers}](#postapiv2uiglobalpermissiongroupsid0-9relationshipsrelationusermembers) |  |
| DELETE | [/api/v2/ui/globalpermissiongroups/{id:[0-9]+}/relationships/{relation:userMembers}](#deleteapiv2uiglobalpermissiongroupsid0-9relationshipsrelationusermembers) |  |
| GET | [/api/v2/ui/globalpermissiongroups/{id:[0-9]+}](#getapiv2uiglobalpermissiongroupsid0-9) |  |
| PATCH | [/api/v2/ui/globalpermissiongroups/{id:[0-9]+}](#patchapiv2uiglobalpermissiongroupsid0-9) |  |
| DELETE | [/api/v2/ui/globalpermissiongroups/{id:[0-9]+}](#deleteapiv2uiglobalpermissiongroupsid0-9) |  |
| GET | [/api/v2/ui/hashes](#getapiv2uihashes) |  |
| GET | [/api/v2/ui/hashes/count](#getapiv2uihashescount) |  |
| GET | [/api/v2/ui/hashes/{id:[0-9]+}/{relation:chunk}](#getapiv2uihashesid0-9relationchunk) |  |
| GET | [/api/v2/ui/hashes/{id:[0-9]+}/relationships/{relation:chunk}](#getapiv2uihashesid0-9relationshipsrelationchunk) |  |
| PATCH | [/api/v2/ui/hashes/{id:[0-9]+}/relationships/{relation:chunk}](#patchapiv2uihashesid0-9relationshipsrelationchunk) |  |
| GET | [/api/v2/ui/hashes/{id:[0-9]+}/{relation:hashlist}](#getapiv2uihashesid0-9relationhashlist) |  |
| GET | [/api/v2/ui/hashes/{id:[0-9]+}/relationships/{relation:hashlist}](#getapiv2uihashesid0-9relationshipsrelationhashlist) |  |
| PATCH | [/api/v2/ui/hashes/{id:[0-9]+}/relationships/{relation:hashlist}](#patchapiv2uihashesid0-9relationshipsrelationhashlist) |  |
| GET | [/api/v2/ui/hashes/{id:[0-9]+}](#getapiv2uihashesid0-9) |  |
| GET | [/api/v2/ui/hashlists](#getapiv2uihashlists) |  |
| POST | [/api/v2/ui/hashlists](#postapiv2uihashlists) |  |
| GET | [/api/v2/ui/hashlists/count](#getapiv2uihashlistscount) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}/{relation:accessGroup}](#getapiv2uihashlistsid0-9relationaccessgroup) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:accessGroup}](#getapiv2uihashlistsid0-9relationshipsrelationaccessgroup) |  |
| PATCH | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:accessGroup}](#patchapiv2uihashlistsid0-9relationshipsrelationaccessgroup) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}/{relation:hashType}](#getapiv2uihashlistsid0-9relationhashtype) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashType}](#getapiv2uihashlistsid0-9relationshipsrelationhashtype) |  |
| PATCH | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashType}](#patchapiv2uihashlistsid0-9relationshipsrelationhashtype) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}/{relation:hashes}](#getapiv2uihashlistsid0-9relationhashes) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashes}](#getapiv2uihashlistsid0-9relationshipsrelationhashes) |  |
| PATCH | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashes}](#patchapiv2uihashlistsid0-9relationshipsrelationhashes) |  |
| POST | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashes}](#postapiv2uihashlistsid0-9relationshipsrelationhashes) |  |
| DELETE | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashes}](#deleteapiv2uihashlistsid0-9relationshipsrelationhashes) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}/{relation:hashlists}](#getapiv2uihashlistsid0-9relationhashlists) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashlists}](#getapiv2uihashlistsid0-9relationshipsrelationhashlists) |  |
| PATCH | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashlists}](#patchapiv2uihashlistsid0-9relationshipsrelationhashlists) |  |
| POST | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashlists}](#postapiv2uihashlistsid0-9relationshipsrelationhashlists) |  |
| DELETE | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashlists}](#deleteapiv2uihashlistsid0-9relationshipsrelationhashlists) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}/{relation:tasks}](#getapiv2uihashlistsid0-9relationtasks) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:tasks}](#getapiv2uihashlistsid0-9relationshipsrelationtasks) |  |
| PATCH | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:tasks}](#patchapiv2uihashlistsid0-9relationshipsrelationtasks) |  |
| POST | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:tasks}](#postapiv2uihashlistsid0-9relationshipsrelationtasks) |  |
| DELETE | [/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:tasks}](#deleteapiv2uihashlistsid0-9relationshipsrelationtasks) |  |
| GET | [/api/v2/ui/hashlists/{id:[0-9]+}](#getapiv2uihashlistsid0-9) |  |
| PATCH | [/api/v2/ui/hashlists/{id:[0-9]+}](#patchapiv2uihashlistsid0-9) |  |
| DELETE | [/api/v2/ui/hashlists/{id:[0-9]+}](#deleteapiv2uihashlistsid0-9) |  |
| GET | [/api/v2/ui/hashtypes](#getapiv2uihashtypes) |  |
| POST | [/api/v2/ui/hashtypes](#postapiv2uihashtypes) |  |
| GET | [/api/v2/ui/hashtypes/count](#getapiv2uihashtypescount) |  |
| GET | [/api/v2/ui/hashtypes/{id:[0-9]+}](#getapiv2uihashtypesid0-9) |  |
| PATCH | [/api/v2/ui/hashtypes/{id:[0-9]+}](#patchapiv2uihashtypesid0-9) |  |
| DELETE | [/api/v2/ui/hashtypes/{id:[0-9]+}](#deleteapiv2uihashtypesid0-9) |  |
| GET | [/api/v2/ui/healthcheckagents](#getapiv2uihealthcheckagents) |  |
| GET | [/api/v2/ui/healthcheckagents/count](#getapiv2uihealthcheckagentscount) |  |
| GET | [/api/v2/ui/healthcheckagents/{id:[0-9]+}/{relation:agent}](#getapiv2uihealthcheckagentsid0-9relationagent) |  |
| GET | [/api/v2/ui/healthcheckagents/{id:[0-9]+}/relationships/{relation:agent}](#getapiv2uihealthcheckagentsid0-9relationshipsrelationagent) |  |
| PATCH | [/api/v2/ui/healthcheckagents/{id:[0-9]+}/relationships/{relation:agent}](#patchapiv2uihealthcheckagentsid0-9relationshipsrelationagent) |  |
| GET | [/api/v2/ui/healthcheckagents/{id:[0-9]+}/{relation:healthCheck}](#getapiv2uihealthcheckagentsid0-9relationhealthcheck) |  |
| GET | [/api/v2/ui/healthcheckagents/{id:[0-9]+}/relationships/{relation:healthCheck}](#getapiv2uihealthcheckagentsid0-9relationshipsrelationhealthcheck) |  |
| PATCH | [/api/v2/ui/healthcheckagents/{id:[0-9]+}/relationships/{relation:healthCheck}](#patchapiv2uihealthcheckagentsid0-9relationshipsrelationhealthcheck) |  |
| GET | [/api/v2/ui/healthcheckagents/{id:[0-9]+}](#getapiv2uihealthcheckagentsid0-9) |  |
| GET | [/api/v2/ui/healthchecks](#getapiv2uihealthchecks) |  |
| POST | [/api/v2/ui/healthchecks](#postapiv2uihealthchecks) |  |
| GET | [/api/v2/ui/healthchecks/count](#getapiv2uihealthcheckscount) |  |
| GET | [/api/v2/ui/healthchecks/{id:[0-9]+}/{relation:crackerBinary}](#getapiv2uihealthchecksid0-9relationcrackerbinary) |  |
| GET | [/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:crackerBinary}](#getapiv2uihealthchecksid0-9relationshipsrelationcrackerbinary) |  |
| PATCH | [/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:crackerBinary}](#patchapiv2uihealthchecksid0-9relationshipsrelationcrackerbinary) |  |
| GET | [/api/v2/ui/healthchecks/{id:[0-9]+}/{relation:healthCheckAgents}](#getapiv2uihealthchecksid0-9relationhealthcheckagents) |  |
| GET | [/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:healthCheckAgents}](#getapiv2uihealthchecksid0-9relationshipsrelationhealthcheckagents) |  |
| PATCH | [/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:healthCheckAgents}](#patchapiv2uihealthchecksid0-9relationshipsrelationhealthcheckagents) |  |
| POST | [/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:healthCheckAgents}](#postapiv2uihealthchecksid0-9relationshipsrelationhealthcheckagents) |  |
| DELETE | [/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:healthCheckAgents}](#deleteapiv2uihealthchecksid0-9relationshipsrelationhealthcheckagents) |  |
| GET | [/api/v2/ui/healthchecks/{id:[0-9]+}](#getapiv2uihealthchecksid0-9) |  |
| PATCH | [/api/v2/ui/healthchecks/{id:[0-9]+}](#patchapiv2uihealthchecksid0-9) |  |
| DELETE | [/api/v2/ui/healthchecks/{id:[0-9]+}](#deleteapiv2uihealthchecksid0-9) |  |
| GET | [/api/v2/ui/logentries](#getapiv2uilogentries) |  |
| POST | [/api/v2/ui/logentries](#postapiv2uilogentries) |  |
| GET | [/api/v2/ui/logentries/count](#getapiv2uilogentriescount) |  |
| GET | [/api/v2/ui/logentries/{id:[0-9]+}](#getapiv2uilogentriesid0-9) |  |
| PATCH | [/api/v2/ui/logentries/{id:[0-9]+}](#patchapiv2uilogentriesid0-9) |  |
| DELETE | [/api/v2/ui/logentries/{id:[0-9]+}](#deleteapiv2uilogentriesid0-9) |  |
| GET | [/api/v2/ui/notifications](#getapiv2uinotifications) |  |
| POST | [/api/v2/ui/notifications](#postapiv2uinotifications) |  |
| GET | [/api/v2/ui/notifications/count](#getapiv2uinotificationscount) |  |
| GET | [/api/v2/ui/notifications/{id:[0-9]+}/{relation:user}](#getapiv2uinotificationsid0-9relationuser) |  |
| GET | [/api/v2/ui/notifications/{id:[0-9]+}/relationships/{relation:user}](#getapiv2uinotificationsid0-9relationshipsrelationuser) |  |
| PATCH | [/api/v2/ui/notifications/{id:[0-9]+}/relationships/{relation:user}](#patchapiv2uinotificationsid0-9relationshipsrelationuser) |  |
| GET | [/api/v2/ui/notifications/{id:[0-9]+}](#getapiv2uinotificationsid0-9) |  |
| PATCH | [/api/v2/ui/notifications/{id:[0-9]+}](#patchapiv2uinotificationsid0-9) |  |
| DELETE | [/api/v2/ui/notifications/{id:[0-9]+}](#deleteapiv2uinotificationsid0-9) |  |
| GET | [/api/v2/ui/preprocessors](#getapiv2uipreprocessors) |  |
| POST | [/api/v2/ui/preprocessors](#postapiv2uipreprocessors) |  |
| GET | [/api/v2/ui/preprocessors/count](#getapiv2uipreprocessorscount) |  |
| GET | [/api/v2/ui/preprocessors/{id:[0-9]+}](#getapiv2uipreprocessorsid0-9) |  |
| PATCH | [/api/v2/ui/preprocessors/{id:[0-9]+}](#patchapiv2uipreprocessorsid0-9) |  |
| DELETE | [/api/v2/ui/preprocessors/{id:[0-9]+}](#deleteapiv2uipreprocessorsid0-9) |  |
| GET | [/api/v2/ui/pretasks](#getapiv2uipretasks) |  |
| POST | [/api/v2/ui/pretasks](#postapiv2uipretasks) |  |
| GET | [/api/v2/ui/pretasks/count](#getapiv2uipretaskscount) |  |
| GET | [/api/v2/ui/pretasks/{id:[0-9]+}/{relation:pretaskFiles}](#getapiv2uipretasksid0-9relationpretaskfiles) |  |
| GET | [/api/v2/ui/pretasks/{id:[0-9]+}/relationships/{relation:pretaskFiles}](#getapiv2uipretasksid0-9relationshipsrelationpretaskfiles) |  |
| PATCH | [/api/v2/ui/pretasks/{id:[0-9]+}/relationships/{relation:pretaskFiles}](#patchapiv2uipretasksid0-9relationshipsrelationpretaskfiles) |  |
| POST | [/api/v2/ui/pretasks/{id:[0-9]+}/relationships/{relation:pretaskFiles}](#postapiv2uipretasksid0-9relationshipsrelationpretaskfiles) |  |
| DELETE | [/api/v2/ui/pretasks/{id:[0-9]+}/relationships/{relation:pretaskFiles}](#deleteapiv2uipretasksid0-9relationshipsrelationpretaskfiles) |  |
| GET | [/api/v2/ui/pretasks/{id:[0-9]+}](#getapiv2uipretasksid0-9) |  |
| PATCH | [/api/v2/ui/pretasks/{id:[0-9]+}](#patchapiv2uipretasksid0-9) |  |
| DELETE | [/api/v2/ui/pretasks/{id:[0-9]+}](#deleteapiv2uipretasksid0-9) |  |
| GET | [/api/v2/ui/speeds](#getapiv2uispeeds) |  |
| GET | [/api/v2/ui/speeds/count](#getapiv2uispeedscount) |  |
| GET | [/api/v2/ui/speeds/{id:[0-9]+}/{relation:agent}](#getapiv2uispeedsid0-9relationagent) |  |
| GET | [/api/v2/ui/speeds/{id:[0-9]+}/relationships/{relation:agent}](#getapiv2uispeedsid0-9relationshipsrelationagent) |  |
| PATCH | [/api/v2/ui/speeds/{id:[0-9]+}/relationships/{relation:agent}](#patchapiv2uispeedsid0-9relationshipsrelationagent) |  |
| GET | [/api/v2/ui/speeds/{id:[0-9]+}/{relation:task}](#getapiv2uispeedsid0-9relationtask) |  |
| GET | [/api/v2/ui/speeds/{id:[0-9]+}/relationships/{relation:task}](#getapiv2uispeedsid0-9relationshipsrelationtask) |  |
| PATCH | [/api/v2/ui/speeds/{id:[0-9]+}/relationships/{relation:task}](#patchapiv2uispeedsid0-9relationshipsrelationtask) |  |
| GET | [/api/v2/ui/speeds/{id:[0-9]+}](#getapiv2uispeedsid0-9) |  |
| GET | [/api/v2/ui/supertasks](#getapiv2uisupertasks) |  |
| POST | [/api/v2/ui/supertasks](#postapiv2uisupertasks) |  |
| GET | [/api/v2/ui/supertasks/count](#getapiv2uisupertaskscount) |  |
| GET | [/api/v2/ui/supertasks/{id:[0-9]+}/{relation:pretasks}](#getapiv2uisupertasksid0-9relationpretasks) |  |
| GET | [/api/v2/ui/supertasks/{id:[0-9]+}/relationships/{relation:pretasks}](#getapiv2uisupertasksid0-9relationshipsrelationpretasks) |  |
| PATCH | [/api/v2/ui/supertasks/{id:[0-9]+}/relationships/{relation:pretasks}](#patchapiv2uisupertasksid0-9relationshipsrelationpretasks) |  |
| POST | [/api/v2/ui/supertasks/{id:[0-9]+}/relationships/{relation:pretasks}](#postapiv2uisupertasksid0-9relationshipsrelationpretasks) |  |
| DELETE | [/api/v2/ui/supertasks/{id:[0-9]+}/relationships/{relation:pretasks}](#deleteapiv2uisupertasksid0-9relationshipsrelationpretasks) |  |
| GET | [/api/v2/ui/supertasks/{id:[0-9]+}](#getapiv2uisupertasksid0-9) |  |
| PATCH | [/api/v2/ui/supertasks/{id:[0-9]+}](#patchapiv2uisupertasksid0-9) |  |
| DELETE | [/api/v2/ui/supertasks/{id:[0-9]+}](#deleteapiv2uisupertasksid0-9) |  |
| GET | [/api/v2/ui/tasks](#getapiv2uitasks) |  |
| POST | [/api/v2/ui/tasks](#postapiv2uitasks) |  |
| GET | [/api/v2/ui/tasks/count](#getapiv2uitaskscount) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/{relation:crackerBinary}](#getapiv2uitasksid0-9relationcrackerbinary) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:crackerBinary}](#getapiv2uitasksid0-9relationshipsrelationcrackerbinary) |  |
| PATCH | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:crackerBinary}](#patchapiv2uitasksid0-9relationshipsrelationcrackerbinary) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/{relation:crackerBinaryType}](#getapiv2uitasksid0-9relationcrackerbinarytype) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:crackerBinaryType}](#getapiv2uitasksid0-9relationshipsrelationcrackerbinarytype) |  |
| PATCH | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:crackerBinaryType}](#patchapiv2uitasksid0-9relationshipsrelationcrackerbinarytype) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/{relation:hashlist}](#getapiv2uitasksid0-9relationhashlist) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:hashlist}](#getapiv2uitasksid0-9relationshipsrelationhashlist) |  |
| PATCH | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:hashlist}](#patchapiv2uitasksid0-9relationshipsrelationhashlist) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/{relation:assignedAgents}](#getapiv2uitasksid0-9relationassignedagents) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:assignedAgents}](#getapiv2uitasksid0-9relationshipsrelationassignedagents) |  |
| PATCH | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:assignedAgents}](#patchapiv2uitasksid0-9relationshipsrelationassignedagents) |  |
| POST | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:assignedAgents}](#postapiv2uitasksid0-9relationshipsrelationassignedagents) |  |
| DELETE | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:assignedAgents}](#deleteapiv2uitasksid0-9relationshipsrelationassignedagents) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/{relation:files}](#getapiv2uitasksid0-9relationfiles) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:files}](#getapiv2uitasksid0-9relationshipsrelationfiles) |  |
| PATCH | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:files}](#patchapiv2uitasksid0-9relationshipsrelationfiles) |  |
| POST | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:files}](#postapiv2uitasksid0-9relationshipsrelationfiles) |  |
| DELETE | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:files}](#deleteapiv2uitasksid0-9relationshipsrelationfiles) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/{relation:speeds}](#getapiv2uitasksid0-9relationspeeds) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:speeds}](#getapiv2uitasksid0-9relationshipsrelationspeeds) |  |
| PATCH | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:speeds}](#patchapiv2uitasksid0-9relationshipsrelationspeeds) |  |
| POST | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:speeds}](#postapiv2uitasksid0-9relationshipsrelationspeeds) |  |
| DELETE | [/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:speeds}](#deleteapiv2uitasksid0-9relationshipsrelationspeeds) |  |
| GET | [/api/v2/ui/tasks/{id:[0-9]+}](#getapiv2uitasksid0-9) |  |
| PATCH | [/api/v2/ui/tasks/{id:[0-9]+}](#patchapiv2uitasksid0-9) |  |
| DELETE | [/api/v2/ui/tasks/{id:[0-9]+}](#deleteapiv2uitasksid0-9) |  |
| GET | [/api/v2/ui/taskwrappers](#getapiv2uitaskwrappers) |  |
| GET | [/api/v2/ui/taskwrappers/count](#getapiv2uitaskwrapperscount) |  |
| GET | [/api/v2/ui/taskwrappers/{id:[0-9]+}/{relation:accessGroup}](#getapiv2uitaskwrappersid0-9relationaccessgroup) |  |
| GET | [/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:accessGroup}](#getapiv2uitaskwrappersid0-9relationshipsrelationaccessgroup) |  |
| PATCH | [/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:accessGroup}](#patchapiv2uitaskwrappersid0-9relationshipsrelationaccessgroup) |  |
| GET | [/api/v2/ui/taskwrappers/{id:[0-9]+}/{relation:tasks}](#getapiv2uitaskwrappersid0-9relationtasks) |  |
| GET | [/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:tasks}](#getapiv2uitaskwrappersid0-9relationshipsrelationtasks) |  |
| PATCH | [/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:tasks}](#patchapiv2uitaskwrappersid0-9relationshipsrelationtasks) |  |
| POST | [/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:tasks}](#postapiv2uitaskwrappersid0-9relationshipsrelationtasks) |  |
| DELETE | [/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:tasks}](#deleteapiv2uitaskwrappersid0-9relationshipsrelationtasks) |  |
| GET | [/api/v2/ui/taskwrappers/{id:[0-9]+}](#getapiv2uitaskwrappersid0-9) |  |
| PATCH | [/api/v2/ui/taskwrappers/{id:[0-9]+}](#patchapiv2uitaskwrappersid0-9) |  |
| DELETE | [/api/v2/ui/taskwrappers/{id:[0-9]+}](#deleteapiv2uitaskwrappersid0-9) |  |
| GET | [/api/v2/ui/users](#getapiv2uiusers) |  |
| POST | [/api/v2/ui/users](#postapiv2uiusers) |  |
| GET | [/api/v2/ui/users/count](#getapiv2uiuserscount) |  |
| GET | [/api/v2/ui/users/{id:[0-9]+}/{relation:globalPermissionGroup}](#getapiv2uiusersid0-9relationglobalpermissiongroup) |  |
| GET | [/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:globalPermissionGroup}](#getapiv2uiusersid0-9relationshipsrelationglobalpermissiongroup) |  |
| PATCH | [/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:globalPermissionGroup}](#patchapiv2uiusersid0-9relationshipsrelationglobalpermissiongroup) |  |
| GET | [/api/v2/ui/users/{id:[0-9]+}/{relation:accessGroups}](#getapiv2uiusersid0-9relationaccessgroups) |  |
| GET | [/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:accessGroups}](#getapiv2uiusersid0-9relationshipsrelationaccessgroups) |  |
| PATCH | [/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:accessGroups}](#patchapiv2uiusersid0-9relationshipsrelationaccessgroups) |  |
| POST | [/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:accessGroups}](#postapiv2uiusersid0-9relationshipsrelationaccessgroups) |  |
| DELETE | [/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:accessGroups}](#deleteapiv2uiusersid0-9relationshipsrelationaccessgroups) |  |
| GET | [/api/v2/ui/users/{id:[0-9]+}](#getapiv2uiusersid0-9) |  |
| PATCH | [/api/v2/ui/users/{id:[0-9]+}](#patchapiv2uiusersid0-9) |  |
| DELETE | [/api/v2/ui/users/{id:[0-9]+}](#deleteapiv2uiusersid0-9) |  |
| GET | [/api/v2/ui/vouchers](#getapiv2uivouchers) |  |
| POST | [/api/v2/ui/vouchers](#postapiv2uivouchers) |  |
| GET | [/api/v2/ui/vouchers/count](#getapiv2uivoucherscount) |  |
| GET | [/api/v2/ui/vouchers/{id:[0-9]+}](#getapiv2uivouchersid0-9) |  |
| PATCH | [/api/v2/ui/vouchers/{id:[0-9]+}](#patchapiv2uivouchersid0-9) |  |
| DELETE | [/api/v2/ui/vouchers/{id:[0-9]+}](#deleteapiv2uivouchersid0-9) |  |
| POST | [/api/v2/auth/token](#postapiv2authtoken) |  |

## Reference Table

| Name | Path | Description |
| --- | --- | --- |
| ListResponse | [#/components/schemas/ListResponse](#componentsschemaslistresponse) |  |
| ErrorResponse | [#/components/schemas/ErrorResponse](#componentsschemaserrorresponse) |  |
| NotFoundResponse | [#/components/schemas/NotFoundResponse](#componentsschemasnotfoundresponse) |  |
| AccessGroupCreate | [#/components/schemas/AccessGroupCreate](#componentsschemasaccessgroupcreate) |  |
| AccessGroupPatch | [#/components/schemas/AccessGroupPatch](#componentsschemasaccessgrouppatch) |  |
| AccessGroupResponse | [#/components/schemas/AccessGroupResponse](#componentsschemasaccessgroupresponse) |  |
| AccessGroupSingleResponse | [#/components/schemas/AccessGroupSingleResponse](#componentsschemasaccessgroupsingleresponse) |  |
| AccessGroupPostPatchResponse | [#/components/schemas/AccessGroupPostPatchResponse](#componentsschemasaccessgrouppostpatchresponse) |  |
| AccessGroupListResponse | [#/components/schemas/AccessGroupListResponse](#componentsschemasaccessgrouplistresponse) |  |
| AssignmentCreate | [#/components/schemas/AssignmentCreate](#componentsschemasassignmentcreate) |  |
| AssignmentPatch | [#/components/schemas/AssignmentPatch](#componentsschemasassignmentpatch) |  |
| AssignmentResponse | [#/components/schemas/AssignmentResponse](#componentsschemasassignmentresponse) |  |
| AssignmentSingleResponse | [#/components/schemas/AssignmentSingleResponse](#componentsschemasassignmentsingleresponse) |  |
| AssignmentPostPatchResponse | [#/components/schemas/AssignmentPostPatchResponse](#componentsschemasassignmentpostpatchresponse) |  |
| AssignmentListResponse | [#/components/schemas/AssignmentListResponse](#componentsschemasassignmentlistresponse) |  |
| AgentBinaryCreate | [#/components/schemas/AgentBinaryCreate](#componentsschemasagentbinarycreate) |  |
| AgentBinaryPatch | [#/components/schemas/AgentBinaryPatch](#componentsschemasagentbinarypatch) |  |
| AgentBinaryResponse | [#/components/schemas/AgentBinaryResponse](#componentsschemasagentbinaryresponse) |  |
| AgentBinarySingleResponse | [#/components/schemas/AgentBinarySingleResponse](#componentsschemasagentbinarysingleresponse) |  |
| AgentBinaryPostPatchResponse | [#/components/schemas/AgentBinaryPostPatchResponse](#componentsschemasagentbinarypostpatchresponse) |  |
| AgentBinaryListResponse | [#/components/schemas/AgentBinaryListResponse](#componentsschemasagentbinarylistresponse) |  |
| AgentCreate | [#/components/schemas/AgentCreate](#componentsschemasagentcreate) |  |
| AgentPatch | [#/components/schemas/AgentPatch](#componentsschemasagentpatch) |  |
| AgentResponse | [#/components/schemas/AgentResponse](#componentsschemasagentresponse) |  |
| AgentSingleResponse | [#/components/schemas/AgentSingleResponse](#componentsschemasagentsingleresponse) |  |
| AgentPostPatchResponse | [#/components/schemas/AgentPostPatchResponse](#componentsschemasagentpostpatchresponse) |  |
| AgentListResponse | [#/components/schemas/AgentListResponse](#componentsschemasagentlistresponse) |  |
| AgentStatCreate | [#/components/schemas/AgentStatCreate](#componentsschemasagentstatcreate) |  |
| AgentStatPatch | [#/components/schemas/AgentStatPatch](#componentsschemasagentstatpatch) |  |
| AgentStatResponse | [#/components/schemas/AgentStatResponse](#componentsschemasagentstatresponse) |  |
| AgentStatSingleResponse | [#/components/schemas/AgentStatSingleResponse](#componentsschemasagentstatsingleresponse) |  |
| AgentStatPostPatchResponse | [#/components/schemas/AgentStatPostPatchResponse](#componentsschemasagentstatpostpatchresponse) |  |
| AgentStatListResponse | [#/components/schemas/AgentStatListResponse](#componentsschemasagentstatlistresponse) |  |
| ChunkCreate | [#/components/schemas/ChunkCreate](#componentsschemaschunkcreate) |  |
| ChunkPatch | [#/components/schemas/ChunkPatch](#componentsschemaschunkpatch) |  |
| ChunkResponse | [#/components/schemas/ChunkResponse](#componentsschemaschunkresponse) |  |
| ChunkSingleResponse | [#/components/schemas/ChunkSingleResponse](#componentsschemaschunksingleresponse) |  |
| ChunkPostPatchResponse | [#/components/schemas/ChunkPostPatchResponse](#componentsschemaschunkpostpatchresponse) |  |
| ChunkListResponse | [#/components/schemas/ChunkListResponse](#componentsschemaschunklistresponse) |  |
| ConfigCreate | [#/components/schemas/ConfigCreate](#componentsschemasconfigcreate) |  |
| ConfigPatch | [#/components/schemas/ConfigPatch](#componentsschemasconfigpatch) |  |
| ConfigResponse | [#/components/schemas/ConfigResponse](#componentsschemasconfigresponse) |  |
| ConfigSingleResponse | [#/components/schemas/ConfigSingleResponse](#componentsschemasconfigsingleresponse) |  |
| ConfigPostPatchResponse | [#/components/schemas/ConfigPostPatchResponse](#componentsschemasconfigpostpatchresponse) |  |
| ConfigListResponse | [#/components/schemas/ConfigListResponse](#componentsschemasconfiglistresponse) |  |
| ConfigSectionCreate | [#/components/schemas/ConfigSectionCreate](#componentsschemasconfigsectioncreate) |  |
| ConfigSectionPatch | [#/components/schemas/ConfigSectionPatch](#componentsschemasconfigsectionpatch) |  |
| ConfigSectionResponse | [#/components/schemas/ConfigSectionResponse](#componentsschemasconfigsectionresponse) |  |
| ConfigSectionSingleResponse | [#/components/schemas/ConfigSectionSingleResponse](#componentsschemasconfigsectionsingleresponse) |  |
| ConfigSectionPostPatchResponse | [#/components/schemas/ConfigSectionPostPatchResponse](#componentsschemasconfigsectionpostpatchresponse) |  |
| ConfigSectionListResponse | [#/components/schemas/ConfigSectionListResponse](#componentsschemasconfigsectionlistresponse) |  |
| CrackerBinaryCreate | [#/components/schemas/CrackerBinaryCreate](#componentsschemascrackerbinarycreate) |  |
| CrackerBinaryPatch | [#/components/schemas/CrackerBinaryPatch](#componentsschemascrackerbinarypatch) |  |
| CrackerBinaryResponse | [#/components/schemas/CrackerBinaryResponse](#componentsschemascrackerbinaryresponse) |  |
| CrackerBinarySingleResponse | [#/components/schemas/CrackerBinarySingleResponse](#componentsschemascrackerbinarysingleresponse) |  |
| CrackerBinaryPostPatchResponse | [#/components/schemas/CrackerBinaryPostPatchResponse](#componentsschemascrackerbinarypostpatchresponse) |  |
| CrackerBinaryListResponse | [#/components/schemas/CrackerBinaryListResponse](#componentsschemascrackerbinarylistresponse) |  |
| CrackerBinaryTypeCreate | [#/components/schemas/CrackerBinaryTypeCreate](#componentsschemascrackerbinarytypecreate) |  |
| CrackerBinaryTypePatch | [#/components/schemas/CrackerBinaryTypePatch](#componentsschemascrackerbinarytypepatch) |  |
| CrackerBinaryTypeResponse | [#/components/schemas/CrackerBinaryTypeResponse](#componentsschemascrackerbinarytyperesponse) |  |
| CrackerBinaryTypeSingleResponse | [#/components/schemas/CrackerBinaryTypeSingleResponse](#componentsschemascrackerbinarytypesingleresponse) |  |
| CrackerBinaryTypePostPatchResponse | [#/components/schemas/CrackerBinaryTypePostPatchResponse](#componentsschemascrackerbinarytypepostpatchresponse) |  |
| CrackerBinaryTypeListResponse | [#/components/schemas/CrackerBinaryTypeListResponse](#componentsschemascrackerbinarytypelistresponse) |  |
| FileCreate | [#/components/schemas/FileCreate](#componentsschemasfilecreate) |  |
| FilePatch | [#/components/schemas/FilePatch](#componentsschemasfilepatch) |  |
| FileResponse | [#/components/schemas/FileResponse](#componentsschemasfileresponse) |  |
| FileSingleResponse | [#/components/schemas/FileSingleResponse](#componentsschemasfilesingleresponse) |  |
| FilePostPatchResponse | [#/components/schemas/FilePostPatchResponse](#componentsschemasfilepostpatchresponse) |  |
| FileListResponse | [#/components/schemas/FileListResponse](#componentsschemasfilelistresponse) |  |
| RightGroupCreate | [#/components/schemas/RightGroupCreate](#componentsschemasrightgroupcreate) |  |
| RightGroupPatch | [#/components/schemas/RightGroupPatch](#componentsschemasrightgrouppatch) |  |
| RightGroupResponse | [#/components/schemas/RightGroupResponse](#componentsschemasrightgroupresponse) |  |
| RightGroupSingleResponse | [#/components/schemas/RightGroupSingleResponse](#componentsschemasrightgroupsingleresponse) |  |
| RightGroupPostPatchResponse | [#/components/schemas/RightGroupPostPatchResponse](#componentsschemasrightgrouppostpatchresponse) |  |
| RightGroupListResponse | [#/components/schemas/RightGroupListResponse](#componentsschemasrightgrouplistresponse) |  |
| HashCreate | [#/components/schemas/HashCreate](#componentsschemashashcreate) |  |
| HashPatch | [#/components/schemas/HashPatch](#componentsschemashashpatch) |  |
| HashResponse | [#/components/schemas/HashResponse](#componentsschemashashresponse) |  |
| HashSingleResponse | [#/components/schemas/HashSingleResponse](#componentsschemashashsingleresponse) |  |
| HashPostPatchResponse | [#/components/schemas/HashPostPatchResponse](#componentsschemashashpostpatchresponse) |  |
| HashListResponse | [#/components/schemas/HashListResponse](#componentsschemashashlistresponse) |  |
| HashlistCreate | [#/components/schemas/HashlistCreate](#componentsschemashashlistcreate) |  |
| HashlistPatch | [#/components/schemas/HashlistPatch](#componentsschemashashlistpatch) |  |
| HashlistResponse | [#/components/schemas/HashlistResponse](#componentsschemashashlistresponse) |  |
| HashlistSingleResponse | [#/components/schemas/HashlistSingleResponse](#componentsschemashashlistsingleresponse) |  |
| HashlistPostPatchResponse | [#/components/schemas/HashlistPostPatchResponse](#componentsschemashashlistpostpatchresponse) |  |
| HashlistListResponse | [#/components/schemas/HashlistListResponse](#componentsschemashashlistlistresponse) |  |
| HashTypeCreate | [#/components/schemas/HashTypeCreate](#componentsschemashashtypecreate) |  |
| HashTypePatch | [#/components/schemas/HashTypePatch](#componentsschemashashtypepatch) |  |
| HashTypeResponse | [#/components/schemas/HashTypeResponse](#componentsschemashashtyperesponse) |  |
| HashTypeSingleResponse | [#/components/schemas/HashTypeSingleResponse](#componentsschemashashtypesingleresponse) |  |
| HashTypePostPatchResponse | [#/components/schemas/HashTypePostPatchResponse](#componentsschemashashtypepostpatchresponse) |  |
| HashTypeListResponse | [#/components/schemas/HashTypeListResponse](#componentsschemashashtypelistresponse) |  |
| HealthCheckAgentCreate | [#/components/schemas/HealthCheckAgentCreate](#componentsschemashealthcheckagentcreate) |  |
| HealthCheckAgentPatch | [#/components/schemas/HealthCheckAgentPatch](#componentsschemashealthcheckagentpatch) |  |
| HealthCheckAgentResponse | [#/components/schemas/HealthCheckAgentResponse](#componentsschemashealthcheckagentresponse) |  |
| HealthCheckAgentSingleResponse | [#/components/schemas/HealthCheckAgentSingleResponse](#componentsschemashealthcheckagentsingleresponse) |  |
| HealthCheckAgentPostPatchResponse | [#/components/schemas/HealthCheckAgentPostPatchResponse](#componentsschemashealthcheckagentpostpatchresponse) |  |
| HealthCheckAgentListResponse | [#/components/schemas/HealthCheckAgentListResponse](#componentsschemashealthcheckagentlistresponse) |  |
| HealthCheckCreate | [#/components/schemas/HealthCheckCreate](#componentsschemashealthcheckcreate) |  |
| HealthCheckPatch | [#/components/schemas/HealthCheckPatch](#componentsschemashealthcheckpatch) |  |
| HealthCheckResponse | [#/components/schemas/HealthCheckResponse](#componentsschemashealthcheckresponse) |  |
| HealthCheckSingleResponse | [#/components/schemas/HealthCheckSingleResponse](#componentsschemashealthchecksingleresponse) |  |
| HealthCheckPostPatchResponse | [#/components/schemas/HealthCheckPostPatchResponse](#componentsschemashealthcheckpostpatchresponse) |  |
| HealthCheckListResponse | [#/components/schemas/HealthCheckListResponse](#componentsschemashealthchecklistresponse) |  |
| LogEntryCreate | [#/components/schemas/LogEntryCreate](#componentsschemaslogentrycreate) |  |
| LogEntryPatch | [#/components/schemas/LogEntryPatch](#componentsschemaslogentrypatch) |  |
| LogEntryResponse | [#/components/schemas/LogEntryResponse](#componentsschemaslogentryresponse) |  |
| LogEntrySingleResponse | [#/components/schemas/LogEntrySingleResponse](#componentsschemaslogentrysingleresponse) |  |
| LogEntryPostPatchResponse | [#/components/schemas/LogEntryPostPatchResponse](#componentsschemaslogentrypostpatchresponse) |  |
| LogEntryListResponse | [#/components/schemas/LogEntryListResponse](#componentsschemaslogentrylistresponse) |  |
| NotificationSettingCreate | [#/components/schemas/NotificationSettingCreate](#componentsschemasnotificationsettingcreate) |  |
| NotificationSettingPatch | [#/components/schemas/NotificationSettingPatch](#componentsschemasnotificationsettingpatch) |  |
| NotificationSettingResponse | [#/components/schemas/NotificationSettingResponse](#componentsschemasnotificationsettingresponse) |  |
| NotificationSettingSingleResponse | [#/components/schemas/NotificationSettingSingleResponse](#componentsschemasnotificationsettingsingleresponse) |  |
| NotificationSettingPostPatchResponse | [#/components/schemas/NotificationSettingPostPatchResponse](#componentsschemasnotificationsettingpostpatchresponse) |  |
| NotificationSettingListResponse | [#/components/schemas/NotificationSettingListResponse](#componentsschemasnotificationsettinglistresponse) |  |
| PreprocessorCreate | [#/components/schemas/PreprocessorCreate](#componentsschemaspreprocessorcreate) |  |
| PreprocessorPatch | [#/components/schemas/PreprocessorPatch](#componentsschemaspreprocessorpatch) |  |
| PreprocessorResponse | [#/components/schemas/PreprocessorResponse](#componentsschemaspreprocessorresponse) |  |
| PreprocessorSingleResponse | [#/components/schemas/PreprocessorSingleResponse](#componentsschemaspreprocessorsingleresponse) |  |
| PreprocessorPostPatchResponse | [#/components/schemas/PreprocessorPostPatchResponse](#componentsschemaspreprocessorpostpatchresponse) |  |
| PreprocessorListResponse | [#/components/schemas/PreprocessorListResponse](#componentsschemaspreprocessorlistresponse) |  |
| PretaskCreate | [#/components/schemas/PretaskCreate](#componentsschemaspretaskcreate) |  |
| PretaskPatch | [#/components/schemas/PretaskPatch](#componentsschemaspretaskpatch) |  |
| PretaskResponse | [#/components/schemas/PretaskResponse](#componentsschemaspretaskresponse) |  |
| PretaskSingleResponse | [#/components/schemas/PretaskSingleResponse](#componentsschemaspretasksingleresponse) |  |
| PretaskPostPatchResponse | [#/components/schemas/PretaskPostPatchResponse](#componentsschemaspretaskpostpatchresponse) |  |
| PretaskListResponse | [#/components/schemas/PretaskListResponse](#componentsschemaspretasklistresponse) |  |
| SpeedCreate | [#/components/schemas/SpeedCreate](#componentsschemasspeedcreate) |  |
| SpeedPatch | [#/components/schemas/SpeedPatch](#componentsschemasspeedpatch) |  |
| SpeedResponse | [#/components/schemas/SpeedResponse](#componentsschemasspeedresponse) |  |
| SpeedSingleResponse | [#/components/schemas/SpeedSingleResponse](#componentsschemasspeedsingleresponse) |  |
| SpeedPostPatchResponse | [#/components/schemas/SpeedPostPatchResponse](#componentsschemasspeedpostpatchresponse) |  |
| SpeedListResponse | [#/components/schemas/SpeedListResponse](#componentsschemasspeedlistresponse) |  |
| SupertaskCreate | [#/components/schemas/SupertaskCreate](#componentsschemassupertaskcreate) |  |
| SupertaskPatch | [#/components/schemas/SupertaskPatch](#componentsschemassupertaskpatch) |  |
| SupertaskResponse | [#/components/schemas/SupertaskResponse](#componentsschemassupertaskresponse) |  |
| SupertaskSingleResponse | [#/components/schemas/SupertaskSingleResponse](#componentsschemassupertasksingleresponse) |  |
| SupertaskPostPatchResponse | [#/components/schemas/SupertaskPostPatchResponse](#componentsschemassupertaskpostpatchresponse) |  |
| SupertaskListResponse | [#/components/schemas/SupertaskListResponse](#componentsschemassupertasklistresponse) |  |
| TaskCreate | [#/components/schemas/TaskCreate](#componentsschemastaskcreate) |  |
| TaskPatch | [#/components/schemas/TaskPatch](#componentsschemastaskpatch) |  |
| TaskResponse | [#/components/schemas/TaskResponse](#componentsschemastaskresponse) |  |
| TaskSingleResponse | [#/components/schemas/TaskSingleResponse](#componentsschemastasksingleresponse) |  |
| TaskPostPatchResponse | [#/components/schemas/TaskPostPatchResponse](#componentsschemastaskpostpatchresponse) |  |
| TaskListResponse | [#/components/schemas/TaskListResponse](#componentsschemastasklistresponse) |  |
| TaskWrapperCreate | [#/components/schemas/TaskWrapperCreate](#componentsschemastaskwrappercreate) |  |
| TaskWrapperPatch | [#/components/schemas/TaskWrapperPatch](#componentsschemastaskwrapperpatch) |  |
| TaskWrapperResponse | [#/components/schemas/TaskWrapperResponse](#componentsschemastaskwrapperresponse) |  |
| TaskWrapperSingleResponse | [#/components/schemas/TaskWrapperSingleResponse](#componentsschemastaskwrappersingleresponse) |  |
| TaskWrapperPostPatchResponse | [#/components/schemas/TaskWrapperPostPatchResponse](#componentsschemastaskwrapperpostpatchresponse) |  |
| TaskWrapperListResponse | [#/components/schemas/TaskWrapperListResponse](#componentsschemastaskwrapperlistresponse) |  |
| UserCreate | [#/components/schemas/UserCreate](#componentsschemasusercreate) |  |
| UserPatch | [#/components/schemas/UserPatch](#componentsschemasuserpatch) |  |
| UserResponse | [#/components/schemas/UserResponse](#componentsschemasuserresponse) |  |
| UserSingleResponse | [#/components/schemas/UserSingleResponse](#componentsschemasusersingleresponse) |  |
| UserPostPatchResponse | [#/components/schemas/UserPostPatchResponse](#componentsschemasuserpostpatchresponse) |  |
| UserListResponse | [#/components/schemas/UserListResponse](#componentsschemasuserlistresponse) |  |
| RegVoucherCreate | [#/components/schemas/RegVoucherCreate](#componentsschemasregvouchercreate) |  |
| RegVoucherPatch | [#/components/schemas/RegVoucherPatch](#componentsschemasregvoucherpatch) |  |
| RegVoucherResponse | [#/components/schemas/RegVoucherResponse](#componentsschemasregvoucherresponse) |  |
| RegVoucherSingleResponse | [#/components/schemas/RegVoucherSingleResponse](#componentsschemasregvouchersingleresponse) |  |
| RegVoucherPostPatchResponse | [#/components/schemas/RegVoucherPostPatchResponse](#componentsschemasregvoucherpostpatchresponse) |  |
| RegVoucherListResponse | [#/components/schemas/RegVoucherListResponse](#componentsschemasregvoucherlistresponse) |  |
| Token | [#/components/schemas/Token](#componentsschemastoken) |  |
| TokenRequest | [#/components/schemas/TokenRequest](#componentsschemastokenrequest) |  |
| ObjectRequest | [#/components/schemas/ObjectRequest](#componentsschemasobjectrequest) |  |
| ObjectListRequest | [#/components/schemas/ObjectListRequest](#componentsschemasobjectlistrequest) |  |
| bearerAuth | [#/components/securitySchemes/bearerAuth](#componentssecurityschemesbearerauth) | JWT Authorization header using the Bearer scheme. |
| basicAuth | [#/components/securitySchemes/basicAuth](#componentssecurityschemesbasicauth) | Basic Authorization header. |

## Path Details

***

### [GET]/api/v2/ui/accessgroups

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/accessgroups?page[size]=25
    first?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/accessgroups

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      groupName?: string
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/accessgroups/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/accessgroups?page[size]=25
    first?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/accessgroups/{id:[0-9]+}/{relation:userMembers}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/accessgroups?page[size]=25
    first?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:userMembers}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/accessgroups?page[size]=25
    first?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:userMembers}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      groupName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:userMembers}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:userMembers}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/accessgroups/{id:[0-9]+}/{relation:agentMembers}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/accessgroups?page[size]=25
    first?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:agentMembers}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/accessgroups?page[size]=25
    first?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:agentMembers}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      groupName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:agentMembers}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/accessgroups/{id:[0-9]+}/relationships/{relation:agentMembers}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/accessgroups/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/accessgroups?page[size]=25
    first?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/accessgroups/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      groupName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/accessgroups/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agentassignments

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentassignments?page[size]=25
    first?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/agentassignments

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      taskId?: integer
      agentId?: integer
      benchmark?: string
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/agentassignments/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentassignments?page[size]=25
    first?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/agentassignments/{id:[0-9]+}/{relation:agent}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentassignments?page[size]=25
    first?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agentassignments/{id:[0-9]+}/relationships/{relation:agent}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentassignments?page[size]=25
    first?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/agentassignments/{id:[0-9]+}/relationships/{relation:agent}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      agentId?: integer
      taskId?: integer
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agentassignments/{id:[0-9]+}/{relation:task}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentassignments?page[size]=25
    first?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agentassignments/{id:[0-9]+}/relationships/{relation:task}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentassignments?page[size]=25
    first?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/agentassignments/{id:[0-9]+}/relationships/{relation:task}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      agentId?: integer
      taskId?: integer
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agentassignments/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentassignments?page[size]=25
    first?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/agentassignments/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agentbinaries

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentbinaries?page[size]=25
    first?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AgentBinary
  data: {
    type?: string
    version?: string
    operatingSystems?: string
    filename?: string
    updateTrack?: string
    updateAvailable?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/agentbinaries

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      type?: string
      version?: string
      operatingSystems?: string
      filename?: string
      updateTrack?: string
      updateAvailable?: string
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: AgentBinary
  data: {
    type?: string
    version?: string
    operatingSystems?: string
    filename?: string
    updateTrack?: string
    updateAvailable?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/agentbinaries/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentbinaries?page[size]=25
    first?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AgentBinary
  data: {
    type?: string
    version?: string
    operatingSystems?: string
    filename?: string
    updateTrack?: string
    updateAvailable?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/agentbinaries/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentbinaries?page[size]=25
    first?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AgentBinary
  data: {
    type?: string
    version?: string
    operatingSystems?: string
    filename?: string
    updateTrack?: string
    updateAvailable?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/agentbinaries/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      filename?: string
      operatingSystems?: string
      type?: string
      updateTrack?: string
      version?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: AgentBinary
  data: {
    type?: string
    version?: string
    operatingSystems?: string
    filename?: string
    updateTrack?: string
    updateAvailable?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/agentbinaries/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agents

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agents?page[size]=25
    first?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/agents/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agents?page[size]=25
    first?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/agents/{id:[0-9]+}/{relation:accessGroups}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agents?page[size]=25
    first?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:accessGroups}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agents?page[size]=25
    first?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:accessGroups}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      agentName?: string
      clientSignature?: string
      cmdPars?: string
      cpuOnly?: boolean
      devices?: string
      ignoreErrors?: enum[0, 1, 2]
      isActive?: boolean
      isTrusted?: boolean
      os?: integer
      token?: string
      uid?: string
      userId?: integer
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:accessGroups}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:accessGroups}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agents/{id:[0-9]+}/{relation:agentStats}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agents?page[size]=25
    first?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:agentStats}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agents?page[size]=25
    first?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:agentStats}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      agentName?: string
      clientSignature?: string
      cmdPars?: string
      cpuOnly?: boolean
      devices?: string
      ignoreErrors?: enum[0, 1, 2]
      isActive?: boolean
      isTrusted?: boolean
      os?: integer
      token?: string
      uid?: string
      userId?: integer
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:agentStats}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/agents/{id:[0-9]+}/relationships/{relation:agentStats}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agents/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agents?page[size]=25
    first?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/agents/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      agentName?: string
      clientSignature?: string
      cmdPars?: string
      cpuOnly?: boolean
      devices?: string
      ignoreErrors?: enum[0, 1, 2]
      isActive?: boolean
      isTrusted?: boolean
      os?: integer
      token?: string
      uid?: string
      userId?: integer
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/agents/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/agentstats

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentstats?page[size]=25
    first?: string //default: /api/v2/ui/agentstats?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentstats?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentstats?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentstats?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AgentStat
  data: {
    agentId?: integer
    statType?: integer
    time?: integer
[]
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/agentstats/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentstats?page[size]=25
    first?: string //default: /api/v2/ui/agentstats?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentstats?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentstats?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentstats?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AgentStat
  data: {
    agentId?: integer
    statType?: integer
    time?: integer
[]
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/agentstats/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentstats?page[size]=25
    first?: string //default: /api/v2/ui/agentstats?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentstats?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentstats?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentstats?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AgentStat
  data: {
    agentId?: integer
    statType?: integer
    time?: integer
[]
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/agentstats/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/chunks

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/chunks?page[size]=25
    first?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/chunks/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/chunks?page[size]=25
    first?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/chunks/{id:[0-9]+}/{relation:agent}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/chunks?page[size]=25
    first?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/chunks/{id:[0-9]+}/relationships/{relation:agent}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/chunks?page[size]=25
    first?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/chunks/{id:[0-9]+}/relationships/{relation:agent}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/chunks/{id:[0-9]+}/{relation:task}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/chunks?page[size]=25
    first?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/chunks/{id:[0-9]+}/relationships/{relation:task}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/chunks?page[size]=25
    first?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/chunks/{id:[0-9]+}/relationships/{relation:task}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/chunks/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/chunks?page[size]=25
    first?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/configs

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/configs?page[size]=25
    first?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Config
  data: {
    configSectionId?: integer
    item?: string
    value?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/configs/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/configs?page[size]=25
    first?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Config
  data: {
    configSectionId?: integer
    item?: string
    value?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/configs/{id:[0-9]+}/{relation:configSection}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/configs?page[size]=25
    first?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Config
  data: {
    configSectionId?: integer
    item?: string
    value?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/configs/{id:[0-9]+}/relationships/{relation:configSection}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/configs?page[size]=25
    first?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Config
  data: {
    configSectionId?: integer
    item?: string
    value?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/configs/{id:[0-9]+}/relationships/{relation:configSection}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      configSectionId?: integer
      item?: string
      value?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Config
  data: {
    configSectionId?: integer
    item?: string
    value?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/configs/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/configs?page[size]=25
    first?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Config
  data: {
    configSectionId?: integer
    item?: string
    value?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/configs/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      configSectionId?: integer
      item?: string
      value?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Config
  data: {
    configSectionId?: integer
    item?: string
    value?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/configsections

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/configsections?page[size]=25
    first?: string //default: /api/v2/ui/configsections?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/configsections?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/configsections?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/configsections?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: ConfigSection
  data: {
    sectionName?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/configsections/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/configsections?page[size]=25
    first?: string //default: /api/v2/ui/configsections?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/configsections?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/configsections?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/configsections?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: ConfigSection
  data: {
    sectionName?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/configsections/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/configsections?page[size]=25
    first?: string //default: /api/v2/ui/configsections?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/configsections?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/configsections?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/configsections?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: ConfigSection
  data: {
    sectionName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/crackers

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackers?page[size]=25
    first?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/crackers

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      crackerBinaryTypeId?: integer
      version?: string
      downloadUrl?: string
      binaryName?: string
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/crackers/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackers?page[size]=25
    first?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/crackers/{id:[0-9]+}/{relation:crackerBinaryType}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackers?page[size]=25
    first?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:crackerBinaryType}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackers?page[size]=25
    first?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:crackerBinaryType}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      binaryName?: string
      crackerBinaryTypeId?: integer
      downloadUrl?: string
      version?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/crackers/{id:[0-9]+}/{relation:tasks}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackers?page[size]=25
    first?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:tasks}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackers?page[size]=25
    first?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      binaryName?: string
      crackerBinaryTypeId?: integer
      downloadUrl?: string
      version?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/crackers/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/crackers/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackers?page[size]=25
    first?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/crackers/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      binaryName?: string
      crackerBinaryTypeId?: integer
      downloadUrl?: string
      version?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/crackers/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/crackertypes

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackertypes?page[size]=25
    first?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/crackertypes

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      typeName?: string
      isChunkingAvailable?: boolean
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/crackertypes/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackertypes?page[size]=25
    first?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/crackertypes/{id:[0-9]+}/{relation:crackerVersions}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackertypes?page[size]=25
    first?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:crackerVersions}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackertypes?page[size]=25
    first?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:crackerVersions}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      isChunkingAvailable?: boolean
      typeName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:crackerVersions}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:crackerVersions}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/crackertypes/{id:[0-9]+}/{relation:tasks}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackertypes?page[size]=25
    first?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:tasks}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackertypes?page[size]=25
    first?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      isChunkingAvailable?: boolean
      typeName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/crackertypes/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/crackertypes/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackertypes?page[size]=25
    first?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/crackertypes/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      isChunkingAvailable?: boolean
      typeName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/crackertypes/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/files

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/files?page[size]=25
    first?: string //default: /api/v2/ui/files?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/files?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/files?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/files?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/files

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      sourceType?: string
      sourceData?: string
      filename?: string
      size?: integer
      isSecret?: boolean
      fileType?: integer
      accessGroupId?: integer
      lineCount?: integer
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/files/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/files?page[size]=25
    first?: string //default: /api/v2/ui/files?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/files?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/files?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/files?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/files/{id:[0-9]+}/{relation:accessGroup}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/files?page[size]=25
    first?: string //default: /api/v2/ui/files?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/files?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/files?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/files?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/files/{id:[0-9]+}/relationships/{relation:accessGroup}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/files?page[size]=25
    first?: string //default: /api/v2/ui/files?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/files?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/files?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/files?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/files/{id:[0-9]+}/relationships/{relation:accessGroup}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      fileType?: integer
      filename?: string
      isSecret?: boolean
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/files/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/files?page[size]=25
    first?: string //default: /api/v2/ui/files?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/files?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/files?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/files?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/files/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      fileType?: integer
      filename?: string
      isSecret?: boolean
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/files/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/globalpermissiongroups

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25
    first?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/globalpermissiongroups

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      name?: string
      permissions: {
      }
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/globalpermissiongroups/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25
    first?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/globalpermissiongroups/{id:[0-9]+}/{relation:userMembers}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25
    first?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/globalpermissiongroups/{id:[0-9]+}/relationships/{relation:userMembers}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25
    first?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/globalpermissiongroups/{id:[0-9]+}/relationships/{relation:userMembers}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      name?: string
      permissions: {
      }
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/globalpermissiongroups/{id:[0-9]+}/relationships/{relation:userMembers}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/globalpermissiongroups/{id:[0-9]+}/relationships/{relation:userMembers}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/globalpermissiongroups/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25
    first?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/globalpermissiongroups/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      name?: string
      permissions: {
      }
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/globalpermissiongroups/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashes

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashes?page[size]=25
    first?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/hashes/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashes?page[size]=25
    first?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/hashes/{id:[0-9]+}/{relation:chunk}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashes?page[size]=25
    first?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashes/{id:[0-9]+}/relationships/{relation:chunk}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashes?page[size]=25
    first?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/hashes/{id:[0-9]+}/relationships/{relation:chunk}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      chunkId?: integer
      crackPos?: integer
      hash?: string
      hashlistId?: integer
      isCracked?: boolean
      plaintext?: string
      salt?: string
      timeCracked?: integer
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashes/{id:[0-9]+}/{relation:hashlist}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashes?page[size]=25
    first?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashes/{id:[0-9]+}/relationships/{relation:hashlist}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashes?page[size]=25
    first?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/hashes/{id:[0-9]+}/relationships/{relation:hashlist}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      chunkId?: integer
      crackPos?: integer
      hash?: string
      hashlistId?: integer
      isCracked?: boolean
      plaintext?: string
      salt?: string
      timeCracked?: integer
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashes/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashes?page[size]=25
    first?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/hashlists

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      hashlistSeperator?: string
      sourceType?: string
      sourceData?: string
      name?: string
      format?: enum[0, 1, 2, 3]
      hashTypeId?: integer
      hashCount?: integer
      separator?: string
      cracked?: integer
      isSecret?: boolean
      isHexSalt?: boolean
      isSalted?: boolean
      accessGroupId?: integer
      notes?: string
      useBrain?: boolean
      brainFeatures?: integer
      isArchived?: boolean
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/hashlists/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}/{relation:accessGroup}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:accessGroup}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:accessGroup}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      isSecret?: boolean
      name?: string
      notes?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}/{relation:hashType}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashType}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashType}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      isSecret?: boolean
      name?: string
      notes?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}/{relation:hashes}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashes}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashes}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      isSecret?: boolean
      name?: string
      notes?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashes}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashes}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}/{relation:hashlists}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashlists}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashlists}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      isSecret?: boolean
      name?: string
      notes?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashlists}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:hashlists}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}/{relation:tasks}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:tasks}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      isSecret?: boolean
      name?: string
      notes?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/hashlists/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashlists/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/hashlists/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      isSecret?: boolean
      name?: string
      notes?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/hashlists/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/hashtypes

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashtypes?page[size]=25
    first?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HashType
  data: {
    description?: string
    isSalted?: boolean
    isSlowHash?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/hashtypes

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      description?: string
      isSalted?: boolean
      isSlowHash?: boolean
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HashType
  data: {
    description?: string
    isSalted?: boolean
    isSlowHash?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/hashtypes/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashtypes?page[size]=25
    first?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HashType
  data: {
    description?: string
    isSalted?: boolean
    isSlowHash?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/hashtypes/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashtypes?page[size]=25
    first?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HashType
  data: {
    description?: string
    isSalted?: boolean
    isSlowHash?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/hashtypes/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      description?: string
      isSalted?: boolean
      isSlowHash?: boolean
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HashType
  data: {
    description?: string
    isSalted?: boolean
    isSlowHash?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/hashtypes/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/healthcheckagents

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthcheckagents?page[size]=25
    first?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/healthcheckagents/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthcheckagents?page[size]=25
    first?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/healthcheckagents/{id:[0-9]+}/{relation:agent}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthcheckagents?page[size]=25
    first?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/healthcheckagents/{id:[0-9]+}/relationships/{relation:agent}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthcheckagents?page[size]=25
    first?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/healthcheckagents/{id:[0-9]+}/relationships/{relation:agent}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/healthcheckagents/{id:[0-9]+}/{relation:healthCheck}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthcheckagents?page[size]=25
    first?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/healthcheckagents/{id:[0-9]+}/relationships/{relation:healthCheck}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthcheckagents?page[size]=25
    first?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/healthcheckagents/{id:[0-9]+}/relationships/{relation:healthCheck}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/healthcheckagents/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthcheckagents?page[size]=25
    first?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/healthchecks

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthchecks?page[size]=25
    first?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/healthchecks

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      time?: integer
      status?: integer
      checkType?: integer
      hashtypeId?: integer
      crackerBinaryId?: integer
      expectedCracks?: integer
      attackCmd?: string
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/healthchecks/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthchecks?page[size]=25
    first?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/healthchecks/{id:[0-9]+}/{relation:crackerBinary}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthchecks?page[size]=25
    first?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:crackerBinary}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthchecks?page[size]=25
    first?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:crackerBinary}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      checkType?: integer
      crackerBinaryId?: integer
      hashtypeId?: integer
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/healthchecks/{id:[0-9]+}/{relation:healthCheckAgents}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthchecks?page[size]=25
    first?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:healthCheckAgents}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthchecks?page[size]=25
    first?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:healthCheckAgents}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      checkType?: integer
      crackerBinaryId?: integer
      hashtypeId?: integer
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:healthCheckAgents}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/healthchecks/{id:[0-9]+}/relationships/{relation:healthCheckAgents}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/healthchecks/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthchecks?page[size]=25
    first?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/healthchecks/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      checkType?: integer
      crackerBinaryId?: integer
      hashtypeId?: integer
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/healthchecks/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/logentries

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/logentries?page[size]=25
    first?: string //default: /api/v2/ui/logentries?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/logentries?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/logentries?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/logentries?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: LogEntry
  data: {
    issuer?: string
    issuerId?: string
    level?: string
    message?: string
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/logentries

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      issuer?: string
      issuerId?: string
      level?: string
      message?: string
      time?: integer
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: LogEntry
  data: {
    issuer?: string
    issuerId?: string
    level?: string
    message?: string
    time?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/logentries/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/logentries?page[size]=25
    first?: string //default: /api/v2/ui/logentries?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/logentries?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/logentries?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/logentries?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: LogEntry
  data: {
    issuer?: string
    issuerId?: string
    level?: string
    message?: string
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/logentries/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/logentries?page[size]=25
    first?: string //default: /api/v2/ui/logentries?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/logentries?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/logentries?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/logentries?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: LogEntry
  data: {
    issuer?: string
    issuerId?: string
    level?: string
    message?: string
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/logentries/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: LogEntry
  data: {
    issuer?: string
    issuerId?: string
    level?: string
    message?: string
    time?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/logentries/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/notifications

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/notifications?page[size]=25
    first?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/notifications

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      actionFilter?: string
      action?: string
      objectId?: integer
      notification?: string
      userId?: integer
      receiver?: string
      isActive?: boolean
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/notifications/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/notifications?page[size]=25
    first?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/notifications/{id:[0-9]+}/{relation:user}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/notifications?page[size]=25
    first?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/notifications/{id:[0-9]+}/relationships/{relation:user}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/notifications?page[size]=25
    first?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/notifications/{id:[0-9]+}/relationships/{relation:user}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      action?: string
      isActive?: boolean
      notification?: string
      receiver?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/notifications/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/notifications?page[size]=25
    first?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/notifications/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      action?: string
      isActive?: boolean
      notification?: string
      receiver?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/notifications/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/preprocessors

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/preprocessors?page[size]=25
    first?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Preprocessor
  data: {
    name?: string
    url?: string
    binaryName?: string
    keyspaceCommand?: string
    skipCommand?: string
    limitCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/preprocessors

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      name?: string
      url?: string
      binaryName?: string
      keyspaceCommand?: string
      skipCommand?: string
      limitCommand?: string
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Preprocessor
  data: {
    name?: string
    url?: string
    binaryName?: string
    keyspaceCommand?: string
    skipCommand?: string
    limitCommand?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/preprocessors/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/preprocessors?page[size]=25
    first?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Preprocessor
  data: {
    name?: string
    url?: string
    binaryName?: string
    keyspaceCommand?: string
    skipCommand?: string
    limitCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/preprocessors/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/preprocessors?page[size]=25
    first?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Preprocessor
  data: {
    name?: string
    url?: string
    binaryName?: string
    keyspaceCommand?: string
    skipCommand?: string
    limitCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/preprocessors/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      binaryName?: string
      keyspaceCommand?: string
      limitCommand?: string
      name?: string
      skipCommand?: string
      url?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Preprocessor
  data: {
    name?: string
    url?: string
    binaryName?: string
    keyspaceCommand?: string
    skipCommand?: string
    limitCommand?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/preprocessors/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/pretasks

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/pretasks?page[size]=25
    first?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/pretasks

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
[]
      taskName?: string
      attackCmd?: string
      chunkTime?: integer
      statusTimer?: integer
      color?: string
      isSmall?: boolean
      isCpuTask?: boolean
      useNewBench?: boolean
      priority?: integer
      maxAgents?: integer
      isMaskImport?: boolean
      crackerBinaryTypeId?: integer
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/pretasks/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/pretasks?page[size]=25
    first?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/pretasks/{id:[0-9]+}/{relation:pretaskFiles}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/pretasks?page[size]=25
    first?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/pretasks/{id:[0-9]+}/relationships/{relation:pretaskFiles}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/pretasks?page[size]=25
    first?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/pretasks/{id:[0-9]+}/relationships/{relation:pretaskFiles}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      crackerBinaryTypeId?: integer
      isCpuTask?: boolean
      isMaskImport?: boolean
      isSmall?: boolean
      maxAgents?: integer
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/pretasks/{id:[0-9]+}/relationships/{relation:pretaskFiles}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/pretasks/{id:[0-9]+}/relationships/{relation:pretaskFiles}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/pretasks/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/pretasks?page[size]=25
    first?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/pretasks/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      crackerBinaryTypeId?: integer
      isCpuTask?: boolean
      isMaskImport?: boolean
      isSmall?: boolean
      maxAgents?: integer
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/pretasks/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/speeds

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/speeds?page[size]=25
    first?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/speeds/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/speeds?page[size]=25
    first?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/speeds/{id:[0-9]+}/{relation:agent}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/speeds?page[size]=25
    first?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/speeds/{id:[0-9]+}/relationships/{relation:agent}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/speeds?page[size]=25
    first?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/speeds/{id:[0-9]+}/relationships/{relation:agent}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/speeds/{id:[0-9]+}/{relation:task}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/speeds?page[size]=25
    first?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/speeds/{id:[0-9]+}/relationships/{relation:task}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/speeds?page[size]=25
    first?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/speeds/{id:[0-9]+}/relationships/{relation:task}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/speeds/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/speeds?page[size]=25
    first?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/supertasks

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/supertasks?page[size]=25
    first?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/supertasks

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
[]
      supertaskName?: string
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/supertasks/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/supertasks?page[size]=25
    first?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/supertasks/{id:[0-9]+}/{relation:pretasks}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/supertasks?page[size]=25
    first?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/supertasks/{id:[0-9]+}/relationships/{relation:pretasks}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/supertasks?page[size]=25
    first?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/supertasks/{id:[0-9]+}/relationships/{relation:pretasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      supertaskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/supertasks/{id:[0-9]+}/relationships/{relation:pretasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/supertasks/{id:[0-9]+}/relationships/{relation:pretasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/supertasks/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/supertasks?page[size]=25
    first?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/supertasks/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      supertaskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/supertasks/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/tasks

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      hashlistId?: integer
[]
      taskName?: string
      attackCmd?: string
      chunkTime?: integer
      statusTimer?: integer
      keyspace?: integer
      keyspaceProgress?: integer
      priority?: integer
      maxAgents?: integer
      color?: string
      isSmall?: boolean
      isCpuTask?: boolean
      useNewBench?: boolean
      skipKeyspace?: integer
      crackerBinaryId?: integer
      crackerBinaryTypeId?: integer
      taskWrapperId?: integer
      isArchived?: boolean
      notes?: string
      staticChunks?: integer
      chunkSize?: integer
      forcePipe?: boolean
      preprocessorId?: integer
      preprocessorCommand?: string
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/tasks/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/{relation:crackerBinary}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:crackerBinary}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:crackerBinary}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      isArchived?: boolean
      isCpuTask?: boolean
      isSmall?: boolean
      maxAgents?: integer
      notes?: string
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/{relation:crackerBinaryType}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:crackerBinaryType}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:crackerBinaryType}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      isArchived?: boolean
      isCpuTask?: boolean
      isSmall?: boolean
      maxAgents?: integer
      notes?: string
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/{relation:hashlist}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:hashlist}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:hashlist}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      isArchived?: boolean
      isCpuTask?: boolean
      isSmall?: boolean
      maxAgents?: integer
      notes?: string
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/{relation:assignedAgents}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:assignedAgents}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:assignedAgents}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      isArchived?: boolean
      isCpuTask?: boolean
      isSmall?: boolean
      maxAgents?: integer
      notes?: string
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:assignedAgents}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:assignedAgents}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/{relation:files}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:files}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:files}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      isArchived?: boolean
      isCpuTask?: boolean
      isSmall?: boolean
      maxAgents?: integer
      notes?: string
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:files}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:files}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/{relation:speeds}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:speeds}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:speeds}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      isArchived?: boolean
      isCpuTask?: boolean
      isSmall?: boolean
      maxAgents?: integer
      notes?: string
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:speeds}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/tasks/{id:[0-9]+}/relationships/{relation:speeds}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/tasks/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/tasks/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      isArchived?: boolean
      isCpuTask?: boolean
      isSmall?: boolean
      maxAgents?: integer
      notes?: string
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/tasks/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/taskwrappers

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/taskwrappers?page[size]=25
    first?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/taskwrappers/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/taskwrappers?page[size]=25
    first?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/taskwrappers/{id:[0-9]+}/{relation:accessGroup}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/taskwrappers?page[size]=25
    first?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:accessGroup}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/taskwrappers?page[size]=25
    first?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:accessGroup}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      maxAgents?: integer
      priority?: integer
      taskWrapperName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/taskwrappers/{id:[0-9]+}/{relation:tasks}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/taskwrappers?page[size]=25
    first?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:tasks}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/taskwrappers?page[size]=25
    first?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      maxAgents?: integer
      priority?: integer
      taskWrapperName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/taskwrappers/{id:[0-9]+}/relationships/{relation:tasks}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/taskwrappers/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/taskwrappers?page[size]=25
    first?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/taskwrappers/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      maxAgents?: integer
      priority?: integer
      taskWrapperName?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/taskwrappers/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/users

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/users?page[size]=25
    first?: string //default: /api/v2/ui/users?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/users?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/users?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/users?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/users

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      name?: string
      email?: string
      passwordHash?: string
      passwordSalt?: string
      isValid?: boolean
      isComputedPassword?: boolean
      lastLoginDate?: integer
      registeredSince?: integer
      sessionLifetime?: integer
      globalPermissionGroupId?: integer
      yubikey?: string
      otp1?: string
      otp2?: string
      otp3?: string
      otp4?: string
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/users/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/users?page[size]=25
    first?: string //default: /api/v2/ui/users?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/users?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/users?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/users?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/users/{id:[0-9]+}/{relation:globalPermissionGroup}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/users?page[size]=25
    first?: string //default: /api/v2/ui/users?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/users?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/users?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/users?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:globalPermissionGroup}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/users?page[size]=25
    first?: string //default: /api/v2/ui/users?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/users?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/users?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/users?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:globalPermissionGroup}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      email?: string
      globalPermissionGroupId?: integer
      isValid?: boolean
      name?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/users/{id:[0-9]+}/{relation:accessGroups}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/users?page[size]=25
    first?: string //default: /api/v2/ui/users?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/users?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/users?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/users?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:accessGroups}

- Description  
GET request for  for a to-one relationship link. Returns the resource record of the object that is part of the specified relation.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/users?page[size]=25
    first?: string //default: /api/v2/ui/users?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/users?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/users?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/users?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:accessGroups}

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      email?: string
      globalPermissionGroupId?: integer
      isValid?: boolean
      name?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:accessGroups}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully created

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/users/{id:[0-9]+}/relationships/{relation:accessGroups}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/users/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/users?page[size]=25
    first?: string //default: /api/v2/ui/users?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/users?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/users?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/users?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/users/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      email?: string
      globalPermissionGroupId?: integer
      isValid?: boolean
      name?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/users/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [GET]/api/v2/ui/vouchers

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/vouchers?page[size]=25
    first?: string //default: /api/v2/ui/vouchers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/vouchers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/vouchers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/vouchers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: RegVoucher
  data: {
    voucher?: string
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [POST]/api/v2/ui/vouchers

- Description  
POST request to create a new object. The request must contain the resource record as data with the attributes of the new object.To add relationships, a relationships object can be added with the resource records of the relations that are part of this object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      voucher?: string
      time?: integer
    }
  }
}
```

#### Responses

- 201 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: RegVoucher
  data: {
    voucher?: string
    time?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/vouchers/count

- Description  
GET many request to retrieve multiple objects.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
page[after]?: integer
```

```ts
page[before]?: integer
```

```ts
page[size]?: integer
```

```ts
filter: {
}
```

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/vouchers?page[size]=25
    first?: string //default: /api/v2/ui/vouchers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/vouchers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/vouchers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/vouchers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: RegVoucher
  data: {
    voucher?: string
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}[]
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

***

### [GET]/api/v2/ui/vouchers/{id:[0-9]+}

- Description  
GET request to retrieve a single object.

- Security  
bearerAuth  

#### Parameters(Query)

```ts
include?: string
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/vouchers?page[size]=25
    first?: string //default: /api/v2/ui/vouchers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/vouchers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/vouchers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/vouchers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: RegVoucher
  data: {
    voucher?: string
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [PATCH]/api/v2/ui/vouchers/{id:[0-9]+}

- Description  
PATCH request to update attributes of a single object.

- Security  
bearerAuth  

#### RequestBody

- application/json

```ts
{
  data: {
    type?: string
    attributes: {
      voucher?: string
    }
  }
}
```

#### Responses

- 200 successful operation

`application/json`

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: RegVoucher
  data: {
    voucher?: string
    time?: integer
  }
}
```

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [DELETE]/api/v2/ui/vouchers/{id:[0-9]+}

- Security  
bearerAuth  

#### RequestBody

- application/json

#### Responses

- 204 successfully deleted

- 400 Invalid request

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

***

### [POST]/api/v2/auth/token

- Security  
basicAuth  

#### RequestBody

- application/json

```ts
string[]
```

#### Responses

- 200 Success

`application/json`

```ts
{
  token?: string
  expires?: integer
}
```

- 401 Authentication failed

`application/json`

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

- 404 Not Found

`application/json`

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

## References

### #/components/schemas/ListResponse

```ts
{
  expand?: string
  page[after]?: integer
  page[before]?: integer
  page[size]?: integer
}
```

### #/components/schemas/ErrorResponse

```ts
{
  title?: string
  type?: string
  status?: integer
}
```

### #/components/schemas/NotFoundResponse

```ts
{
  message?: string
  exception: {
    type?: string
    code?: integer
    message?: string
    file?: string
    line?: integer
  }
}
```

### #/components/schemas/AccessGroupCreate

```ts
{
  data: {
    type?: string
    attributes: {
      groupName?: string
    }
  }
}
```

### #/components/schemas/AccessGroupPatch

```ts
{
  data: {
    type?: string
    attributes: {
      groupName?: string
    }
  }
}
```

### #/components/schemas/AccessGroupResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/accessgroups?page[size]=25
    first?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/accessgroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/AccessGroupSingleResponse

```ts
{
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/AccessGroupPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: AccessGroup
  data: {
    groupName?: string
  }
}
```

### #/components/schemas/AccessGroupListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/AccessGroupResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/AssignmentCreate

```ts
{
  data: {
    type?: string
    attributes: {
      taskId?: integer
      agentId?: integer
      benchmark?: string
    }
  }
}
```

### #/components/schemas/AssignmentPatch

```ts
{
  data: {
    type?: string
    attributes: {
      agentId?: integer
      taskId?: integer
    }
  }
}
```

### #/components/schemas/AssignmentResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentassignments?page[size]=25
    first?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentassignments?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/AssignmentSingleResponse

```ts
{
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/AssignmentPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Assignment
  data: {
    taskId?: integer
    agentId?: integer
    benchmark?: string
  }
}
```

### #/components/schemas/AssignmentListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/AssignmentResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/AgentBinaryCreate

```ts
{
  data: {
    type?: string
    attributes: {
      type?: string
      version?: string
      operatingSystems?: string
      filename?: string
      updateTrack?: string
      updateAvailable?: string
    }
  }
}
```

### #/components/schemas/AgentBinaryPatch

```ts
{
  data: {
    type?: string
    attributes: {
      filename?: string
      operatingSystems?: string
      type?: string
      updateTrack?: string
      version?: string
    }
  }
}
```

### #/components/schemas/AgentBinaryResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentbinaries?page[size]=25
    first?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentbinaries?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AgentBinary
  data: {
    type?: string
    version?: string
    operatingSystems?: string
    filename?: string
    updateTrack?: string
    updateAvailable?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/AgentBinarySingleResponse

```ts
{
  id?: integer
  type?: string //default: AgentBinary
  data: {
    type?: string
    version?: string
    operatingSystems?: string
    filename?: string
    updateTrack?: string
    updateAvailable?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/AgentBinaryPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: AgentBinary
  data: {
    type?: string
    version?: string
    operatingSystems?: string
    filename?: string
    updateTrack?: string
    updateAvailable?: string
  }
}
```

### #/components/schemas/AgentBinaryListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/AgentBinaryResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/AgentCreate

```ts
{
  data: {
    type?: string
    attributes: {
      agentName?: string
      uid?: string
      os?: integer
      devices?: string
      cmdPars?: string
      ignoreErrors?: enum[0, 1, 2]
      isActive?: boolean
      isTrusted?: boolean
      token?: string
      lastAct?: string
      lastTime?: integer
      lastIp?: string
      userId?: integer
      cpuOnly?: boolean
      clientSignature?: string
    }
  }
}
```

### #/components/schemas/AgentPatch

```ts
{
  data: {
    type?: string
    attributes: {
      agentName?: string
      clientSignature?: string
      cmdPars?: string
      cpuOnly?: boolean
      devices?: string
      ignoreErrors?: enum[0, 1, 2]
      isActive?: boolean
      isTrusted?: boolean
      os?: integer
      token?: string
      uid?: string
      userId?: integer
    }
  }
}
```

### #/components/schemas/AgentResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agents?page[size]=25
    first?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/AgentSingleResponse

```ts
{
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/AgentPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Agent
  data: {
    agentName?: string
    uid?: string
    os?: integer
    devices?: string
    cmdPars?: string
    ignoreErrors?: enum[0, 1, 2]
    isActive?: boolean
    isTrusted?: boolean
    token?: string
    lastAct?: string
    lastTime?: integer
    lastIp?: string
    userId?: integer
    cpuOnly?: boolean
    clientSignature?: string
  }
}
```

### #/components/schemas/AgentListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/AgentResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/AgentStatCreate

```ts
{
  data: {
    type?: string
    attributes: {
      agentId?: integer
      statType?: integer
      time?: integer
[]
    }
  }
}
```

### #/components/schemas/AgentStatPatch

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

### #/components/schemas/AgentStatResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/agentstats?page[size]=25
    first?: string //default: /api/v2/ui/agentstats?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/agentstats?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/agentstats?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/agentstats?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: AgentStat
  data: {
    agentId?: integer
    statType?: integer
    time?: integer
[]
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/AgentStatSingleResponse

```ts
{
  id?: integer
  type?: string //default: AgentStat
  data: {
    agentId?: integer
    statType?: integer
    time?: integer
[]
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/AgentStatPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: AgentStat
  data: {
    agentId?: integer
    statType?: integer
    time?: integer
[]
  }
}
```

### #/components/schemas/AgentStatListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/AgentStatResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/ChunkCreate

```ts
{
  data: {
    type?: string
    attributes: {
      taskId?: integer
      skip?: integer
      length?: integer
      agentId?: integer
      dispatchTime?: integer
      solveTime?: integer
      checkpoint?: integer
      progress?: integer
      state?: integer
      cracked?: integer
      speed?: integer
    }
  }
}
```

### #/components/schemas/ChunkPatch

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

### #/components/schemas/ChunkResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/chunks?page[size]=25
    first?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/chunks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/chunks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/ChunkSingleResponse

```ts
{
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/ChunkPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Chunk
  data: {
    taskId?: integer
    skip?: integer
    length?: integer
    agentId?: integer
    dispatchTime?: integer
    solveTime?: integer
    checkpoint?: integer
    progress?: integer
    state?: integer
    cracked?: integer
    speed?: integer
  }
}
```

### #/components/schemas/ChunkListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/ChunkResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/ConfigCreate

```ts
{
  data: {
    type?: string
    attributes: {
      configSectionId?: integer
      item?: string
      value?: string
    }
  }
}
```

### #/components/schemas/ConfigPatch

```ts
{
  data: {
    type?: string
    attributes: {
      configSectionId?: integer
      item?: string
      value?: string
    }
  }
}
```

### #/components/schemas/ConfigResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/configs?page[size]=25
    first?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/configs?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/configs?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Config
  data: {
    configSectionId?: integer
    item?: string
    value?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/ConfigSingleResponse

```ts
{
  id?: integer
  type?: string //default: Config
  data: {
    configSectionId?: integer
    item?: string
    value?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/ConfigPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Config
  data: {
    configSectionId?: integer
    item?: string
    value?: string
  }
}
```

### #/components/schemas/ConfigListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/ConfigResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/ConfigSectionCreate

```ts
{
  data: {
    type?: string
    attributes: {
      sectionName?: string
    }
  }
}
```

### #/components/schemas/ConfigSectionPatch

```ts
{
  data: {
    type?: string
    attributes: {
      sectionName?: string
    }
  }
}
```

### #/components/schemas/ConfigSectionResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/configsections?page[size]=25
    first?: string //default: /api/v2/ui/configsections?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/configsections?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/configsections?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/configsections?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: ConfigSection
  data: {
    sectionName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/ConfigSectionSingleResponse

```ts
{
  id?: integer
  type?: string //default: ConfigSection
  data: {
    sectionName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/ConfigSectionPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: ConfigSection
  data: {
    sectionName?: string
  }
}
```

### #/components/schemas/ConfigSectionListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/ConfigSectionResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/CrackerBinaryCreate

```ts
{
  data: {
    type?: string
    attributes: {
      crackerBinaryTypeId?: integer
      version?: string
      downloadUrl?: string
      binaryName?: string
    }
  }
}
```

### #/components/schemas/CrackerBinaryPatch

```ts
{
  data: {
    type?: string
    attributes: {
      binaryName?: string
      crackerBinaryTypeId?: integer
      downloadUrl?: string
      version?: string
    }
  }
}
```

### #/components/schemas/CrackerBinaryResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackers?page[size]=25
    first?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/CrackerBinarySingleResponse

```ts
{
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/CrackerBinaryPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: CrackerBinary
  data: {
    crackerBinaryTypeId?: integer
    version?: string
    downloadUrl?: string
    binaryName?: string
  }
}
```

### #/components/schemas/CrackerBinaryListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/CrackerBinaryResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/CrackerBinaryTypeCreate

```ts
{
  data: {
    type?: string
    attributes: {
      typeName?: string
      isChunkingAvailable?: boolean
    }
  }
}
```

### #/components/schemas/CrackerBinaryTypePatch

```ts
{
  data: {
    type?: string
    attributes: {
      isChunkingAvailable?: boolean
      typeName?: string
    }
  }
}
```

### #/components/schemas/CrackerBinaryTypeResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/crackertypes?page[size]=25
    first?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/crackertypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/CrackerBinaryTypeSingleResponse

```ts
{
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/CrackerBinaryTypePostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: CrackerBinaryType
  data: {
    typeName?: string
    isChunkingAvailable?: boolean
  }
}
```

### #/components/schemas/CrackerBinaryTypeListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/CrackerBinaryTypeResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/FileCreate

```ts
{
  data: {
    type?: string
    attributes: {
      sourceType?: string
      sourceData?: string
      filename?: string
      size?: integer
      isSecret?: boolean
      fileType?: integer
      accessGroupId?: integer
      lineCount?: integer
    }
  }
}
```

### #/components/schemas/FilePatch

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      fileType?: integer
      filename?: string
      isSecret?: boolean
    }
  }
}
```

### #/components/schemas/FileResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/files?page[size]=25
    first?: string //default: /api/v2/ui/files?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/files?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/files?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/files?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/FileSingleResponse

```ts
{
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/FilePostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: File
  data: {
    filename?: string
    size?: integer
    isSecret?: boolean
    fileType?: integer
    accessGroupId?: integer
    lineCount?: integer
  }
}
```

### #/components/schemas/FileListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/FileResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/RightGroupCreate

```ts
{
  data: {
    type?: string
    attributes: {
      name?: string
      permissions: {
      }
    }
  }
}
```

### #/components/schemas/RightGroupPatch

```ts
{
  data: {
    type?: string
    attributes: {
      name?: string
      permissions: {
      }
    }
  }
}
```

### #/components/schemas/RightGroupResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25
    first?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/globalpermissiongroups?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/RightGroupSingleResponse

```ts
{
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/RightGroupPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: RightGroup
  data: {
    name?: string
    permissions: {
    }
  }
}
```

### #/components/schemas/RightGroupListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/RightGroupResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/HashCreate

```ts
{
  data: {
    type?: string
    attributes: {
      hashlistId?: integer
      hash?: string
      salt?: string
      plaintext?: string
      timeCracked?: integer
      chunkId?: integer
      isCracked?: boolean
      crackPos?: integer
    }
  }
}
```

### #/components/schemas/HashPatch

```ts
{
  data: {
    type?: string
    attributes: {
      chunkId?: integer
      crackPos?: integer
      hash?: string
      hashlistId?: integer
      isCracked?: boolean
      plaintext?: string
      salt?: string
      timeCracked?: integer
    }
  }
}
```

### #/components/schemas/HashResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashes?page[size]=25
    first?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/HashSingleResponse

```ts
{
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/HashPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hash
  data: {
    hashlistId?: integer
    hash?: string
    salt?: string
    plaintext?: string
    timeCracked?: integer
    chunkId?: integer
    isCracked?: boolean
    crackPos?: integer
  }
}
```

### #/components/schemas/HashListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/HashResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/HashlistCreate

```ts
{
  data: {
    type?: string
    attributes: {
      hashlistSeperator?: string
      sourceType?: string
      sourceData?: string
      name?: string
      format?: enum[0, 1, 2, 3]
      hashTypeId?: integer
      hashCount?: integer
      separator?: string
      cracked?: integer
      isSecret?: boolean
      isHexSalt?: boolean
      isSalted?: boolean
      accessGroupId?: integer
      notes?: string
      useBrain?: boolean
      brainFeatures?: integer
      isArchived?: boolean
    }
  }
}
```

### #/components/schemas/HashlistPatch

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      isSecret?: boolean
      name?: string
      notes?: string
    }
  }
}
```

### #/components/schemas/HashlistResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashlists?page[size]=25
    first?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashlists?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashlists?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/HashlistSingleResponse

```ts
{
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/HashlistPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Hashlist
  data: {
    name?: string
    format?: enum[0, 1, 2, 3]
    hashTypeId?: integer
    hashCount?: integer
    separator?: string
    cracked?: integer
    isSecret?: boolean
    isHexSalt?: boolean
    isSalted?: boolean
    accessGroupId?: integer
    notes?: string
    useBrain?: boolean
    brainFeatures?: integer
    isArchived?: boolean
  }
}
```

### #/components/schemas/HashlistListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/HashlistResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/HashTypeCreate

```ts
{
  data: {
    type?: string
    attributes: {
      description?: string
      isSalted?: boolean
      isSlowHash?: boolean
    }
  }
}
```

### #/components/schemas/HashTypePatch

```ts
{
  data: {
    type?: string
    attributes: {
      description?: string
      isSalted?: boolean
      isSlowHash?: boolean
    }
  }
}
```

### #/components/schemas/HashTypeResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/hashtypes?page[size]=25
    first?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/hashtypes?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HashType
  data: {
    description?: string
    isSalted?: boolean
    isSlowHash?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/HashTypeSingleResponse

```ts
{
  id?: integer
  type?: string //default: HashType
  data: {
    description?: string
    isSalted?: boolean
    isSlowHash?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/HashTypePostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HashType
  data: {
    description?: string
    isSalted?: boolean
    isSlowHash?: boolean
  }
}
```

### #/components/schemas/HashTypeListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/HashTypeResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/HealthCheckAgentCreate

```ts
{
  data: {
    type?: string
    attributes: {
      healthCheckId?: integer
      agentId?: integer
      status?: integer
      cracked?: integer
      numGpus?: integer
      start?: integer
      end?: integer
      errors?: string
    }
  }
}
```

### #/components/schemas/HealthCheckAgentPatch

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

### #/components/schemas/HealthCheckAgentResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthcheckagents?page[size]=25
    first?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthcheckagents?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/HealthCheckAgentSingleResponse

```ts
{
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/HealthCheckAgentPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HealthCheckAgent
  data: {
    healthCheckId?: integer
    agentId?: integer
    status?: integer
    cracked?: integer
    numGpus?: integer
    start?: integer
    end?: integer
    errors?: string
  }
}
```

### #/components/schemas/HealthCheckAgentListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/HealthCheckAgentResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/HealthCheckCreate

```ts
{
  data: {
    type?: string
    attributes: {
      time?: integer
      status?: integer
      checkType?: integer
      hashtypeId?: integer
      crackerBinaryId?: integer
      expectedCracks?: integer
      attackCmd?: string
    }
  }
}
```

### #/components/schemas/HealthCheckPatch

```ts
{
  data: {
    type?: string
    attributes: {
      checkType?: integer
      crackerBinaryId?: integer
      hashtypeId?: integer
    }
  }
}
```

### #/components/schemas/HealthCheckResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/healthchecks?page[size]=25
    first?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/healthchecks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/HealthCheckSingleResponse

```ts
{
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/HealthCheckPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: HealthCheck
  data: {
    time?: integer
    status?: integer
    checkType?: integer
    hashtypeId?: integer
    crackerBinaryId?: integer
    expectedCracks?: integer
    attackCmd?: string
  }
}
```

### #/components/schemas/HealthCheckListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/HealthCheckResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/LogEntryCreate

```ts
{
  data: {
    type?: string
    attributes: {
      issuer?: string
      issuerId?: string
      level?: string
      message?: string
      time?: integer
    }
  }
}
```

### #/components/schemas/LogEntryPatch

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

### #/components/schemas/LogEntryResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/logentries?page[size]=25
    first?: string //default: /api/v2/ui/logentries?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/logentries?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/logentries?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/logentries?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: LogEntry
  data: {
    issuer?: string
    issuerId?: string
    level?: string
    message?: string
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/LogEntrySingleResponse

```ts
{
  id?: integer
  type?: string //default: LogEntry
  data: {
    issuer?: string
    issuerId?: string
    level?: string
    message?: string
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/LogEntryPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: LogEntry
  data: {
    issuer?: string
    issuerId?: string
    level?: string
    message?: string
    time?: integer
  }
}
```

### #/components/schemas/LogEntryListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/LogEntryResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/NotificationSettingCreate

```ts
{
  data: {
    type?: string
    attributes: {
      actionFilter?: string
      action?: string
      objectId?: integer
      notification?: string
      userId?: integer
      receiver?: string
      isActive?: boolean
    }
  }
}
```

### #/components/schemas/NotificationSettingPatch

```ts
{
  data: {
    type?: string
    attributes: {
      action?: string
      isActive?: boolean
      notification?: string
      receiver?: string
    }
  }
}
```

### #/components/schemas/NotificationSettingResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/notifications?page[size]=25
    first?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/notifications?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/notifications?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/NotificationSettingSingleResponse

```ts
{
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/NotificationSettingPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: NotificationSetting
  data: {
    action?: string
    objectId?: integer
    notification?: string
    userId?: integer
    receiver?: string
    isActive?: boolean
  }
}
```

### #/components/schemas/NotificationSettingListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/NotificationSettingResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/PreprocessorCreate

```ts
{
  data: {
    type?: string
    attributes: {
      name?: string
      url?: string
      binaryName?: string
      keyspaceCommand?: string
      skipCommand?: string
      limitCommand?: string
    }
  }
}
```

### #/components/schemas/PreprocessorPatch

```ts
{
  data: {
    type?: string
    attributes: {
      binaryName?: string
      keyspaceCommand?: string
      limitCommand?: string
      name?: string
      skipCommand?: string
      url?: string
    }
  }
}
```

### #/components/schemas/PreprocessorResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/preprocessors?page[size]=25
    first?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/preprocessors?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Preprocessor
  data: {
    name?: string
    url?: string
    binaryName?: string
    keyspaceCommand?: string
    skipCommand?: string
    limitCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/PreprocessorSingleResponse

```ts
{
  id?: integer
  type?: string //default: Preprocessor
  data: {
    name?: string
    url?: string
    binaryName?: string
    keyspaceCommand?: string
    skipCommand?: string
    limitCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/PreprocessorPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Preprocessor
  data: {
    name?: string
    url?: string
    binaryName?: string
    keyspaceCommand?: string
    skipCommand?: string
    limitCommand?: string
  }
}
```

### #/components/schemas/PreprocessorListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/PreprocessorResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/PretaskCreate

```ts
{
  data: {
    type?: string
    attributes: {
[]
      taskName?: string
      attackCmd?: string
      chunkTime?: integer
      statusTimer?: integer
      color?: string
      isSmall?: boolean
      isCpuTask?: boolean
      useNewBench?: boolean
      priority?: integer
      maxAgents?: integer
      isMaskImport?: boolean
      crackerBinaryTypeId?: integer
    }
  }
}
```

### #/components/schemas/PretaskPatch

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      crackerBinaryTypeId?: integer
      isCpuTask?: boolean
      isMaskImport?: boolean
      isSmall?: boolean
      maxAgents?: integer
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

### #/components/schemas/PretaskResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/pretasks?page[size]=25
    first?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/pretasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/pretasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/PretaskSingleResponse

```ts
{
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/PretaskPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Pretask
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    priority?: integer
    maxAgents?: integer
    isMaskImport?: boolean
    crackerBinaryTypeId?: integer
  }
}
```

### #/components/schemas/PretaskListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/PretaskResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/SpeedCreate

```ts
{
  data: {
    type?: string
    attributes: {
      agentId?: integer
      taskId?: integer
      speed?: integer
      time?: integer
    }
  }
}
```

### #/components/schemas/SpeedPatch

```ts
{
  data: {
    type?: string
    attributes: {
    }
  }
}
```

### #/components/schemas/SpeedResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/speeds?page[size]=25
    first?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/speeds?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/speeds?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/SpeedSingleResponse

```ts
{
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/SpeedPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Speed
  data: {
    agentId?: integer
    taskId?: integer
    speed?: integer
    time?: integer
  }
}
```

### #/components/schemas/SpeedListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/SpeedResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/SupertaskCreate

```ts
{
  data: {
    type?: string
    attributes: {
[]
      supertaskName?: string
    }
  }
}
```

### #/components/schemas/SupertaskPatch

```ts
{
  data: {
    type?: string
    attributes: {
      supertaskName?: string
    }
  }
}
```

### #/components/schemas/SupertaskResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/supertasks?page[size]=25
    first?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/supertasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/supertasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/SupertaskSingleResponse

```ts
{
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/SupertaskPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Supertask
  data: {
    supertaskName?: string
  }
}
```

### #/components/schemas/SupertaskListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/SupertaskResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/TaskCreate

```ts
{
  data: {
    type?: string
    attributes: {
      hashlistId?: integer
[]
      taskName?: string
      attackCmd?: string
      chunkTime?: integer
      statusTimer?: integer
      keyspace?: integer
      keyspaceProgress?: integer
      priority?: integer
      maxAgents?: integer
      color?: string
      isSmall?: boolean
      isCpuTask?: boolean
      useNewBench?: boolean
      skipKeyspace?: integer
      crackerBinaryId?: integer
      crackerBinaryTypeId?: integer
      taskWrapperId?: integer
      isArchived?: boolean
      notes?: string
      staticChunks?: integer
      chunkSize?: integer
      forcePipe?: boolean
      preprocessorId?: integer
      preprocessorCommand?: string
    }
  }
}
```

### #/components/schemas/TaskPatch

```ts
{
  data: {
    type?: string
    attributes: {
      attackCmd?: string
      chunkTime?: integer
      color?: string
      isArchived?: boolean
      isCpuTask?: boolean
      isSmall?: boolean
      maxAgents?: integer
      notes?: string
      priority?: integer
      statusTimer?: integer
      taskName?: string
    }
  }
}
```

### #/components/schemas/TaskResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/tasks?page[size]=25
    first?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/tasks?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/tasks?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/TaskSingleResponse

```ts
{
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/TaskPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: Task
  data: {
    taskName?: string
    attackCmd?: string
    chunkTime?: integer
    statusTimer?: integer
    keyspace?: integer
    keyspaceProgress?: integer
    priority?: integer
    maxAgents?: integer
    color?: string
    isSmall?: boolean
    isCpuTask?: boolean
    useNewBench?: boolean
    skipKeyspace?: integer
    crackerBinaryId?: integer
    crackerBinaryTypeId?: integer
    taskWrapperId?: integer
    isArchived?: boolean
    notes?: string
    staticChunks?: integer
    chunkSize?: integer
    forcePipe?: boolean
    preprocessorId?: integer
    preprocessorCommand?: string
  }
}
```

### #/components/schemas/TaskListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/TaskResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/TaskWrapperCreate

```ts
{
  data: {
    type?: string
    attributes: {
      priority?: integer
      maxAgents?: integer
      taskType?: enum[0, 1]
      hashlistId?: integer
      accessGroupId?: integer
      taskWrapperName?: string
      isArchived?: boolean
      cracked?: integer
    }
  }
}
```

### #/components/schemas/TaskWrapperPatch

```ts
{
  data: {
    type?: string
    attributes: {
      accessGroupId?: integer
      isArchived?: boolean
      maxAgents?: integer
      priority?: integer
      taskWrapperName?: string
    }
  }
}
```

### #/components/schemas/TaskWrapperResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/taskwrappers?page[size]=25
    first?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/taskwrappers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/TaskWrapperSingleResponse

```ts
{
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/TaskWrapperPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: TaskWrapper
  data: {
    priority?: integer
    maxAgents?: integer
    taskType?: enum[0, 1]
    hashlistId?: integer
    accessGroupId?: integer
    taskWrapperName?: string
    isArchived?: boolean
    cracked?: integer
  }
}
```

### #/components/schemas/TaskWrapperListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/TaskWrapperResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/UserCreate

```ts
{
  data: {
    type?: string
    attributes: {
      name?: string
      email?: string
      passwordHash?: string
      passwordSalt?: string
      isValid?: boolean
      isComputedPassword?: boolean
      lastLoginDate?: integer
      registeredSince?: integer
      sessionLifetime?: integer
      globalPermissionGroupId?: integer
      yubikey?: string
      otp1?: string
      otp2?: string
      otp3?: string
      otp4?: string
    }
  }
}
```

### #/components/schemas/UserPatch

```ts
{
  data: {
    type?: string
    attributes: {
      email?: string
      globalPermissionGroupId?: integer
      isValid?: boolean
      name?: string
    }
  }
}
```

### #/components/schemas/UserResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/users?page[size]=25
    first?: string //default: /api/v2/ui/users?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/users?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/users?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/users?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/UserSingleResponse

```ts
{
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/UserPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: User
  data: {
    name?: string
    email?: string
    passwordHash?: string
    passwordSalt?: string
    isValid?: boolean
    isComputedPassword?: boolean
    lastLoginDate?: integer
    registeredSince?: integer
    sessionLifetime?: integer
    globalPermissionGroupId?: integer
    yubikey?: string
    otp1?: string
    otp2?: string
    otp3?: string
    otp4?: string
  }
}
```

### #/components/schemas/UserListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/UserResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/RegVoucherCreate

```ts
{
  data: {
    type?: string
    attributes: {
      voucher?: string
      time?: integer
    }
  }
}
```

### #/components/schemas/RegVoucherPatch

```ts
{
  data: {
    type?: string
    attributes: {
      voucher?: string
    }
  }
}
```

### #/components/schemas/RegVoucherResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  links: {
    self?: string //default: /api/v2/ui/vouchers?page[size]=25
    first?: string //default: /api/v2/ui/vouchers?page[size]=25&page[after]=0
    last?: string //default: /api/v2/ui/vouchers?page[size]=25&page[before]=500
    next?: string //default: /api/v2/ui/vouchers?page[size]=25&page[after]=25
    previous?: string //default: /api/v2/ui/vouchers?page[size]=25&page[before]=25
  }
  id?: integer
  type?: string //default: RegVoucher
  data: {
    voucher?: string
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/RegVoucherSingleResponse

```ts
{
  id?: integer
  type?: string //default: RegVoucher
  data: {
    voucher?: string
    time?: integer
  }
  relationships: {
  }
  included: {
  }[]
}
```

### #/components/schemas/RegVoucherPostPatchResponse

```ts
{
  jsonapi: {
    version?: string //default: 1.1
    ext?: string //default: https://jsonapi.org/profiles/ethanresnick/cursor-pagination
  }
  id?: integer
  type?: string //default: RegVoucher
  data: {
    voucher?: string
    time?: integer
  }
}
```

### #/components/schemas/RegVoucherListResponse

```ts
{
  "allOf": [
    {
      "$ref": "#/components/schemas/ListResponse"
    },
    {
      "type": "object",
      "properties": {
        "values": {
          "type": "array",
          "items": {
            "$ref": "#/components/schemas/RegVoucherResponse"
          }
        }
      }
    }
  ]
}
```

### #/components/schemas/Token

```ts
{
  token?: string
  expires?: integer
}
```

### #/components/schemas/TokenRequest

```ts
string[]
```

### #/components/schemas/ObjectRequest

```ts
{
  expand?: string
  expires?: integer
}
```

### #/components/schemas/ObjectListRequest

```ts
{
  expand?: string
  filter?: string[]
}
```

### #/components/securitySchemes/bearerAuth

```ts
{
  "type": "http",
  "description": "JWT Authorization header using the Bearer scheme.",
  "scheme": "bearer",
  "bearerFormat": "JWT",
  "scopes": [
    "permAccessGroupCreate",
    "permAccessGroupDelete",
    "permAccessGroupRead",
    "permAccessGroupUpdate",
    "permAgentAssignmentCreate",
    "permAgentAssignmentDelete",
    "permAgentAssignmentRead",
    "permAgentAssignmentUpdate",
    "permAgentBinaryCreate",
    "permAgentBinaryDelete",
    "permAgentBinaryRead",
    "permAgentBinaryUpdate",
    "permAgentCreate",
    "permAgentDelete",
    "permAgentRead",
    "permAgentStatDelete",
    "permAgentStatRead",
    "permAgentUpdate",
    "permChunkRead",
    "permChunkUpdate",
    "permConfigRead",
    "permConfigSectionRead",
    "permConfigUpdate",
    "permCrackerBinaryCreate",
    "permCrackerBinaryDelete",
    "permCrackerBinaryRead",
    "permCrackerBinaryTypeCreate",
    "permCrackerBinaryTypeDelete",
    "permCrackerBinaryTypeRead",
    "permCrackerBinaryTypeUpdate",
    "permCrackerBinaryUpdate",
    "permFileCreate",
    "permFileDelete",
    "permFileRead",
    "permFileUpdate",
    "permHashRead",
    "permHashTypeCreate",
    "permHashTypeDelete",
    "permHashTypeRead",
    "permHashTypeUpdate",
    "permHashUpdate",
    "permHashlistCreate",
    "permHashlistDelete",
    "permHashlistRead",
    "permHashlistUpdate",
    "permHealthCheckAgentRead",
    "permHealthCheckAgentUpdate",
    "permHealthCheckCreate",
    "permHealthCheckDelete",
    "permHealthCheckRead",
    "permHealthCheckUpdate",
    "permLogEntryCreate",
    "permLogEntryDelete",
    "permLogEntryRead",
    "permLogEntryUpdate",
    "permNotificationSettingCreate",
    "permNotificationSettingDelete",
    "permNotificationSettingRead",
    "permNotificationSettingUpdate",
    "permPreprocessorCreate",
    "permPreprocessorDelete",
    "permPreprocessorRead",
    "permPreprocessorUpdate",
    "permPretaskCreate",
    "permPretaskDelete",
    "permPretaskRead",
    "permPretaskUpdate",
    "permRegVoucherCreate",
    "permRegVoucherDelete",
    "permRegVoucherRead",
    "permRegVoucherUpdate",
    "permRightGroupCreate",
    "permRightGroupDelete",
    "permRightGroupRead",
    "permRightGroupUpdate",
    "permSpeedRead",
    "permSpeedUpdate",
    "permSupertaskCreate",
    "permSupertaskDelete",
    "permSupertaskRead",
    "permSupertaskUpdate",
    "permTaskCreate",
    "permTaskDelete",
    "permTaskRead",
    "permTaskUpdate",
    "permTaskWrapperCreate",
    "permTaskWrapperDelete",
    "permTaskWrapperRead",
    "permTaskWrapperUpdate",
    "permUserCreate",
    "permUserDelete",
    "permUserRead",
    "permUserUpdate"
  ]
}
```

### #/components/securitySchemes/basicAuth

```ts
{
  "type": "http",
  "description": "Basic Authorization header.",
  "scheme": "basic"
}
```