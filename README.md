# Workflow Process
[![PHP Composer](https://github.com/creatortsv/workflow/actions/workflows/php.yml/badge.svg)](https://github.com/creatortsv/workflow/actions/workflows/php.yml)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/creatortsv/workflow-process)

This stand-alone PHP package helps to create and organize processes using stages, which can be easily configured to build different workflows
***
## Installation
Install this package using composer
```
composer require creatortsv/workflow-process
```
## Configuration
Package uses PHP 8.0 attributes to configure different aspects of a process. There are only four attributes:

- `Support\Workflow`
- `Support\Stage`
- `Support\Transition`
- `Support\Artifacts`