## MKDocs Local Setup

1. Make sure you are in the root of the server project and setup a virtual enviroment there.
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
