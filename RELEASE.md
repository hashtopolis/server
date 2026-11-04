# Release Process

## Planning a Release

1. Create a **milestone** named after the target version (e.g., `v1.1.0`).
2. Assign all issues and pull requests that should be part of this release to the milestone.
3. Create a **release issue** from the [template below](#release-issue-template) with the relevant checklist items for this specific release. Select only the steps that actually apply — delete the rest.

## Release Issue Template

When cutting a release, create an issue under this repository with the version as the title (e.g., `Release v1.1.0`). Attach it to the corresponding milestone. Copy the applicable items from the checklist below, delete the ones that are not needed.

### Release Preparations

#### Agents

- [ ] Update agent version in [hashtopolis/agent-python](https://github.com/hashtopolis/agent-python) if needed
- [ ] Release the python agent
- [ ] Update agent version in [hashtopolis/agent-rust](https://github.com/hashtopolis/agent-rust) if needed
- [ ] Release the rust agent

#### Backend

- [ ] Start branch for release preparations
- [ ] Build the python agent via `./build.sh` in [hashtopolis/agent-python](https://github.com/hashtopolis/agent-python), copy the resulting zip to `src/bin/`
- [ ] Copy the built rust agent release zip to `src/bin/`
- [ ] Update agent versions references in setup.json
- [ ] Add migration to update existing agent versions
- [ ] Insert newest hashcat version in setup.json
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
- [ ] Update maintained tags on docker readme page.

### Post-Release

- [ ] **Bump version to `+dev` on `master`**: update backend `StartupConfig::getVersion()` to `"vMAJOR.MINOR.PATCH+dev"` (e.g., `v1.1.0+dev` after releasing `v1.1.0`).
- [ ] **Bump version to `+dev` on `master`**: update frontend (`src/config/default/app/main.ts`)

## Version Schema

See [VERSIONING.md](VERSIONING.md) for the full versioning and branching model.
