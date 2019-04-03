WesnickWorkflowBundle
==================

Introduction
------------

This bundle tries to allow relative easy integration between the API Platform and Symfony's Workflow component

The goal is to automatically expose workflow information as part of the resource payload.

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
            "httpMethod": "PUT",
            "url": "/users/1/ban"
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
            "httpMethod": "PUT",
            "url": "/users/1/ban"
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
  - Depending on how you use workflows, you may or may not have data input for transitions. If there is no data, you can just PUT (POST??) and empty payload to the generated API endpoint. If your workflow transition requires data input, you can use the DTO feature to provide a custom input class  

Features
--------

- Validation Helpers
- React admin integration (WIP)

Documentation
-------------

Roadmap
-------

License
-------

This bundle is released under the MIT license. See the included
[LICENSE](src/Resources/meta/LICENSE) file for more information.
