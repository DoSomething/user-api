# User - Optional Fields

## Overview

Following an [Access Control & Auditing](https://docs.google.com/document/d/1OclKWYEtjo0LTI9DzKaVG1dEBY6kfAEEgYZVRzSlD7s/edit) audit, [we implemented](https://github.com/DoSomething/northstar/pull/907) a security feature on the `GET` `v2/users/:id` endpoint to ensure that any fields marked as "sensitive" (fields containing personally identifiable information) would need to be explicitly queried so that we can log the request.

Fields are marked as "sensitive" via the `$sensitive` property on the User model.

## Usage

To include such fields in the response, the request should include an `include` query parameter with a list of optional fields to include.

For example, if we wanted to query for the user's street address, we might request:

`v2/users/5571f4f5a59dbf3c7a8b4569?include=addr_street1,addr_street2`
