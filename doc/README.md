## MKDocs Local Setup

1. Make sure you are in the root of the server project and setup a virtual environment there.
2. Install mkdocs
3. Install required mkdocs extensions
4. Start the server
5. Browse to http://127.0.0.1:8000

``` bash
cd hashtopolis
virtualenv venv
source venv/bin/activate
pip3 install mkdocs
pip3 install $(mkdocs get-deps)
mkdocs server
```

When testing the API reference you need to retrieve the openapi.json file from the Hashtopolis server (e.g. via `http://localhost:8080/api/v2/openapi.json) and place it inside this folder.
