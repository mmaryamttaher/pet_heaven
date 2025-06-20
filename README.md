# Pet Heaven - Pet Boarding Management System

A comprehensive pet boarding management system built with vanilla PHP, featuring user authentication, booking management, and an admin dashboard.

## Features

### User Features
- User registration and authentication
- Pet profile management
- Hotel search with filters (location, price, amenities)
- Online booking system
- Booking history and management
- User profile management

### Admin Features
- Comprehensive admin dashboard
- Hotel management (add, edit, activate/deactivate)
- Booking management (view, update status)
- User management
- Analytics and statistics

### System Features
- Responsive design with Bootstrap
- Secure password hashing
- Session management
- Input validation and sanitization
- Database-driven content

## Installation

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Instructions

1. **Start XAMPP**
   - Start Apache and MySQL services

2. **Copy Files**
   - Copy all project files to `c:\xampp\htdocs\pets_shop\`

3. **Database Configuration**
   - Open `config/database.php`
   - Update database credentials if needed (default: localhost, root, no password)

4. **Initialize Database**
   - Open your browser and go to `http://localhost/pets_shop/setup.php`
   - Click "Setup Database" to create tables and sample data

5. **Access the System**
   - Website: `http://localhost/pets_shop/`
   - Admin Login: `http://localhost/pets_shop/login.php`

## Default Credentials

### Admin Account
- **Email:** admin@petheaven.com
- **Password:** password

## File Structure

```
pets_shop/
├── config/
│   ├── config.php          # General configuration
│   └── database.php        # Database connection
├── includes/
│   ├── header.php          # Common header
│   └── footer.php          # Common footer
├── admin/
│   ├── dashboard.php       # Admin dashboard
│   ├── hotels.php          # Hotel management
│   └── bookings.php        # Booking management
├── database/
│   ├── schema.sql          # Database schema
│   └── init.php            # Database initialization
├── imgs/                   # Image assets
├── index.php               # Homepage
├── login.php               # User login
├── signup.php              # User registration
├── search.php              # Hotel search
├── book.php                # Booking form
├── booking.php             # User bookings
├── user.php                # User profile
├── hotel.php               # Hotel details
├── add_pet.php             # Add pet form
├── setup.php               # Database setup
└── README.md               # This file
```

## Database Schema

### Main Tables
- **users** - User accounts and profiles
- **hotels** - Hotel/boarding facility information
- **pets** - User pet profiles
- **bookings** - Booking records
- **reviews** - Hotel reviews and ratings
- **services** - Available services
- **messages** - Chat/messaging system

## Usage

### For Users
1. Register an account or login
2. Add your pet(s) to your profile
3. Search for available hotels
4. Make a booking
5. Manage your bookings and profile

### For Admins
1. Login with admin credentials
2. Access admin dashboard
3. Manage hotels, bookings, and users
4. View analytics and statistics

## Customization

### Adding New Hotels
- Use the admin dashboard to add new hotels
- Upload images to the `imgs/` folder
- Update amenities and pricing as needed

### Styling
- Main CSS files are referenced in each HTML page
- Bootstrap 5 is used for responsive design
- Custom styles can be added to existing CSS files

### Database
- Modify `database/schema.sql` for schema changes
- Update PHP files accordingly for new fields

## Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Input sanitization and validation
- Session-based authentication
- CSRF protection considerations

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP MySQL is running
   - Verify database credentials in `config/database.php`

2. **Images Not Loading**
   - Ensure images are in the `imgs/` folder
   - Check file paths in database records

3. **Permission Issues**
   - Ensure proper file permissions on web server
   - Check XAMPP configuration

### Support
For issues or questions, check the code comments or modify as needed for your specific requirements.

## License
This project is open source and available under the MIT License.
