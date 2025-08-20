import requests
import subprocess
import json
import sys
import os
import re


class HashType:
    def __init__(self, hashType, description, salted, slowHash):
        self.hashType = hashType
        self.description = description
        self.salted = salted
        self.slowHash = slowHash


url = "https://raw.githubusercontent.com/hashcat/hashcat/refs/tags/v7.1.1/docs/hashcat-example-hashes.md"
binary = sys.argv[1]  # The hashcat binary is the first argument
if not os.path.isfile(binary):
    print("usage: python3 update-hashes.py <hashcat binary>")
args = [binary, "-m", "PLACEHOLDER", "--exam", "--machine-readable", "--quiet"]
new_hashtypes = []

response = requests.get(url)
response.raise_for_status()  

lines = response.text.splitlines()

auth_uri = 'http://localhost:8080/api/v2/auth/token'
auth = ("admin", "hashtopolis")  # default credentials
r = requests.post(auth_uri, auth=auth)
token = r.json()["token"]

headers = {
    'Authorization': 'Bearer ' + token
}
url = "http://localhost:8080/api/v2/ui/hashtypes/"
print("Retrieving hashes from db please wait...")

for line in lines[4:]:  # skip first 4 header lines
    splitted = line.split("|")
    if len(splitted) == 1:  # table is finished
        break
    hashType = re.search(r"\[`?(\d+)`?\]", splitted[1]).group(1)
    description = splitted[2].strip().split("`")[1]
    print(description)
    r = requests.get(url + hashType, headers=headers)
    if (r.status_code != 200):
        args[2] = hashType
        hashcat_output = subprocess.run(args, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        data = json.loads(hashcat_output.stdout)
        slow_hash = data[hashType]["slow_hash"]
        salted = data[hashType]["is_salted"]
        new_hashtypes.append(HashType(hashType, description, salted, slow_hash))
print("Finished retrieving hashes!")

print("Add the following to the update script:")
print('if (!isset($PRESENT["PLACEHOLDER"])){')
print("  $hashTypes = [")
for hashType in new_hashtypes:
    print(f'    new HashType( {hashType.hashType}, "{hashType.description}", {int(hashType.salted)}, {int(hashType.slowHash)}),')
print("   ];")
print('  foreach ($hashTypes as $hashtype) {')
print('    $check = Factory::getHashTypeFactory()->get($hashtype->getId());')
print('    if ($check === null) {')
print('      Factory::getHashTypeFactory()->save($hashtype);')
print('    }')
print('  }')
print('  $EXECUTED["PLACEHOLDER"] = true;')
print('}')

print("Add the following to the install script:")
for hashType in new_hashtypes:
    print(f"  ({hashType.hashType},    '{hashType.description}', {int(hashType.salted)}, {int(hashType.slowHash)}),")


print("Dont forgot to check if all hashtypes where salted = '1', are actually salted in a way that Hashtopolis understands!")