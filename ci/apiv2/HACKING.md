#### Examples
Common sequences of commands used in development setups

Initilise token:
```
TOKEN=$(curl -X POST --user admin:hashtopolis http://localhost:8080/api/v2/auth/token | jq -r .token)
```


Fetch object:
```
curl --compressed --header "Authorization: Bearer $TOKEN" -g 'http://localhost:8080/api/v2/ui/hashtypes?page[size]=5'
```

Access database:
```
mysql -u $HASHTOPOLIS_DB_USER -p$HASHTOPOLIS_DB_PASS -h $HASHTOPOLIS_DB_HOST -D $HASHTOPOLIS_DB_DATABASE
```

Enable query logging:
```
docker exec $(docker ps -aqf "ancestor=mysql:8.0") mysql -u root -phashtopolis -e "SET global log_output = 'FILE'; SET global general_log_file='/tmp/mysql_all.log'; SET global general_log = 1;"
docker exec $(docker ps -aqf "ancestor=mysql:8.0") tail -f /tmp/mysql_all.log
```

Shortcut for testing within development setup:
```
cd ~/src/hashtopolis/server/ci/apiv2
pytest --exitfirst --last-failed
```

### paper flipchart scribbles

#### v2 beta

# Items with '#' are (partially) implemented

* /api/v2/
  * ./agent   : for now aligning to the PHP-api
  * ./auth    : local OAuth provider
  * ./ui      : all queries for angular, cli etc.
#    * ./agents                      [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]
#      * ./{id}/healthchecks         [GET, POST]
#      * ./{id}/healthchecks/{id}    [GET, (PATCH?,) DELETE]
#      * ./{id}/healthcheckagents    [GET, POST]
#      * ./{id}/healthcheckagents/{id} [GET, (PATCH?,) DELETE]
#    * ./agentstats                  [GET]
#    * ./agentstats/${id}            [DELETE]
#    * ./agentbinaries               [GET, POST]
#    * ./agentbinaries/{id}          [GET, PATCH, DELETE]
#    * ./chunks                      [GET]
#      * ./{id}                      [GET]
      * ./{id}/abort                [POST]
      * ./{id}/reset                [POST]
#    * ./configs                     [GET, PATCH]
      * ./recount-cracked           [POST]
      * ./rescan-files              [POST]
    * ./configsections              [GET]
#    * ./crackers                    [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]
      * ./{id}/versions             [GET, POST]
      * ./{id}/versions/{id}        [GET, DELETE, PATCH]
    * ./fields                      [-]
      * ./notification-types        [GET]
      * ./notification-triggers     [GET]
#    * ./files                       [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]
#    * ./hashes                      [GET (output-format set in GET)]
#      * ./{id}                      [GET (output-format set in GET)]
#    * ./hashlists                   [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]
      * ./{id}/add-cracked          [POST]
      * ./{id}/export-cracked       [POST]
#    * ./hashtypes                   [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]
    * ./logs                        [GET]
      * ./{id}                      [GET]
#    * ./logentries                   [GET]
#      * ./{id}                      [GET]
#    * ./notifications               [GET, POST]
#      * ./{id}                      [GET, PATCH, ?DELETE?]
    * ./notification-settings       [GET, POST]
      * ./{id}                      [GET, PATCH, DELETE]
    * ./permissiongroups            [GET, POST]
      * ./{id}                      [GET, PATCH, DELETE]
#    * ./preprocessors               [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]
    * ./pretaskgroup                [GET, POST]
      * ./{id}                      [GET, PATCH, DELETE]
      * ./import/hcmask             [POST]
#    * ./pretasks                    [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]
#    * ./superhashlists              [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]
    * ./taskgroups                  [GET]
      * ./{tgid}                    [GET, PATCH, DELETE]
      * ./{tgid}/set-toppriority    [POST]
#    * ./tasks                       [GET, POST]
#      * ./{tid}                     [GET, PATCH, DELETE]
      * ./{tid}/unassign-agents     [POST]
      * ./{tid}/assign-agents       [POST]
      * ./{tid}/reset               [POST]
      * ./{tid}/set-toppriority     [POST]
    * ./tests                       [-]
      * ./connection                [GET]
      * ./access                    [GET]
#    * ./tokens                      [GET, POST]
#      * ./{tid}                     [GET, PATCH, DELETE]
#    * ./users/                      [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]
#    * ./vouchers                    [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]


#    * ./crackertypes                [GET, POST]
#      * ./{id}                      [GET, PATCH, DELETE]


# Type devs mapping static values (StatType), om generator (bvb DAgentStatsType) casten naar field types.

#### abbreviations used

* id
  * an ID within the respective scope

* tid
  * taskId (used to differentiate from tgid)

* tgid
  * taskGroupId (used to differentiate from tid)


#### additional notes

permissiongroups should be reflecting the following scopes:
  * user (global)
  * user (project)
  * team (project)
  * token


