# Database optimization report — Alt Notification (2026-11-13)

Part of an audit of the database usage of all CUZY modules (November 2026). The changes were implemented, reviewed,
tested and merged into `main` (merge commit `4f0da9f`) for version 1.3.1. One regression found during the pre-release
review is fixed (uncommitted at the time of writing, see below).

## Findings and status

| ID | Finding | Status |
|---|---|---|
| ANT1 | Saving the configuration loads all active users and re-queries all spaces per user | Done |
| ANT2 | The member-added handler loads every enabled space GUID to test one space | Done (regression fixed in review) |

## What changed

- **Saving the configuration** (`Configuration::updateAllUsersNotificationSettings()`): users processed by batches of
  500 (`USERS_BATCH_SIZE`), one membership query per batch, and the selected spaces resolved once instead of once per
  user. Same result: users who changed their notification settings themselves are still skipped.
- **Member added to a space** (`Events::onSpaceMemberAdded()` → `Configuration::isSpaceSelectedForNewContentNotif()`):
  no query when "Notify Users for all Spaces" is on (the space only has to be enabled); otherwise the space GUID is
  checked against the configured list.
- No migration.

## Tests

New unit suite `ConfigurationTest`: space selection, batch update of the members' settings, member added to a selected
space, creator of a new space (added in the review). Suite on `main` with the fix: 4 tests, OK.

## Pre-release review

**Bug found and fixed:** with "Notify Users for all Spaces" on, the creator of a new space no longer got the space's
"New Content" notifications enabled. The creator is added as a member in `Space::afterSave()`, while the space object
has no `status` yet (the column default is applied by the database), so the new `status === enabled` check failed;
the former code read the enabled spaces from the database and found the new one. `isSpaceSelectedForNewContentNotif()`
now treats a missing status as the default (enabled) and compares the status as an integer. Test:
`testCreatorOfANewSpaceGetsTheNotificationSettingWhenAllSpacesAreSelected` (fails without the fix). CHANGELOG `Fix`
bullet added under 1.3.1.

The rest of the diff was reviewed against the code before the work (same users, same memberships, same spaces);
`module.json` (1.3.1) matches the CHANGELOG.
