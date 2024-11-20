# WordPress Custom Date Range Calendar

A WordPress plugin that allows you to set custom date ranges for your posts and display them in an interactive calendar widget.

Demo and Illustrations: [Custom Date Range Calendar](https://wputopia.com/insights/custom-date-range-calendar)

## Features

- Set custom date ranges for posts (e.g., events spanning multiple days)
- Interactive AJAX-powered calendar widget
- Month/Year dropdown selectors for quick navigation
- Highlights dates with associated posts
- Shows all posts within a date range when clicking any date in that range
- Maintains calendar state during navigation
- Responsive design for all screen sizes
- Clear visual indicators for dates with posts
- Loading states for better user experience

## Installation

1. Download the plugin files from "realeases"
2. Install the zip file as other WordPress plugins


## Usage

### Setting Date Ranges

1. Edit or create a new post
2. Look for the "Post Date Range" meta box in the sidebar
3. Set the start and end dates for your post
4. If no dates are set, the post's publication date will be used

### Adding the Calendar Widget

1. Go to Appearance > Widgets in your WordPress admin
2. Find the "Date Range Calendar" widget
3. Add it to your desired sidebar or widget area

### Viewing Posts

- Click any highlighted date to see posts associated with that date
- Use the month/year dropdowns for quick navigation
- Use the prev/next buttons to navigate between months
- The calendar will show a notice indicating which date range you're viewing

## Customization

### Styling

The plugin comes with default styles, but you can customize them by adding CSS to your theme. Main CSS classes include:

```css
.post-calendar          /* Main calendar container */
.calendar-nav          /* Navigation buttons container */
.calendar-table        /* Calendar grid */
.has-posts            /* Dates with associated posts */
.calendar-selectors   /* Month/year dropdown container */
.calendar-archive-notice /* Notice showing current date range */
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- JavaScript enabled in the browser

## License

This project is licensed under the GPL v2 or later.

## Credits

- Original concept from [WP Utopia](https://wputopia.com/insights/custom-date-range-calendar)
- Development and enhancements by [Your Name]

## Support

For issues, feature requests, or contributions, please submit them to the GitHub repository's issue tracker.

## Changelog

### 1.0.0
- Initial release
- Basic calendar functionality
- Date range support
- AJAX navigation
- Responsive design
- Month/year selector dropdowns

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Submit a pull request

## Screenshots

[Add screenshots of your calendar in action here]

## FAQ

**Q: Can I use this for events?**  
A: Yes, this plugin is perfect for events or any content that spans multiple days.

**Q: Does it support different languages?**  
A: The calendar uses WordPress's built-in localization, so it will display in your site's language.

**Q: Will it work with my theme?**  
A: The plugin is designed to be compatible with most WordPress themes and includes responsive styling.
