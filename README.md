WesnickWorkflowBundle
==================

### This bundle is a work-in-progress.

[![Build Status](https://travis-ci.org/wesnick/api-platform-workflow-extension.svg?branch=master)](https://travis-ci.org/wesnick/api-platform-workflow-extension)
[![Build Status](https://scrutinizer-ci.com/g/wesnick/api-platform-workflow-extension/badges/build.png?b=master)](https://scrutinizer-ci.com/g/wesnick/api-platform-workflow-extension/build-status/master)

Introduction
------------

This bundle tries to allow relative easy integration between the API Platform and Symfony's Workflow component

The goal is:
 - implement [actions as outlined in schema.org](https://schema.org/docs/actions.html) 
 - automatically expose workflow information as part of the resource payload.
  - contains information about all available transitions
  - contains available but blocked transitions with ConstraintViolation messages 
 - automatically expose endpoints to execute transitions.
   - workflows should try to do all mutations and persistence using listeners

- Workflows that support API Platform resources can be toggled to include workflow data and operations
  - Workflow transition information is added to the resource representation as an array of [potentialAction](https://schema.org/docs/actions.html)
    ```json
    {
      "id": 1,
      "name": "Bob",
      "potentialAction": [
        {
          "@type": "ControlAction",
          "target": {
            "@type": "Entrypoint",
            "httpMethod": "PATCH",
            "url": "/users/1?workflow=user_status&transition=ban_user"
          }
        }
      ]
    }
    
    ```
  -  If any potential action has a workflow transition blocker, these are displayed as an Api Platform ConstraintViolation class type under the property error
    ```json
    {
      "id": 1,
      "name": "Bob",
      "potentialAction": [
        {
          "@type": "ControlAction",
          "name": "ban",
          "description": "Ban a User",
          "target": {
            "@type": "Entrypoint",
            "httpMethod": "PATCH",
            "url": "/users/1?workflow=user_status&transition=ban_user"
          },
          "error": [{
            "message": "User must be enabled before banning.",
            "propertyPath": "/enabled"
          }]
        }
      ]
    }
    ```
  - The return type for workflows transition executions is always the subject.  The subject will have updated potentialAction information as part of the response payload.  If your transition succeeded 200, if it failed 400 with a ConstraintViolationList.  
  - Depending on how you use workflows, you may or may not have data input for transitions. If there is no data, you can just PATCH a simple payload with the transition to the PATCH API endpoint. If your workflow transition requires data input, you can use the DTO feature to provide a custom input class

### Example
Here are some example payloads with a basic user promote demote workflow

GET a User
```
GET http://localhost/api/users/5
Content-Type: application/ld+json

{
  "@context": "\/api\/contexts\/User",
  "@id": "\/api\/users\/5",
  "@type": "User",
  "id": 5,
  "email": "erik.ledner@gmail.com",
  "lastLogin": null,
  "expired": false,
  "locked": false,
  "credentialsExpired": false,
  "enabled": true,
  "roles": [
    "ROLE_ADMIN"
  ],
  "potentialAction": [
    {
      "@context": "http:\/\/schema.org",
      "@type": "Action",
      "actionStatus": "PotentialActionStatus",
      "target": {
        "httpMethod": "PATCH",
        "url": "\/api\/users\/5?workflow=user_role&transition=promote_to_superadmin"
      },
      "name": "promote_to_superadmin",
      "description": "Promote to Super Admin"
    },
    {
      "@context": "http:\/\/schema.org",
      "@type": "Action",
      "actionStatus": "PotentialActionStatus",
      "target": {
        "httpMethod": "PATCH",
        "url": "\/api\/users\/5?workflow=user_role&transition=demote_to_user"
      },
      "name": "demote_to_user",
      "description": "Demote to User"
    }
  ]
}
```
Execute demote_to_user transition
```
PATCH http://localhost/api/users/5?workflow=user_role
Content-Type: application/ld+json

{
  "transition": "demote_to_user"
}
```
Response
```json
{
  "@context": "\/api\/contexts\/User",
  "@id": "\/api\/users\/5",
  "@type": "User",
  "id": 5,
  "email": "erik.ledner@gmail.com",
  "lastLogin": null,
  "expired": false,
  "locked": false,
  "credentialsExpired": false,
  "enabled": true,
  "roles": [
    "ROLE_USER"
  ],
  "potentialAction": [
    {
      "@context": "http:\/\/schema.org",
      "@type": "Action",
      "actionStatus": "PotentialActionStatus",
      "target": {
        "httpMethod": "PATCH",
        "url": "\/api\/users\/5?workflow=user_role&transition=promote_to_admin"
      },
      "name": "promote_to_admin",
      "description": "Promote to Admin"
    },
    {
      "@context": "http:\/\/schema.org",
      "@type": "Action",
      "actionStatus": "PotentialActionStatus",
      "target": {
        "httpMethod": "PATCH",
        "url": "\/api\/users\/5?workflow=user_role&transition=promote_to_superadmin"
      },
      "name": "promote_to_superadmin",
      "description": "Promote to Super Admin"
    }
  ]
}
```  


Features
--------

- Validation Helpers
- React admin integration (WIP)

Documentation
-------------

- enable the module
```yml
# config/packages/wesnick_workflow.yaml

wesnick_workflow:
    api_patch_transitions: true      # default
    workflow_validation_guard: true  # default
```

- To enable API support for your workflows:

  - Subject class must implement PotentialActionInterface
  - You can implement this interface either with PotentialActionsTrait or on your own, be sure to set serialization groups appropriately. The bundle automatically pushes the group ```workflowAction:output``` during denormalization. 
 - add descriptive messages for your workflow/transitions using workflow metadata



Roadmap
-------

License
-------

This bundle is released under the MIT license. See the included
[LICENSE](src/Resources/meta/LICENSE) file for more information.
