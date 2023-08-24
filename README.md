# Hashtopolis

<img src="https://github.com/hashtopolis/server/blob/master/src/static/logo.png" alt='Hashtopolis' width="100">

[![CodeFactor](https://www.codefactor.io/repository/github/hashtopolis/server/badge)](https://www.codefactor.io/repository/github/hashtopolis/server)
[![LoC](https://tokei.rs/b1/github/hashtopolis/server?category=code)](https://github.com/hashtopolis/server)
[![Hashtopolis Build](https://github.com/hashtopolis/server/actions/workflows/ci.yml/badge.svg)](https://github.com/hashtopolis/server)

Hashtopolis is a multi-platform client-server tool for distributing hashcat tasks to multiple computers. The main goals for Hashtopolis's development are portability, robustness, multi-user support, and multiple groups management.
The application has two parts:

- **Agent** Python client, easily customizable to suit any need.
- **Server** several PHP/CSS files operating on two endpoints: an Admin GUI and an Agent Connection Point

Aiming for high usability even on restricted networks, Hashtopolis communicates over HTTP(S) using a human-readable, hashing-specific dialect of JSON.

The server part runs on PHP using MySQL as the database back end. It is vital that your MySQL server is configured with performance in mind. Queries can be very expensive and proper configuration makes the difference between a few milliseconds of waiting and disastrous multi-second lags. The database schema heavily profits from indexing. Therefore, if you see a hint about pre-sorting your hashlist, please do so.

The web admin interface is the single point of access for all client agents. New agent deployments require a one-time password generated in the New Agent tab. This reduces the risk of leaking hashes or files to rogue or fake agents.

There are parts of the documentation and wiki which are not up-to-date. If you see anything wrong or have questions on understanding descriptions, join our Discord server at https://discord.gg/S2NTxbz.

To report a bug, please create an issue and try to describe the problem as accurately as possible. This helps us to identify the bug and see if it is reproducible.

In an effort to make the Hashtopussy project conform to a more politically neutral name it was rebranded to "Hashtopolis" in March 2018.

## Features

- Easy and comfortable to use
- Dark and light theme
- Accessible from anywhere via web interface or user API
- Server component highly compatible with common web hosting setups
- Unattended agents
- File management for word lists, rules, ...
- Self-updating of both Hashtopolis and Hashcat
- Cracking multiple hashlists of the same hash type as though they were a single hashlist
- Running the same client on Windows, Linux and macOS
- Files and hashes marked as "secret" are only distributed to agents marked as "trusted"
- Many data import and export options
- Rich statistics on hashes and running tasks
- Visual representation of chunk distribution
- Multi-user support
- User permission levels
- Various notification types
- Small and/or CPU-only tasks
- Group assignment for agents and users for fine-grained access-control
- Compatible with crackers supporting certain flags
- Report generation for executed attacks and agent status
- Multiple file distribution variants

## Setup and Usage

Please visit the [wiki](https://github.com/hashtopolis/server/wiki) for more information on setup and upgrade.

Some screenshots of Hashtopolis (by winxp5421 and s3in!c): [Imgur1](http://imgur.com/gallery/Fj0s0) [Imgur2](http://imgur.com/gallery/LzTsI)

## Contribution Guidelines

We are open to all kinds of contributions. If it's a bug fix or a new feature, feel free to create a pull request. Please consider some points:

* Just include one feature or one bugfix in one pull request. In case you have two new features please also create two pull requests.
* Try to stick with the code style used (especially in the PHP parts). IntelliJ/PHPStorm users can get a code style XML [here](https://gist.github.com/s3inlc/226ed78b05eb6dc8f60f18d6fd310d74).

The pull request will then be reviewed by at least one member and merged after approval. Don't be discouraged just because the first review is not approved, often these are just small changes.

## Thanks

* winxp5421 for testing, writing help texts and a lot of input ideas
* blazer for working on the csharp agent and hops for working on the python agent
* Cynosure Prime for testing
* atom for [hashcat](https://github.com/hashcat/hashcat)
* curlyboi for the original [Hashtopus](https://github.com/curlyboi/hashtopus) code
* 7zip binaries are compiled from [here](https://sourceforge.net/projects/sevenzip/files/7-Zip/16.04/)
* uftp binaries are compiled from [here](http://uftp-multicast.sourceforge.net/)
