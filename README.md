# EcoTech Electronics E-commerce Website

EcoTech Electronics is a PHP + MySQL group project for an electronics e-commerce website. It is designed to satisfy the HD rubric requirements: authentication, admin/user roles, CRUD operations, database-driven pages, search/filtering, contact form, validation, accessibility, SEO, privacy/GDPR content, and professional documentation.

## Main features

- User registration with required customer/vendor account selection, login, logout, session handling, and profile editing
- Admin/user roles with protected admin routes
- Vendor/seller role with protected product, order, and revenue dashboard
- Product CRUD in the admin dashboard
- Vendor product creation and stock management
- Dynamic product listing and product detail pages loaded from MySQL
- Search, category, price, and sorting filters
- Shopping cart, checkout, order history, and admin order status updates
- Product reviews with admin moderation
- Revenue cards, order history tables, product detail analytics, and sales graphs
- Contact form stored in the database and admin message review
- Prepared statements, password hashing, CSRF tokens, and server-side validation
- Bootstrap 5 responsive layout, sticky navigation, footer, hero section, hover effects, and dark/light mode
- Accessibility basics: labels, alt text, semantic HTML, heading structure, contrast, keyboard-friendly controls
- SEO basics: page titles, meta descriptions, semantic sections, meaningful URLs/file names
- Privacy policy, terms, cookie notice, and GDPR discussion

## Technology stack

- PHP 8+
- MySQL or MariaDB
- phpMyAdmin/XAMPP for local database management
- Bootstrap 5 and Bootstrap Icons
- Vanilla JavaScript for client-side validation, cookie notice, and dark mode

## Setup with XAMPP/phpMyAdmin

1. Copy this project folder into your XAMPP `htdocs` directory.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open phpMyAdmin.
4. Import `database/schema.sql`.
5. Check database credentials in `config/config.php`.
   - Defaults: host `127.0.0.1`, database `ecotech_ecommerce`, user `root`, empty password.
   - You can also set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, and `APP_BASE_URL` environment variables.
6. Visit `http://localhost/<project-folder>/index.php`.

## Demo accounts

After importing the seed data:

- Admin: `admin@example.com` / `Admin123!`
- Vendor: `vendor@example.com` / `Vendor123!`
- User: `user@example.com` / `User123!`

For final submission, create a stronger admin password from the user interface or update the database with a new `password_hash()` value.

## Suggested demo flow

1. Show the homepage, responsive navigation, dark mode, privacy/cookie notice, and footer.
2. Search/filter products from the database.
3. Register a new user and show validation errors first, including the required Customer/Vendor account type selection.
4. Log in, add a product to cart, checkout, and view order history.
5. Write a product review and show admin review moderation.
6. Log out, log in as admin, and show protected dashboard access, revenue, sales graph, product details, and order history.
7. Create, edit, and delete a product in admin CRUD.
8. Log in as the vendor and show vendor product upload, order history, and revenue dashboard.
9. Update an order status and review a contact message.
10. Explain prepared statements, password hashing, CSRF, sessions, WCAG practices, SEO, and GDPR page.

## Project structure

```text
admin/              Protected admin dashboard, analytics, CRUD, orders, reviews, messages, users
assets/             CSS, JavaScript, and SVG product images
config/             Database/session configuration
database/schema.sql MySQL schema and seed data
docs/               Report, demo script, and contribution logs
includes/           Shared functions, header, footer, product card
vendor/             Protected vendor dashboard, product upload, vendor order history
*.php               Public storefront pages
```

## Security notes

- All database operations use PDO prepared statements.
- Passwords are hashed with `password_hash()` for newly registered users.
- Login verifies passwords with `password_verify()`.
- Session IDs are regenerated after login.
- Admin pages call `require_admin()` before rendering.
- POST forms include CSRF tokens.
- Server-side validation is used even when client-side validation is present.
