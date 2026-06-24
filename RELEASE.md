# Release Process

## Planning a Release

1. Create a **milestone** named after the target version (e.g., `v1.1.0`).
2. Assign all issues and pull requests that should be part of this release to the milestone.
3. Create a **release issue** from the [template below](#release-issue-template) with the relevant checklist items for this specific release. Select only the steps that actually apply — delete the rest.

## Release Issue Template

When cutting a release, create an issue under this repository with the version as the title (e.g., `Release v1.1.0`). Attach it to the corresponding milestone. Copy the applicable items from the checklist below, delete the ones that are not needed.

### Release Preparations

#### Agent

- [ ] Update agent release notes and version in [hashtopolis-agent-python](https://github.com/hashtopolis/hashtopolis-agent-python) if needed
- [ ] Release the agent

#### Backend

- [ ] Start branch for release preparations
- [ ] Update hashcat modes in the database schema (`hashtopolis.sql` and update scripts) — see [Hashcat Modes Diff](#hashcat-modes-diff) below
- [ ] Build the agent via `./build.sh` in [hashtopolis-agent-python](https://github.com/hashtopolis/hashtopolis-agent-python), copy the resulting zip to `src/bin/`
- [ ] Update agent version references in migration for initial setups
- [ ] Insert newest hashcat version as a migration for initial setups
- [ ] Adjust server release notes (`changelog.md`) _(legacy)_
- [ ] Update `StartupConfig::getVersion()` from `"vMAJOR.MINOR.PATCH+dev"` to `"vMAJOR.MINOR.PATCH"`
- [ ] Ensure `master` is in sync with any long-running feature branches
- [ ] Create PR for merging
- [ ] Run the full test suite (PHPUnit + pytest if applicable)
- [ ] Run the build process

#### Frontend

- [ ] Start branch for release preparations
- [ ] Update the version of the frontend (`src/config/default/app/main.ts`) if it changed
- [ ] Create PR for merging
- [ ] Run the full test suite
- [ ] Run the build process

### Release

- [ ] Backend: merge the release PR to `master`
- [ ] Backend: release the server with the appropriate tag for the version
- [ ] Frontend: merge the release PR to `master`
- [ ] Frontend: release the frontend with the appropriate tag for the version

### Docker

- [ ] Pull, tag, and push backend image:
  ```
  docker pull hashtopolis/backend:vMAJOR.MINOR.PATCH
  docker tag hashtopolis/backend:vMAJOR.MINOR.PATCH hashtopolis/backend:latest
  docker push hashtopolis/backend:latest
  ```
- [ ] Pull, tag, and push frontend image:
  ```
  docker pull hashtopolis/frontend:vMAJOR.MINOR.PATCH
  docker tag hashtopolis/frontend:vMAJOR.MINOR.PATCH hashtopolis/frontend:latest
  docker push hashtopolis/frontend:latest
  ```

### Post-Release

- [ ] **Bump version to `+dev` on `master`**: update backend `StartupConfig::getVersion()` to `"vMAJOR.MINOR.PATCH+dev"` (e.g., `v1.1.0+dev` after releasing `v1.1.0`).
- [ ] **Bump version to `+dev` on `master`**: update frontend (`src/config/default/app/main.ts`)

## Hashcat Modes Diff

To check whether hashcat added or removed modes since the last release:

```
cat dbmodes | grep -Eo '\([0-9]+,' | tr -d '(,' > dbmodes.num
cat hcmodes | cut -d'|' -f 1 | tr -d ' ' | sort -n > hcmodes.num
comm -32 hcmodes.num dbmodes.num | tee diff
```

Where `dbmodes` contains the current SQL entries and `hcmodes` is a dump of the hashcat wiki mode table.

## Version Schema

See [VERSIONING.md](VERSIONING.md) for the full versioning and branching model.
