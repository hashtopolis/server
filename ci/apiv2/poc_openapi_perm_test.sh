#!/bin/sh
#
# PoC to use openapi.json for automated unit testing. This will test of 'GET' permission is available on all API listing endpoints'
#
for ENDPOINT in $(curl -s 'http://localhost:8080/api/v2/ui/openapi.json' | jq -r '.paths | keys[]' | grep -v '}'); do 
	echo -n "$ENDPOINT..."; 
	curl --header "Content-Type: application/json" -X GET --header "Authorization: Bearer $TOKEN" "http://localhost:8080$ENDPOINT" -s -d '{}' | grep -q '403' && echo "FAIL" || echo "OK"
done
