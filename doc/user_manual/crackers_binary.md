# Binaries

Hashtopolis is also responsible for the update and distribution of several binaries, starting with the cracker, e.g. Hashcat, but also the binaries for the agent. This part of the manual is dedicated to the management of those binaries from the corresponding menu.

## Crackers

When Hashtopolis was first developed it was solely designed to manage hashcat tasks with multiple agents. As part of the evolution of the project, support for other tools than hashcat was integrated into Hashtopolis. In addition to the support of different tools, Hashtopolis can also manage different versions of the same tool. 

<figure markdown="span">
    ![screenshot_cracker_page](../assets/images/cracker_page.png){ width="600" }
</figure>

This page displays some basic information about all the crackers configured in Hashtopolis. Apart from the ID of the cracker and its name, the available version(s) are also displayed. Hashtopolis is configured with a default hashcat cracker to be downloaded by the agents whenever they need it. 

### Creating a New Cracker

As mentioned above, Hashtopolis supports other crackers than Hashcat. To deploy a new cracker, two steps are required, first the creation of the type of cracker and then adding a version for it. 

By clicking on the *New Cracker* button, a new page opens in which you can set the name for the new cracker. <!-- and declare if the chunking is available for this cracker. In order to be compatible with chunking, a cracker must have the following features:--> A cracker must have the following features in order to split the work into chunks:

- **--keyspace**: calculate the size of the task to be distributed.
- **--skip**: define the starting point from where the hashcat instance should start working on the keyspace.
- **--limit**: define how many entries from the keyspace should be evaluated by the hashcat instance.

In other words, the keyspace is the total amount of work related to a task. The combination of skip and limit will define a portion of the keyspace, also called chunk, on which an agent will be working. These are the main features required to distribute a task among several agents.

