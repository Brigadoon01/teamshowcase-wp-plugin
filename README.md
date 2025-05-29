# Team Showcase WordPress Plugin

A responsive, feature-rich team showcase plugin for WordPress with filtering, search, and pagination capabilities.

## Features

- **Responsive Design** - Works seamlessly on all screen sizes
- **Real-time Search** - Search by name or job title
- **Department Filtering** - Filter team members by department
- **Pagination** - Navigate through team members efficiently
- **Hover Animations** - Smooth scale and shadow effects
- **Social Links** - LinkedIn and Twitter integration
- **Gutenberg Block** - Easy integration with the block editor
- **Shortcode Support** - Use in classic editor or widgets
- **Custom Post Type** - Manage team members easily
- **ACF Integration** - Advanced Custom Fields for team member details

## Installation

1. Upload the `team-showcase` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Team Members' in the admin menu to add team members
4. Use the Gutenberg block or shortcode to display the team showcase

## Usage

### Gutenberg Block

1. Add a new block and search for "Team Showcase"
2. Configure the block settings in the sidebar:
   - Items Per Page
   - Show/Hide Search
   - Show/Hide Department Filter

### Shortcode

Use the shortcode with optional parameters:

\`\`\`
[team_showcase items_per_page="6" show_search="true" show_department_filter="true"]
\`\`\`

### Parameters

- `items_per_page` - Number of team members to display per page (default: 6)
- `show_search` - Show or hide the search input (default: true)
- `show_department_filter` - Show or hide the department filter (default: true)

## Adding Team Members

1. Go to 'Team Members' in the admin menu
2. Click 'Add New'
3. Enter the team member's name as the title
4. Add a featured image for the team member's photo
5. Fill in the custom fields:
   - Job Title
   - LinkedIn URL
   - Twitter URL
6. Assign the team member to a department using the Department taxonomy
7. Publish the team member

## Customization

### CSS Customization

You can add custom CSS to your theme to override the default styles:

\`\`\`css
/* Example: Change card background color */
.team-showcase-card {
    background-color: #f9f9f9;
}

/* Example: Change hover effect */
.team-showcase-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}
\`\`\`

### Hooks and Filters

The plugin provides several hooks and filters for developers to extend functionality:

\`\`\`php
// Example: Modify team member data before output
add_filter('team_showcase_member_data', function($member_data, $post_id) {
    // Modify data here
    return $member_data;
}, 10, 2);
\`\`\`

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- Advanced Custom Fields plugin (free version is sufficient)

## Support

For support, feature requests, or bug reports, please create an issue on the GitHub repository or contact the plugin author.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- Icons by [Feather Icons](https://feathericons.com/)
- Built with love for the WordPress community
