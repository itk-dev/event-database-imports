# Event database 2.0

This is the technical documentation for the project.

# Abstracted data flow

![System input data flow](./images/data_flow.png)

### Notes

* Clone events (UI)
* Soft delete
* Feed events not editable (feed is master)
* Tags (controlled and free (ukendt))
* Geo encoding filter
* Postel code filter (or filter)

# User handling

![User handling concept](./images/user_handling.png)

### Notes

* User expire
* User enable/disable (soft delete)
* Is context handler in play

# API access

![Api user creation flow](./images/api_user.png)
