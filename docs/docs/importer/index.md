---
sidebar_label: Overview
slug: /importer
---

# Data Importer

## Introduction

The **Importer** component of the DoSomething admin app enables staff to import users and activity data from third-party sources, either ingested via third-party API requests or by manual upload of a CSV file.

## CSV Data

The importer application supports CSV data imports of the following types:

- Voter registrations from Rock The Vote
- Email subscriptions to DoSomething newsletters
- User requests to mute promotional messaging by deleting their Customer.io profiles

When a CSV file is uploaded, each record in the file is parsed and dispatched as a job on a queue to be imported into the system. Any failed jobs in the queue are retried once daily (a setting configuerd via the Heroku Scheduler add-on). Failed jobs are infinitely retried until they are manually removed by an admin (via the Importer Admin UI) or a developer (via Heroku CLI).
