# Slow Algorithms

To extract all Hashcat modes which are flagged as slow hashes, following command can be run inside the hashcat directory:

```
grep -Hr SLOW_HASH src/modules/ | cut -d: -f1 | sort | cut -d'.' -f1 | sed 's/src\/modules\/module_[0]\?//g'
```
