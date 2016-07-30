# Hashtopussy 

This is a wrapper for hashcat for distributed hashcracking, based on the source code of Hashtopus. 

Notice: We decided to just publish Hashtopussy working with hashcat v3 and not focussing on also supporting the older versions. 
If you would like to use Hashtopussy with the older versions, please checkout commit cc3513b6e638beb0d70d75f44a2fbd0452c0ded7 where it was the state before adding hc v3 support.

Please visit the [wiki](https://bitbucket.org/seinlc/hashtopussy/wiki/Home) to get more information on setup and upgrade.

Some screenshots of Hashtopussy (by winxp5421): [Imgur](http://imgur.com/gallery/Fj0s0)

IMPORTANT: This branch currently is not working as there is a complete change in DB structure!!!!


## Thanks

* winxp for testing, writing help texts and a lot of input ideas
* blazer for modifying the agent to get it working with hashcat v3
* CynoSure Prime for testing (in Hashkiller Contest 2016)
* curlyboi for the original Hashtopus code

### What is working? 

* Templating
* Bootstrap GUI
* Right management system
* DBA system
* Addittions in SQL tables
* User management
* Rights management
* Server config
* Install script

### What is missing currently? 

* Fancy password analysis
* Some graphical informations about tasks