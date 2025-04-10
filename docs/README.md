# Alternative notifications

Replaces the "Receive 'New Content' Notifications for the following spaces" Notification Setting with a new behavior.

Solves issue https://github.com/humhub/humhub/issues/7043

## How it works

### Terminology

| Term            | Location |  
|-----------------|----------|  
| **User Settings**  | "Your Account" → "Notifications" → *"Receive 'New Content' Notifications for the following spaces"* |  
| **Admin Settings** | "Admin" → "Settings" → "Notifications" → *"Receive 'New Content' Notifications for the following spaces"*` |  
| **Module Settings** | `Alternative Notifications` module config → *"Select Spaces for which Users should be notified about new content upon becoming a member."* |  

### The problem

With the default HumHub behavior, when adding Public Spaces to **Admin Settings**, users which are not a member of these Public Spaces are notified about new content.

### User Types before using this module

| Type | Description |  
|------|-------------|  
| **A** | Users with default (uncustomized) **User Settings**. |  
| **B** | Users with explicitly configured **User Settings**. |  

Users A will have the same Space list as the one define in **Admin Settings**.

### Module behavior

#### 1. Removal of **Admin Settings** and **Type A** users

- All Spaces defined in **Admin Settings** will be **removed**.
- **Type A** users will no longer exist (no default Spaces).

#### 2. One-Time Migration

When the module is enabled:
- Copy all Spaces from **Admin Settings** to **Module Settings**.
- For **Type A** users: Add all Spaces the User is a member of, from **Module Settings**, to their **User Settings**.

#### 3. Ongoing Rules

- **When a user joins a Space**:
    - If the Space is in **Module Settings**, auto-add it to their **User Settings**.
- **When a Space is added to Module Settings**:
    - **No retroactive action**.
    - Only affects users joining the Space **after** the addition.

## Pricing

This module is free, but the result of a lot of work for design and maintenance over time.

If it's useful to you, please consider [making a donation](https://www.cuzy.app/checkout/donate/) or [participating in the code](https://github.com/cuzy-app/clean-theme). Thanks!

## Repository

https://github.com/cuzy-app/alt-notification

## Publisher

[CUZY.APP](https://www.cuzy.app/)

## Licence

[GNU AGPL](https://github.com/cuzy-app/alt-notification/blob/master/docs/LICENCE.md)
