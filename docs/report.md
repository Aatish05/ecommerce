# EcoTech Electronics Website Report

## 1. Introduction

EcoTech Electronics is a dynamic e-commerce website for electronic products such as laptops, smartphones, audio devices, and smart home technology. The purpose of the website is to demonstrate a complete PHP and MySQL online store with secure accounts, product management, ordering, accessibility, SEO, privacy awareness, and professional usability.

The business concept is a sustainable electronics shop. It promotes energy-efficient products, repair-friendly ownership, honest product information, and responsible data handling.

## 2. Technologies Used

- **HTML5**: Semantic page structure with headings, navigation, sections, tables, forms, and footer content.
- **CSS3**: Custom styling in `assets/css/styles.css` for the hero section, cards, hover effects, dark mode support, and responsive adjustments.
- **Bootstrap 5**: Responsive grid, navbar, cards, alerts, buttons, forms, tables, badges, and utility classes.
- **Bootstrap Icons**: Visual support for navigation, feature cards, cart actions, and admin dashboard.
- **JavaScript**: Client-side validation, dark/light mode toggle, confirmation prompts, and cookie notice.
- **PHP**: Server-side pages, sessions, authentication, validation, admin access control, CRUD, cart, checkout, and contact form processing.
- **MySQL**: Relational database storing users, categories, products, orders, order items, and contact messages.
- **PDO**: Prepared database statements to reduce SQL injection risk.

## 3. Backend Design

### Database tables

The database is defined in `database/schema.sql`.

| Table | Purpose |
| --- | --- |
| `users` | Stores registered customers and administrators. |
| `categories` | Stores product categories such as laptops and smartphones. |
| `products` | Stores product details, prices, stock, images, and active status. |
| `orders` | Stores checkout records linked to users. |
| `order_items` | Stores products and quantities inside each order. |
| `contact_messages` | Stores contact form submissions for admin review. |
| `reviews` | Stores customer product reviews and moderation status. |

### Relationships

- One vendor/seller has many products.
- One category has many products.
- One user has many orders.
- One order has many order items.
- One product can appear in many order items.
- One product has many reviews.

Text ER diagram:

```text
users/customers (1) ----< orders (1) ----< order_items >---- (1) products >---- (1) categories
users/vendors (1) ----< products
users/customers (1) ----< reviews >---- (1) products

contact_messages is independent and managed by admins.
```

### Authentication system

The website uses PHP sessions to store the currently logged-in user. Registration stores new passwords using:

```php
password_hash($password, PASSWORD_DEFAULT);
```

Login uses:

```php
password_verify($password, $user['password_hash']);
```

After successful login, `session_regenerate_id(true)` is called to reduce session fixation risk. Admin-only pages call `require_admin()`, which redirects non-admin users away from protected pages.

## 4. Features

### Public website features

- Homepage with hero section and feature summary.
- Dynamic product cards loaded from the database.
- Product listing page with search, category filter, maximum price filter, and sorting.
- Individual product detail pages.
- Product reviews and rating summaries.
- Shopping cart stored in the session.
- Checkout for logged-in users.
- Order history and order detail pages.
- Contact form saved to MySQL.
- Privacy policy, terms and conditions, and cookie notice.

### Admin features

- Protected admin dashboard.
- Professional revenue dashboard with sales graph, order history, product analytics, and review summaries.
- Product CRUD: create, read, update, and delete products.
- Admin search for products.
- Product details page showing stock, revenue, units sold, order history, and reviews.
- Order status updates.
- Review moderation.
- Contact message review and mark-as-read action.
- User role management for admin, vendor, and customer accounts.

### Vendor features

- Protected vendor dashboard.
- Vendor product upload and editing.
- Vendor order history for products they sell.
- Vendor revenue tracking and sales graph.
- Vendor product performance table.

### Suggested screenshots to include before submission

1. Homepage hero section.
2. Product search/filter results.
3. Registration validation error.
4. Successful login dropdown.
5. Cart and checkout page.
6. Admin dashboard.
7. Product create/edit form.
8. phpMyAdmin showing tables and relationships.

## 5. Accessibility

The website follows WCAG-inspired accessibility practices:

