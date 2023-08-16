name: Epic
description: Feature issue for large additions/changes. Should only be used by Collaborators.
title: "[EPIC]: "
labels: ["epic"]
assignees:
- octocat
  body:
- type: markdown
  attributes:
  value: |
  Please try to describe the desired new addition in detail, i.e. by providing example behaviour, needed actions, handling etc.
- type: textarea
  id: description
  attributes:
  label: Description
  description: 
  value: ""
  validations:
  required: true
