# Course Recommender Block for Moodle

A Moodle block that helps users discover relevant courses based on their interests through an intuitive tag-based interface.

## Supported Languages

- English (en)
- German (de)
- Turkish (tr)
- French (fr)

## Features

- **Tag-Based Course Discovery**: Efficient course filtering through interest tags
- **Sorting Algorithm**: Courses are sorted by number of matching tags and then by creation date
- **Real-Time Updates**: Dynamic course recommendations without page reload
- **Responsive Design**: Optimized for desktop, tablet, and mobile devices
- **Card Layout**: Intelligent positioning in different block locations
- **Course Image Support**: Displays course images with fallback to placeholders

## Installation

1. Download the plugin
2. Copy it to your Moodle blocks directory: `/blocks/course_recommender`
3. Visit your Moodle site as an admin
4. Follow the installation prompts

## Usage

### For Administrators
1. Add the block to any page where you want to offer course recommendations
2. Ensure your courses have appropriate tags assigned
3. Watch as users discover courses they never knew existed

### For Teachers
1. Add relevant tags to your courses
2. Make sure your course has an eye-catching image
3. Your course will automatically appear in recommendations when it matches user interests

### For Students
1. Select your interests from the available tags
2. Watch as matching courses magically appear
3. Click on any course card to learn more
4. Discover new learning opportunities!

## Technical Requirements

- Moodle 4.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB equivalent)

## Configuration

The block is designed to work immediately after installation with zero configuration. However, for the best experience:

1. Ensure your courses have meaningful tags assigned
2. Consider adding course images for better visual appeal
3. Place the block in a prominent location on your course pages

## Privacy

This block is designed with privacy in mind:
- No personal data storage
- No tracking of user selections
- No cookies or local storage used

## Support

For support or to contribute to the development:

- Report issues via GitHub Issues
- Submit pull requests for improvements
- Contact: sadikmert@hotmail.de

### Feedback

I would greatly appreciate any feedback from users – ideas, suggestions, challenges, or any issues you encounter. Your input will help improve the plugin and guide future features.

## Credits

Developed by Sadik Mert, 2025
- Created for the Moodle community
- A strong belief that finding the right course shouldn't feel like finding a needle in a haystack

## License

GNU GPL v3 or later - http://www.gnu.org/copyleft/gpl.html

## Changelog

### Version 1.2.1 (2025-09-13)
- Allow customization of tag colors so they can match the company's corporate design. This will also be configurable in `settings.php`
- Limit the number of initial tags displayed. For example, show only X tags by default and allow the user to reveal more/all if desired. The initial count can be configured by admins in `settings.php`
- Admins can now choose how tags are sorted (by popularity, A-Z, or Z-A) via a new setting in `settings.php`.

### Version 1.2.0 (2025-09-02)
Improve tag selection UI and card filtering
- Made the tag selection area horizontally scrollable for better usability with many tags.
- Updated cards to display only the tags that are currently selected in the tag filter.

### Version 1.1.0 (2025-08-08)
- Switched to clickable badges for interest selection (no checkboxes)
- Tag list is now cached using the Moodle cache API for improved performance
- Added Turkish (tr) and French (fr) language support

### Version 1.0.0 (2025-07-20)
- Initial release
- Tag-based course recommendation
- Responsive design
- Real-time updates
- Course image support

## Planned Features
- Add a search function to allow users to search for specific tags, which is especially useful when a large number of tags are used on a page. This will also be configurable in `settings.php` (not yet implemented).