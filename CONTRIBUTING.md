# Contributing to Hashtopolis

## Opening Issues

- **Existing issues** — Before opening a new issue, check if a similar issue or feature request already exists.

- **Expected behavior** — If you are unsure whether the behavior you are encountering is expected, check the documentation at https://docs.hashtopolis.org. You can also check the FAQ to see if your problem is already addressed there.

## Code Contributions

- **Coding style** — Follow the existing coding style and conventions used throughout the project.

- **Test coverage** — Include tests for your changes. Depending on the case this means PHPUnit tests, pytest tests for the API, or both if necessary.

- **Documentation** — Document your code using PHPDoc for PHP code or inline comments where necessary.

## Pull Requests

- **PR titles** should be phrased as an imperative sentence describing what was added, fixed, or changed (e.g., "Add user authentication", "Fix memory leak in worker pool", "Update dependency versions").

- **Issues** — Every pull request that resolves an issue must reference it in the description using a closing keyword (e.g., `closes #123`, `fixes #456`).

- **Branch cleanup** — The person merging the pull request is responsible for deleting the branch after the merge.

## Pull Requests — Backend

When submitting a pull request that includes database migration scripts, adhere to the following:

- **Never alter an existing migration script** that has been released or lived on `master` for any amount of time. Changing a released migration will leave setups that already applied the unaltered script in an inconsistent state that cannot be recovered without manual intervention or deletion.

- **One migration per atomic change** — Create a new migration script for each distinct feature or change. The database must be in a healthy, consistent state between every migration.

- **Dual database support** — New migration scripts must be provided for **both** MySQL and PostgreSQL in their respective directories (`src/migrations/mysql/` and `src/migrations/postgres/`).

- **Timestamp ordering** — Right before a PR with new migration scripts is merged, the script file names must be updated to reflect the actual merge date prefix. This ensures correct ordering across concurrent PRs. Commit this rename into the PR branch before merging.


