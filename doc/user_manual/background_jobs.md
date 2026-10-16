# Background Jobs

Long-running server operations are executed asynchronously as background jobs instead of blocking a request. Jobs are persisted in the database and processed one after another by a job runner.

## Job Types

| Type | Description |
| --- | --- |
| `recount_file` | Recount the lines of a file (gzipped files are counted decompressed). |
| `scan_cracker` | Scan a hashcat binary archive for supported hash modes and update its hashtype associations. |
| `checksum_file` | Compute the BLAKE3 checksum of a file. |

## Job Properties

Keywords to document:

- `jobType`, JSON `payload` (e.g. `fileId`, `crackerBinaryId`), triggering `userId` (`null` for system-triggered jobs)
- `status`: *Pending* → *Running* → *Done* or *Failed*, with `exitCode` and `resultMessage`
- timestamps: `createdAt`, `startedAt`, `finishedAt`

## Monitoring Jobs

- API v2 (read/delete only): `GET /api/v2/ui/backgroundJobs`, `GET /api/v2/ui/backgroundJobs/{id}`, `GET /api/v2/ui/backgroundJobs/count`, `DELETE` (single and bulk)
- a currently running job cannot be deleted (409 Conflict)
- jobs cannot be created or modified via the API; they are enqueued by server actions (file uploads, binary scans, recount/checksum requests)
- permissions: background job read/delete rights (server configuration access)

## Runner and Scheduling

- the official Docker image runs the job runner every 60 seconds (entrypoint scheduler loop)
- jobs are processed one at a time under a global lock; overlapping runs are prevented, and a long job delays the others until it finishes
- stale jobs (e.g. after a runner crash) are detected and marked as failed on the next run
- disable the built-in scheduler with `HASHTOPOLIS_CRON_ENABLE=0`, e.g. to drive the runner with an external cron instead: run `php -f inc/cron.php` inside the backend container (see [Advanced installation](../installation_guidelines/advanced_install.md#background-job-scheduler))
