#!/bin/bash

if [ -f server.zip ]; then
  rm server.zip
fi

count=$(git log $(git describe --tags --abbrev=0)..HEAD --oneline | wc -l | tr -d ' ')
sed -i -E 's/BUILD = "repository"/BUILD = "'"$count"'"/g' ../src/inc/info.php
cd ../src/
zip -r ../ci/server.zip * -x "*.gitignore" -x "*.txt" -x "*README.md" -x "*generator.php"
cd ../ci/
sed -i -E 's/BUILD = "'"$count"'"/BUILD = "repository"/g' ../src/inc/info.php