- Forms use visible `<label>` elements.
- Images include descriptive `alt` text.
- Pages use logical heading order (`h1`, `h2`, `h3`).
- Navigation is keyboard accessible through standard links and buttons.
- A skip link lets keyboard users jump to main content.
- Bootstrap focus styles remain visible.
- Text and buttons use strong colour contrast.
- Responsive font sizes and layouts improve readability on mobile screens.
- Tables include captions and clear column headings.

## 6. Validation and Security

### Validation

Client-side validation is implemented with HTML attributes and JavaScript in `assets/js/app.js`. Server-side validation is implemented in PHP for registration, login, contact form, checkout, and admin product forms.

Examples:

- Required fields are checked.
- Email addresses use `filter_var(..., FILTER_VALIDATE_EMAIL)`.
- Passwords require at least 8 characters with letters and numbers.
- Duplicate usernames and emails are blocked.
- Product price and stock are validated.
- Contact messages must include a meaningful message.

### Security

- Prepared statements are used for all database queries with user input.
- New passwords are stored using `password_hash()`.
- Login checks use `password_verify()`.
- Admin pages are protected with role checks.
- Session IDs are regenerated after login.
- CSRF tokens protect POST forms.
- Output is escaped with `htmlspecialchars()`.
- Users cannot access admin pages unless their role is `admin`.

## 7. SEO Techniques

The site includes:

- Meaningful page titles.
- Meta descriptions and keywords.
- Semantic HTML elements such as `header`, `nav`, `main`, `section`, and `footer`.
- Descriptive link text and product names.
- Fast-loading local SVG images.
- Mobile responsive design using Bootstrap.
- Clean, understandable PHP page names such as `products.php`, `product.php`, and `privacy.php`.

## 8. Privacy and Ethics

The project includes a privacy policy and terms page. The privacy policy explains what data is collected, why it is collected, and how it is protected.

The report and website mention GDPR. The General Data Protection Regulation was approved in 2016 and enforced from May 25, 2018. Important GDPR principles applied in the project include:

- **Consent**: The cookie notice explains essential session cookies.
- **Data minimisation**: The site collects only data needed for account, order, and support functions.
- **Security**: Passwords are hashed and admin areas are restricted.
- **Transparency**: The privacy policy explains what data is stored.
- **User rights**: Users can request correction or deletion of personal data.

Ethically, the store avoids misleading product claims, encourages sustainable technology, and limits admin access to legitimate support and fulfilment activities.

## 9. AI Usage

AI-assisted code generation was reviewed, modified, tested, and integrated manually. AI helped with:

- Structuring the PHP/MySQL project.
- Drafting validation and access-control patterns.
- Creating report sections based on the marking rubric.
- Suggesting demo flow and documentation structure.

Example prompts used:

- "Build a PHP and MySQL electronics e-commerce website with login, roles, CRUD, search, and cart."
- "Add GDPR, accessibility, SEO, validation, and security features for an HD-level student report."
- "Create a demo script showing registration, login, database update, CRUD, and validation errors."

The final code was customised to match the EcoTech Electronics topic and the rubric requirements. AI was used ethically as an assistant, not as a replacement for understanding, testing, or project ownership.

## 10. Challenges and Solutions

| Challenge | Solution |
| --- | --- |
| Protecting admin pages | Added `require_admin()` and role checks before rendering admin content. |
| Preventing SQL injection | Used PDO prepared statements with bound parameters. |
| Avoiding weak registration data | Added client-side and server-side validation for all important fields. |
| Making pages dynamic | Stored products, orders, users, and messages in MySQL rather than static HTML. |
| Supporting mobile users | Used Bootstrap responsive grid and navbar components. |
| Addressing privacy rubric | Added privacy policy, terms, cookie notice, and GDPR explanation. |

## 11. Reflection

This project improved our understanding of full-stack web development. We learned how frontend usability, backend security, database design, accessibility, SEO, and privacy all connect in a professional website. The most important lesson was that a high-quality website is not only about appearance; it also requires secure data handling, validation, clear documentation, and a reliable user experience.

Individual reflections should be added by each group member before submission, including their tasks, challenges, and learning outcomes.

## 12. Conclusion

EcoTech Electronics is a complete dynamic e-commerce website built with PHP and MySQL. It includes authentication, role-based access, product CRUD, cart and checkout, order management, contact form, validation, accessibility, SEO, privacy, and documentation. These features directly address the rubric and demonstrate a professional, secure, and user-friendly web application.
