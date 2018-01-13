# Hashtopussy

<img src="https://github.com/s3inlc/hashtopussy/blob/master/src/static/logo.png" alt='Hashtopussy' width="100">

**This is the current development of version 0.5.0 with a large number of changes, there is no warranty that it will work until released!**

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

**Note:** "Hashtopussy" is based on the original project Hashtopus and utilizes the popular program Hashcat. The name Hashtopussy is derived from both of these projects. 

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
- Various notification types
- Small and/or CPU-only tasks

## Hashtopus or Hashtopussy?

Hashtopus is a great program but is lacking in some areas. Major differences between the two are:

- Drastically improved security
- Multi-user support
- Improved look and layout
- Super Tasks
- --hex-salt support

Please visit the [wiki](https://github.com/s3inlc/hashtopussy/wiki) for more information on setup and upgrade.

Some screenshots of Hashtopussy (by winxp5421 and s3in!c): [Imgur1](http://imgur.com/gallery/Fj0s0) [Imgur2](http://imgur.com/gallery/LzTsI)

## Contribution Guidelines

We are open to all kinds of contributions. If it's a bug fix or a new feature, feel free to create a pull request. Please consider some points:

* Just include one feature or one bugfix in one pull request. In case you have two new features please also create two pull requests.
* Try to stick with the code style used (especially in the PHP parts). IntelliJ/PHPStorm users can get a code style xml [here](https://gist.github.com/s3inlc/226ed78b05eb6dc8f60f18d6fd310d74).

The pull request will then be reviewed by at least one member and merged after approval. Don't be discouraged just because the first review is not approved, often these are just small changes.

## Support us

[PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7P3KXV8DQ5XKE)

```
BTC: 15gi3X5L4VPa5S2yygztYaN7MF7VA26Zaf
ETH: 0x06B3Ae7561AD763eF58Df9C37deB6757bDA2BC0c
```

## Thanks

* winxp for testing, writing help texts and a lot of input ideas
* blazer for working on the agent
* CynoSure Prime for testing
* atom for [hashcat](https://github.com/hashcat/hashcat)
* curlyboi for the original [Hashtopus](https://github.com/curlyboi/hashtopus) code
* 7zip binaries are compiled from https://sourceforge.net/projects/sevenzip/files/7-Zip/16.04/
