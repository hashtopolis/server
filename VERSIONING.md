# Versioning

## Semantic Versioning

Hashtopolis follows [Semantic Versioning 2.0.0](https://semver.org/). Given a version number `MAJOR.MINOR.PATCH`:

- **MAJOR:** incompatible API or database schema changes.
- **MINOR:** new functionality added in a backward-compatible manner.
- **PATCH:** backward-compatible bug fixes.

Pre-release versions may be suffixed (e.g., `v1.0.0-beta`, `v1.1.0-rc1`). They sort before the final release per the
semver specification.

## Branch Model — GitHub Flow

```
master ← feature branches (via PR)
   ↑
   └── release/vMAJOR.MINOR.x  (short-lived, only for hotfixes)
```

### Master branch

`master` is always stable and deployable. Its version string indicates the last released version with a `+dev` suffix:

```php
// src/inc/StartupConfig.php
public function getVersion(): string {
    return "v1.0.0+dev";
}
```

The `+dev` part is [semver build metadata](https://semver.org/#spec-item-10) — it is ignored for precedence and stripped
by existing migration logic (`explode("+", $version)[0]`). This means anyone building from `master` gets a version that:

- Is clearly distinguishable from a release.
- Will not collide with any future release.
- Triggers migrations correctly when the next release ships (since `v1.1.0 > v1.0.0`).

### Feature branches

Branch from `master`, merge back via pull request. Bug fixes follow the same flow.

### Making a release

1. Create a **release PR** that bumps `getVersion()` from `"v1.0.0+dev"` to `"v1.1.0"`.
2. Merge the release PR to `master`.
3. **Tag** the merge commit:
   ```
   git tag v1.1.0 && git push origin v1.1.0
   ```
4. **Immediately follow up** with a commit on `master` bumping the version to `"v1.1.0+dev"` (the `+dev` marker for the
   next cycle). There is no need to guess whether the next release will be `v1.2.0` or `v1.1.1` — `+dev` does not commit
   to either.

### Hotfixes for a released version

If a critical bug needs a patch for a released version while `master` has already moved on:

1. Create a short-lived branch from the tag:
   ```
   git checkout -b release/v1.0.x v1.0.0
   ```
2. Apply the fix (cherry-pick from `master` if it already contains the fix).
3. Bump the version to `"v1.0.1"`, commit, tag:
   ```
   git tag v1.0.1 && git push origin v1.0.1
   ```
4. Delete the `release/v1.0.x` branch after tagging, or keep it if more patches are expected for that line. Only the
   latest two minor release lines are actively supported.

## Docker Images

Docker images are built automatically and pushed to the container registry. Three image tags are maintained:

| Tag      | Source                                          | Description                                                                                         |
|----------|-------------------------------------------------|-----------------------------------------------------------------------------------------------------|
| `master` | `master` branch HEAD                            | Latest build from the main branch. May contain unreleased changes. Not intended for production use. |
| `latest` | Most recent stable tag (excluding pre-releases) | Newest production-ready release. Updated whenever a new stable version is tagged.                   |
| `vX.Y.Z` | Corresponding git tag                           | Exact release. Immutable once published.                                                            |

Pre-release tags (e.g., `v1.1.0-rc1`) also get their own Docker image tag but do **not** update `latest`.
