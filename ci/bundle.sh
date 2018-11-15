#!/bin/bash

if [ -f server.zip ]; then
  rm server.zip
fi

count=$(git log $(git describe --tags --abbrev=0)..HEAD --oneline | wc -l)
if [ ${count} \> 0 ];
then
    sed -i -E 's/VERSION = "([0-9]+)\.([0-9]+)\.([0-9]+)"/VERSION = "\1.\2.\3\+'$count'"/g' ../src/inc/info.php
fi;
cd ../src/
zip -r ../ci/server.zip * -x "*.gitignore" -x "*.txt" -x "*README.md" -x "*generator.php"
cd ../ci/
if [ ${count} \> 0 ];
then
    sed -i -E 's/VERSION = "([0-9]+)\.([0-9]+)\.([0-9]+)\+([0-9]+)"/VERSION = "\1.\2.\3"/g' ../src/inc/info.php
fi;