<!-- If chunking is not available for a cracker, then a task cannot be split and it must be run by a single agent. When selecting such type of cracker during the task creation, the ["small task"](./tasks.md#advanced-parameters) flag will be enabled by default. 
-->

> [!CAUTION]
> Creating a new type of cracker is not a simple plug-and-play process with Hashtopolis. In addition to defining the new cracker type, you must also modify the agent itself. Specifically, this involves writing a dedicated Python handler file for your cracker.
>
> An example handler, generic_cracker.py, is available in the agent-python Git repository and can serve as a starting point. However, adapting it to your needs will likely require a solid understanding of how the agent communicates with the server and processes tasks. Once your custom handler is ready, you must also update the agent's main file to properly register and include it.

### Adding a New Version

Whether it is the first version for a new cracker or to update an existing cracker, the page displayed below for adding a version to a cracker will appear. 

<figure markdown="span">
    ![screenshot_manage_file](../assets/images/new_binary_version.png){ width="400" }
</figure>

The three following information are required to deploy a new version.

- **Binary Base Name**: this is how the cracker should be called from the command line by the agent. In our example, the hashcat cracker is called with ```hashcat```. 
- **Binary Version**: the version number of the cracker should be inserted here. The backend will order them in decreasing order. The latest version will be selected by default for this cracker.
- **Download URL**: this specifies from where the agent should download the binary package. In the case of our example, it is directly downloaded from the hashcat webpage. Alternatively, the archive can be uploaded to and stored on the server itself (see [Storing the binary on the server](#storing-the-binary-on-the-server)).

> [!NOTE]
> The agents may not have access to the location of the cracker, e.g. in the case of an offline system. One solution is to store the cracker package in a folder of the main server that is reachable by the agents. Then use this for the URL so that the agent can reach it and download it when needed. Alternatively, the archive can be stored on the Hashtopolis server itself, in which case the agents download it directly from the server (see [Storing the binary on the server](#storing-the-binary-on-the-server)).
>

#### Storing the Binary on the Server

Instead of referencing an external *Download URL*, a cracker binary archive can be uploaded to the server and stored in its *crackers* directory. Keywords to document:

- upload sources (API v2, `POST /api/v2/ui/crackers` with `sourceType`/`sourceData` instead of `downloadUrl`, mutually exclusive):
    - `import`: archive uploaded beforehand to the import directory (chunked upload) — for arbitrarily large archives
    - `url`: server fetches the archive itself (http/https only)
    - `inline`: base64-encoded archive data — small archives only
- archive constraints: `.7z` only (magic-byte validation), no client-supplied filename — server composes `{crackerType}-{version}.7z`
- the *Download URL* is set automatically to the server download endpoint (`/api/download.php/crackerBinary/{id}`) and cannot be changed afterwards for stored binaries
- stored archives live in the server's crackers directory (`HASHTOPOLIS_CRACKERS_PATH`, default `/usr/local/share/hashtopolis/crackers`)
- deleting a binary (or a whole cracker type) also deletes its stored archive
- URL-based binaries keep working as before (agents download from the external URL); the server additionally keeps a local copy of such archives
- agents require no changes: download URLs of stored binaries automatically carry the requesting agent's token

### Access Groups for Cracker Binaries

Each binary version belongs to an **access group**, extending the [access group concept](./users.md#access-groups) to cracker binaries. Keywords to document:

- an access group is required when adding a binary version; the creating user must be a member of that group
- visibility and usage restricted to group members: list, view, update, task creation, preconfigured tasks, supertasks
- reassigning a binary to another group is only allowed for users who are members of both the current and the new group
- agents only receive tasks whose cracker binary belongs to one of their access groups; binary downloads are enforced likewise
- on upgrade, existing binaries are assigned to the default access group; deleting an access group moves its binaries to the default group

### Supported Hashtypes

Each cracker binary has a set of hashtypes it supports, controlling which binaries can be selected for a task on a given hashlist. Keywords to document:

- **hashcat binaries**: supported hashtypes are determined automatically by a scan of the binary (see [Automatic Hashcat Scanning](#automatic-hashcat-scanning)); they cannot be edited manually
- **other cracker types**: start without any association and are configured manually (API v2: `GET`/`PATCH`/`POST`/`DELETE` `/api/v2/ui/crackers/{id}/relationships/hashtypes`)
- a binary without any association supports every hashtype
- reverse view: which cracker binaries support a given hashtype (API v2: `/api/v2/ui/hashtypes/{id}/crackerBinaries`)
- task creation is rejected if the selected binary does not support the hashtype of the target hashlist
- associations are removed consistently when a binary or a hashtype is deleted

### Automatic Hashcat Scanning

- trigger: every hashcat binary is scanned when it is added, updated or checked (background job `scan_cracker`, see [Background Jobs](./background_jobs.md))
- method: the stored archive is unpacked and the binary is queried with `--example-hashes --machine-readable`
- effects:
    - hashtypes reported by the binary that do not exist yet are created automatically (description, salted status, slow hash)
    - the binary's associations are replaced to exactly match the reported hash modes (new modes added, modes no longer available removed)
    - existing hashtypes are never modified by the scan
- on upgrade, existing hashcat binaries are queued for scanning automatically

## Preprocessors

The purpose of a preprocessor in the context of hashcat is to generate password candidates that are then fed through the standard input to a hashcat process. The preprocessor page displayed below lists all the preprocessors configured in Hashtopolis. 

<figure markdown="span">
    ![screenshot_cracker_page](../assets/images/preprocessor_page.png){ width="600" }
</figure>

By default Hashtopolis is installed with a single preprocessor, namely [*Prince*](https://github.com/hashcat/princeprocessor). Additional preprocessors can be added by clicking the *New Preprocessor* button. The creation page below is displayed.

<figure markdown="span">
    ![screenshot_cracker_page](../assets/images/new_preprocessor_page.png){ width="600" }
</figure>

It is rather similar to the creation of a new version of a [cracker](./crackers_binary.md#adding-a-new-version). The main difference is that the user can associate the required keyspace, skip, and limit options to different flags of the preprocessor. Note that those three remain mandatory to be used within Hashtopolis, however this allows more flexibility as the preprocessor may have named those options differently. If additional parameters are required at execution time, they should be included in the [preprocessor's command](./tasks.md#advanced-parameters) during the task creation.


## Agent Binaries

There are several situations where deploying a new Hashtopolis agent binary is necessary. Most commonly, this happens when official updates introduce bug fixes, performance improvements, or support for new features. However, you may also need to build or modify an agent binary yourself—for example, if you've developed a custom cracker that requires integration via a new Python handler, or if you need a version of the agent compiled specifically for another platform such as Windows. In all cases, updating the agent typically involves replacing the existing binary and ensuring any dependencies are still met.

The agent binaries page displays the information shown below about the current agent binaries configured in Hashtopolis.

<figure markdown="span">
    ![screenshot_cracker_page](../assets/images/agent_binaries_page.png){ width="600" }
</figure>

To register a new agent binary, simply press the button *New Binary* in the agent binaries page. The following page is then displayed.

<figure markdown="span">
    ![screenshot_cracker_page](../assets/images/new_agent_page.png){ width="400" }
</figure>

The following fields need to be filled at creation time.

- **Type**: Identifier of the agent binary, e.g. the language it is written in (the default Python agent uses *python*). It must be unique among the agent binaries. For official agents, it must match the name used on the Hashtopolis archive (see the note on updates below).
- **Operating Systems**: Informational free text listing the OSs supported by the agent, e.g. *Windows, Linux, OS X*.
- **Filename**: The filename of the agent binary as stored in the *bin/* folder of the server. The file must exist there: when adding a custom agent binary, you need to place the file in that folder yourself (e.g. by copying it into the backend container).
- **Version**: The version number of the agent binary. It is compared against the archive to detect available updates.
- **Update Track**: The release channel used when checking for updates, either *stable* or *release*.

> [!NOTE]
> For official agents, the server checks ```https://archive.hashtopolis.org/agent/<type>/<track>/``` for a newer version than the one configured. When an update is available, it can be downloaded from the archive directly from this page; the server verifies the SHA256 checksum and replaces the file in the *bin/* folder automatically.