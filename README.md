# Task Manager Pro (Database)

A comprehensive task management system built with HTML, CSS, JavaScript, and PHP that allows users to track daily tasks across multiple shifts with detailed reporting and analytics.

## Features

- **Multi-User Support**: Manage tasks for multiple operators/users
- **Shift-Based Task Management**: Organize tasks by shifts (A, B, C, G)
- **Real-Time Progress Tracking**: Visual progress bars showing completion rates
- **Dashboard Analytics**: View statistics and charts for performance metrics
- **Task Editing Interface**: Add, edit, activate/deactivate, and delete tasks
- **User Management**: Add and remove users from the system
- **Reporting System**: Submit and view historical reports
- **Responsive Design**: Clean, modern UI that works on various screen sizes

## Tech Stack

- Frontend: HTML5, CSS3, JavaScript (ES6+)
- Backend: PHP 7+
- Database: MySQL
- Architecture: Client-server with REST-like API

## Installation

1. Clone this repository to your local server directory:
   ```bash
   git clone https://github.com/yourusername/task-manager-pro.git
   cd task-manager-pro
   ```

2. Set up your local server environment (XAMPP, WAMP, MAMP, etc.)

3. Import the database schema:
   - Create a MySQL database named `task_db`
   - Execute the SQL commands in `task_db.sql` to create tables and insert default data

4. Configure the database connection in `api.php`:
   ```php
   $host = 'localhost';
   $db   = 'task_db';
   $user = 'root';  // Your MySQL username
   $pass = '';      // Your MySQL password
   ```

5. Access the application through your web browser

## Database Schema

The application uses three main tables:

- `users`: Stores user information
- `tasks`: Contains tasks organized by shift
- `reports`: Stores submitted reports with completion statistics

## Usage

1. Open the application in your browser
2. Select a user from the dropdown menu
3. Choose a date and shift
4. Complete tasks by checking the checkboxes
5. Click "Submit Daily Report" to save your progress
6. Navigate to the Dashboard to view analytics and historical data
7. Use the Task List Editor to manage tasks for each shift
8. Use the User Management modal to add/remove users

## API Endpoints

The application uses a simple PHP API with the following endpoints:

- `GET /api.php?action=get_users` - Retrieve all users
- `POST /api.php?action=add_user` - Add a new user
- `POST /api.php?action=delete_user` - Delete a user
- `GET /api.php?action=get_tasks&shift=X` - Get tasks for a specific shift
- `POST /api.php?action=add_task` - Add a new task
- `POST /api.php?action=update_task` - Update a task's text
- `POST /api.php?action=toggle_task` - Toggle task active status
- `POST /api.php?action=delete_task` - Delete a task
- `GET /api.php?action=get_report_context&user=X&date=Y&shift=Z` - Get existing report context
- `POST /api.php?action=submit_report` - Submit daily report
- `GET /api.php?action=get_logs&start=X&end=Y&user=Z` - Get dashboard logs

## Customization

- Modify the default tasks in the `task_db.sql` file
- Adjust color scheme by changing CSS variables in the `:root` section
- Extend the shift system by adding more shift letters in the database and UI

## Security Notes

- The application uses basic SQL injection prevention with `real_escape_string`
- For production use, consider implementing prepared statements
- Update database credentials before deploying to production
- The API allows CORS from all origins; restrict this in production

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on how to contribute to this project.

## License

See [LICENSE](LICENSE) file for licensing information.
