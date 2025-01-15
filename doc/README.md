# Hashtopolis

> [!CAUTION]
> This is a new page that will be used for documentation. It is work in progress, so use with care!



## Hashtopolis Protocol

The current up-to-date protocol version which Hashtopolis uses to communicate with clients is contained in the `protocol.pdf` file. 
The documentation for the User API can be found in `user-api/user-api.pdf`, listing all functions which can be called.

## Generic Crackers

Custom crackers which should be able to get distributed with Hashtopolis need to fulfill some minimal requirements as command line options. Shown here with the help function of a generic example implementation (which is available [here](https://github.com/hashtopolis/generic-cracker)):

```
cracker.exe [options] action
Generic Cracker compatible with Hashtopolis

Options:
  -m, --mask <mask>                   Use mask for attack
  -w, --wordlist <wordlist>           Use wordlist for attack
  -a, --attacked-hashlist <hashlist>  Hashlist to attack
  -s, --skip <skip>                   Keyspace to skip at the beginning
  -l, --length <length>               Length of the keyspace to run
  --timeout <seconds>                 Stop cracking process after fixed amount of time

Arguments:
  action                              Action to execute ('keyspace' or 'crack')
```

`-m` and `-w` are used to specify the type of attack, but these options are not mandatory to look like this.

Please note that not all Hashtopolis clients are compatible with generic cracker binaries (check their README) and if there are slight differences in the cracker compared to the generic requirements there might be changes required on the client to adapt to another handling schema.

## Slow Algorithms

To extract all Hashcat modes which are flagged as slow hashes, following command can be run inside the hashcat directory:

```
grep -Hr SLOW_HASH src/modules/ | cut -d: -f1 | sort | cut -d'.' -f1 | sed 's/src\/modules\/module_[0]\?//g'
```
