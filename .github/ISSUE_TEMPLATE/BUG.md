name: Bug Report
description: File a bug report
title: "[BUG]: "
labels: ["bug"]
assignees:
- octocat
  body:
- type: markdown
  attributes:
  value: |
    Before you submit a bug report please make sure that the same bug was not reported previously. 
    Further, make sure to include as much information as possible to help identifying it.
- type: input
  id: version
  attributes:
  label: Version Information
  description: Which version(s) of the backend, frontend and ui (depending on the bug) are you using.
  validations:
  required: true
- type: input
  id: hashcat
  attributes:
  label: Hashcat
  description: If applicable, which version of Hashcat were you running?
  validations:
  required: false
- type: textarea
  id: description
  attributes:
  label: Description
  description: Describe your issue in as much detail as possible, this may include the exact task command you are trying to run, debug output from the client by running "hashtopolis.exe -d" or with debug flag set on the python client and steps to reproduce.
  value: "<<It's broke>> is not a description."
  validations:
  required: true
