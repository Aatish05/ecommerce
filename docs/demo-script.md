# Demonstration Script

## Person 1: Introduction and website overview

1. Introduce EcoTech Electronics as an electronics e-commerce website.
2. Explain the purpose: sell sustainable laptops, phones, audio devices, and smart home products.
3. Show the homepage:
   - Hero section
   - Sticky navigation
   - Footer
   - Dark/light mode
   - Cookie notice
4. Show responsive mode using browser developer tools.
5. Open the privacy policy and explain GDPR:
   - Approved in 2016
   - Enforced from May 25, 2018
   - Consent, data minimisation, user rights, password security

## Person 2: Database, backend, authentication

1. Open phpMyAdmin and show:
   - `users`
   - `categories`
   - `products`
   - `orders`
   - `order_items`
   - `contact_messages`
   - `reviews`
2. Explain foreign key relationships.
3. Show registration:
   - First submit invalid data to show validation.
   - Show that users must choose Customer or Vendor.
   - Then register a valid customer or vendor account.
4. Log in and explain:
   - PHP sessions
   - `password_hash()`
   - `password_verify()`
   - prepared statements
5. Add a product to cart and checkout.
6. Show the new order in phpMyAdmin or the user order page.
7. Add a product review and explain that admins can moderate reviews.

## Person 3: Admin, SEO, accessibility, security, reflection

1. Log out and log in as admin.
2. Show that admin pages are protected from normal users.
3. Open admin dashboard.
4. Show revenue cards, order history, product details, reviews, and the sales graph.
5. Demonstrate CRUD:
   - Create a product
   - Edit product price/stock
   - Delete or hide a product
6. Log in as the vendor account and show:
   - Adding a vendor product
   - Vendor product list
   - Vendor order history
   - Vendor revenue dashboard and sales graph
7. Update an order status.
8. Review a contact message and mark it as read.
9. Explain SEO:
   - Meta descriptions
   - Meaningful titles
   - Semantic HTML
   - Fast local SVG images
10. Explain accessibility:
   - Alt text
   - Labels
   - Heading structure
   - Keyboard navigation
   - Contrast and responsive layout
11. Reflection:
   - Each member briefly explains their contribution and what they learned.

## Demo checklist

- [ ] Registration shown
- [ ] Login shown
- [ ] Logout shown
- [ ] Admin/user roles shown
- [ ] Product CRUD shown
- [ ] Vendor product upload shown
- [ ] Admin revenue and sales graph shown
- [ ] Vendor revenue and order history shown
- [ ] Reviews shown
- [ ] Database update shown
- [ ] Search/filter shown
- [ ] Validation errors shown
- [ ] Contact form shown
- [ ] Responsive design shown
- [ ] Accessibility and SEO explained
- [ ] GDPR/privacy explained
- [ ] Individual contributions explained
