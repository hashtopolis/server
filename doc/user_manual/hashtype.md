# Hashtypes

Hashcat gets constantly developed and often new hashtypes get added. To be flexible Hashtopolis provides the possibility for the server admin to add new Hashcat algorithms. Even if you use a customized Hashcat with some special algorithm. To add a new type you just need to add the -m number of Hashcat and the name of it.

Salted says if a hash of this algorithm has a separate hash value (e.g. vBulletin), but this does not include algorithms which have the salt included in the full hash (e.g. bcrypt). This is a feature to help that when this algorithm is selected on hashlist import, the salted checkbox gets ticked automatically.

## Slow Algorithms

To extract all Hashcat modes which are flagged as slow hashes, following command can be run inside the hashcat directory:

```
grep -Hr SLOW_HASH src/modules/ | cut -d: -f1 | sort | cut -d'.' -f1 | sed 's/src\/modules\/module_[0]\?//g'
```
