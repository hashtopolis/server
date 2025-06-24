# User Settings

This section describes the account settings that each user can set.

## Account Settings

The account settings offer an easy way to change the stored e-mail address of the currently logged in user as well as the password. When changing the password, the use of a 12-digit password using the entire character set leads to the visualization of a particularly strong password.

## UI Settings

The UI settings offer the option of adjusting the date and time format to your own preferences and switching between dark and light mode.

## Notifications
It is possible to be informed about various events via different channels. The following channels are currently supported:

- Chatbot
- Discord
- Email
- Telegram
- Slack

A notification can be triggered for the following triggers (if you are allowed to receive this information depending on your access group):

- **agentError**: When one of the agents throws an error
- **ownAgentError**: When your agent (as agent owner) throws an error
- **deleteAgent**: When an agent was deleted
- **newTask**: When a new Task was created
- **TaskComplete**: When a Task was completed
- **deleteTask**: When a task was deleted
- **newHashlist**: When a new hashlist was created
- **deleteHashlist**: When a hashlist was deleted
- **hashlistAllCracked**: When all hashes in a hashlist got cracked
- **hashlistCrackedHash. When any hash got cracked - **warning**: You could receive a lot of notifications if you try to crack a large hashlist.
- **userCreated**: When a new user was created
- **userDeleted**: When a user was deleted
- **userLoginFailed**: When a user login failed
- **logWarn**: When there are logs on warn level
- **logFatal**: When there are logs on fatal level
- **logError**: When there are logs on error level

To set up notification, click on the **New Notification** button. The trigger and then the channel are selected.

To complete the setup of the notifications, a so-called recipient must be specified. This can be an e-mail address or a special Telegram token, for example. To find the right receiver, please refer to the external documentation of the channels offered, as this can change constantly in a dynamic environment.