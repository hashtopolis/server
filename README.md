# Hashtopussy

<img src="https://github.com/s3inlc/hashtopussy/blob/master/src/static/logo.png" alt='Hashtopussy' width="100">

ATTENTION: people using the newest Hashcat beta need to use branch [hashcat-beta](https://github.com/s3inlc/hashtopussy/tree/hashcat-beta)!

Hashtopussy is a multi-platform client-server tool for distributing hashcat tasks among multiple computers. It is strongly based on Hashtopus.
Like Hashtopus, the main goals for Hashtopussy's development are portability, robustness, multi-user support, and to bring Hashtopus to the next level.
The application has two parts:

- **Agent** Multiple clients (C#, Python, PHP), easily customizable to suite any need.
- **Server** several PHP/CSS files operating on two endpoints: an Admin GUI and an Agent Connection Point

Aiming for high usability even on restricted networks, Hashtopussy communicates over HTTP(S) using a human-readable, hashing-specific dialect of JSON.

The server part runs on PHP using MySQL as the database back end. It is vital that your MySQL server be configured with performance in mind. Queries can be very expensive and proper configuration makes the difference between a few milliseconds of waiting and disastrous multi-second lags. The database schema heavily profits from indexing. Therefore, if you see a hint about pre-sorting your hashlist, please do so.

The web admin interface is the single point of access across all client agents. New agent deployments require a one-time password generated in the New Agent tab. This reduces the risk of leaking hashes or files to rogue or fake agents.

There are parts of the documentation and wiki which are not up-to-date. If you detect anything or have questions on understanding descriptions, feel free to ask us.

To report a bug, please create an issue and try to describe the problem as accurately as possible. This helps us to identify the bug and see if it is reproducible.

## Features

- Easy and comfortable to use
- Accessible from anywhere via web interface
- Server component highly compatible with common webhosting setups
- Unattended agents
- File management for word lists, rules, ...
- Self-updating of both Hashtopussy and hashcat
- Cracking multiple hashlists of the same hash type as though they were a single hashlist
- Running the same binary on Windows and Linux
- Files and hashes marked as "secret" are only distributed to agents marked as "trusted"
- Many data import and export options
- Rich statistics on hashes and running tasks
- Visual representation of chunk distribution
- Multi-user support
- User permission levels

## Hashtopus or Hashtopussy?

Hashtopus is a great program but is lacking in some areas. Major differences between the two are:

- Drastically improved security
- Multi-user support
- Improved look and layout
- Super Tasks
- --hex-salt support

Please visit the [wiki](https://github.com/s3inlc/hashtopussy/wiki) for more information on setup and upgrade.

Some screenshots of Hashtopussy (by winxp5421 and s3in!c): [Imgur1](http://imgur.com/gallery/Fj0s0) [Imgur2](http://imgur.com/gallery/LzTsI)

## Thanks

* winxp for testing, writing help texts and a lot of input ideas
* blazer for working on the agent
* CynoSure Prime for testing
* atom for [hashcat](https://github.com/hashcat/hashcat)
* curlyboi for the original [Hashtopus](https://github.com/curlyboi/hashtopus) code
* 7zip binaries are compiled from https://sourceforge.net/projects/sevenzip/files/7-Zip/16.04/
