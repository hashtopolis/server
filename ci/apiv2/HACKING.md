Examples
========

Common sequences of commands used in development setups

Initilise token:
```
TOKEN=$(curl -X POST --user root:hashtopolis http://localhost:8080/api/v2/auth/token | jq -r .token)
```


Fetch object:
```
curl --header "Content-Type: application/json" -X GET --header "Authorization: Bearer $TOKEN" 'http://localhost:8080/api/v2/ui/hashlists/1?expand=hashes' -d '{}'
```
