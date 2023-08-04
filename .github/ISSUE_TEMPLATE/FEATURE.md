name: Feature
description: Request a new feature or functionality
title: "[FEATURE]: "
labels: ["feature"]
assignees:
- octocat
  body:
- type: markdown
  attributes:
  value: |
  Please try to describe the desired new functionality in detail, i.e. by providing example behaviour, needed actions, handling etc.
- type: textarea
  id: description
  attributes:
  label: Description
  description: 
  value: ""
  validations:
  required: true
