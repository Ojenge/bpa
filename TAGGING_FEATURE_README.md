# Tagging Feature for Measures and Initiatives

## Overview
This feature adds the ability to tag measures and initiatives with status labels such as "approved" and "needs review". The tags are displayed in the My Data Entry page and can be managed through a user-friendly dialog interface.

## Features
- **Status Tags**: Add "approved" or "needs review" status to measures and initiatives
- **Quick Dropdown**: Inline dropdown in the Status column for instant status changes
- **Notes**: Add optional notes to each tag
- **Visual Indicators**: Status tags are displayed as colored badges in the data entry tables
- **Easy Management**: Simple dialog interface for adding, editing, and removing tags
- **Real-time Updates**: Tags are saved immediately and reflected in the UI

## Database Changes
The following database changes are required:

### 1. Initiative Table
Add a `tags` column to the `initiative` table:
```sql
ALTER TABLE `initiative` ADD COLUMN `tags` varchar(9000) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '[]' AFTER `lastUpdated`;
UPDATE `initiative` SET `tags` = '[]' WHERE `tags` IS NULL OR `tags` = '';
```

### 2. Measure Table
The `measure` table already has a `tags` column, so no changes are needed.

## Files Added/Modified

### New Files
1. **`add_tags_to_initiatives.sql`** - Database migration script
2. **`dataEntry/tag-functions.php`** - PHP functions for tag operations
3. **`js/tag-management.js`** - JavaScript for tag management UI
4. **`test_tags.php`** - Test file to verify functionality

### Modified Files
1. **`dataEntry/get-measures.php`** - Added status column and tag display
2. **`dataEntry/get-initiatives.php`** - Added status column and tag display
3. **`dataEntry/myDataEntry.php`** - Added tag management script and button integration

## Usage

### For Users
1. Navigate to the My Data Entry page
2. **Quick Status Change**: Use the dropdown in the Status column to instantly change status
   - Select "Approved" to mark as approved
   - Select "Needs Review" to mark for review
   - Select "Remove Status" to clear the status
3. **Advanced Tag Management**: Click the "Manage Tags" button next to any item for detailed management
   - Select a status (Approved or Needs Review)
   - Optionally add notes
   - Click "Add Tag" to add the tag
   - Click "Save All" to save all tags
   - Click "Cancel" to close without saving

### For Developers
The tagging system uses JSON to store tag data in the format:
```json
[
  {
    "status": "approved",
    "notes": "Optional notes here"
  },
  {
    "status": "needs_review",
    "notes": "Review required"
  }
]
```

## API Endpoints
The `tag-functions.php` file provides the following AJAX endpoints:

- `save_measure_tags` - Save tags for a measure
- `save_initiative_tags` - Save tags for an initiative
- `get_measure_tags` - Retrieve tags for a measure
- `get_initiative_tags` - Retrieve tags for an initiative

## Installation Steps
1. Run the database migration: `add_tags_to_initiatives.sql`
2. Ensure all new files are in the correct locations
3. Test the functionality using `test_tags.php`
4. Access the My Data Entry page to use the new tagging feature

## Visual Design
- **Approved**: Green badge with "Approved" text
- **Needs Review**: Yellow badge with "Needs Review" text
- **No Status**: Light gray badge with "No Status" text
- **Status Dropdown**: Small dropdown next to status badge for quick changes
- **Manage Tags Button**: Blue outline button next to Update links

## Browser Compatibility
The feature uses Dojo Toolkit and should work in all modern browsers that support the existing application.

## Troubleshooting
1. If tags don't appear, check that the database migration was run successfully
2. If the dialog doesn't open, ensure the JavaScript files are loaded correctly
3. If tags don't save, check the browser console for JavaScript errors
4. Verify that the user has appropriate permissions to access the data entry page

## Future Enhancements
Potential improvements for the tagging system:
- Add more status types (e.g., "in progress", "completed")
- Add tag filtering and search functionality
- Add tag-based reporting and analytics
- Add tag history and audit trail
- Add bulk tag operations for multiple items 