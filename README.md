# PharmaCare - Online Pharmacy E-Commerce Website

A full-featured pharmacy e-commerce website built with PHP, MySQL, and vanilla JavaScript.

## Features

### Customer Features
- Browse products by category
- Search products
- Product filtering (category, price range, prescription/OTC)
- Product sorting (price, name, date)
- Shopping cart with AJAX updates
- User registration and login
- User profile management
- Order history
- Prescription upload for Rx medications
- Responsive mobile-first design

### Admin Features
- Dashboard with statistics
- Product management (CRUD)
- Category management
- Order management with status updates
- User management
- Low stock alerts

## Tech Stack

- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Backend:** PHP 8.x
- **Database:** MySQL 8.x
- **Server:** XAMPP (Apache + MySQL)

## Requirements

- XAMPP (or any Apache + MySQL + PHP setup)
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Web browser with JavaScript enabled

## Installation

### Step 1: Install XAMPP

Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)

### Step 2: Clone/Copy Files

Copy the `pharmacy` folder to your XAMPP htdocs directory:
- **Windows:** `C:\xampp\htdocs\pharmacy`
- **macOS:** `/Applications/XAMPP/htdocs/pharmacy`
- **Linux:** `/opt/lampp/htdocs/pharmacy`

### Step 3: Start XAMPP

1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services

### Step 4: Create Database

1. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Create a new database named `pharmacy_db`
3. Select the database
4. Click "Import" tab
5. Choose the file `sql/pharmacy_db.sql` from the pharmacy folder
6. Click "Go" to import

### Step 5: Configure Database Connection

Edit `config/database.php` if your MySQL credentials differ from defaults:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pharmacy_db');
define('DB_USER', 'root');      // Change if needed
define('DB_PASS', '');          // Change if needed
```

### Step 6: Access the Website

Open your browser and navigate to:
- **Store:** [http://localhost/pharmacy/](http://localhost/pharmacy/)
- **Admin:** [http://localhost/pharmacy/admin/](http://localhost/pharmacy/admin/)

## Demo Accounts

### Admin Access
- **Email:** admin@pharmacy.com
- **Password:** password

### Customer Access
- **Email:** john.doe@email.com
- **Password:** password

## Project Structure

```
pharmacy/
├── index.php                 # Homepage
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── header.php            # Common header
│   ├── footer.php            # Common footer
│   └── functions.php         # Utility functions
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   ├── js/
│   │   └── main.js           # Frontend JavaScript
│   └── images/               # Product images
├── pages/
│   ├── products.php          # Product listing
│   ├── product-detail.php    # Single product view
│   ├── cart.php              # Shopping cart
│   ├── checkout.php          # Checkout process
│   ├── login.php             # User login
│   ├── register.php          # User registration
│   ├── profile.php           # User profile
│   └── search.php            # Search results
├── admin/
│   ├── index.php             # Admin dashboard
│   ├── products.php          # Manage products
│   ├── orders.php            # Manage orders
│   ├── categories.php        # Manage categories
│   ├── users.php             # Manage users
│   └── includes/
│       └── sidebar.php       # Admin sidebar
├── actions/
│   ├── auth.php              # Authentication handler
│   ├── cart-actions.php      # Cart AJAX handler
│   └── order-actions.php     # Order placement
├── uploads/
│   └── prescriptions/        # Prescription uploads
└── sql/
    └── pharmacy_db.sql       # Database schema
```

## Database Tables

- **users** - Customer and admin accounts
- **categories** - Product categories
- **products** - Product catalog
- **orders** - Customer orders
- **order_items** - Order line items
- **cart** - Shopping cart items

## Security Features

- Password hashing with `password_hash()` and `PASSWORD_DEFAULT`
- PDO prepared statements for all database queries
- CSRF token protection on all forms
- Input sanitization with `htmlspecialchars()`
- Session-based authentication
- File upload validation (type and size)

## Sample Data

The SQL file includes:
- 2 admin users
- 3 customer users
- 6 categories
- 24 products
- 5 sample orders

## Customization

### Adding Product Images

1. Add images to `assets/images/` folder
2. Update product records with image filenames
3. Supported formats: JPG, PNG, GIF, WebP

### Changing Colors

Edit CSS variables in `assets/css/style.css`:

```css
:root {
    --primary-color: #28a745;    /* Green */
    --secondary-color: #007bff;  /* Blue */
}
```

### Adding Categories

1. Login as admin
2. Go to Categories in admin panel
3. Add new category with name and description

## Troubleshooting

### Database Connection Error
- Verify XAMPP MySQL is running
- Check credentials in `config/database.php`
- Ensure `pharmacy_db` database exists

### Images Not Loading
- Check if images exist in `assets/images/`
- Verify file permissions
- Check image filenames in database match actual files

### Cart Not Working
- Ensure JavaScript is enabled
- Check browser console for errors
- Verify PHP session is working

## License

This project is created for educational purposes.

## Support

For issues or questions, please check:
1. XAMPP documentation
2. PHP documentation
3. MySQL documentation
