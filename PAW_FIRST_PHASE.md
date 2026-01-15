# PharmaCare - Project Analysis & Planning Document
## First Phase Documentation

---

## Table of Contents
1. [Website Objectives](#1-website-objectives)
2. [Project Development Phases](#2-project-development-phases)
3. [User Journeys](#3-user-journeys)
4. [Activity Diagrams](#4-activity-diagrams)

5. [Database Design](#5-database-design)

---

## 1. Website Objectives

### 1.1 Primary Business Objectives

| Objective | Description | Success Metrics |
|-----------|-------------|-----------------|
| **Online Pharmacy Sales** | Enable customers to purchase medications, vitamins, and healthcare products online | Number of orders, revenue generated |
| **Prescription Management** | Allow customers to upload prescriptions for controlled medications | Prescription orders processed |
| **Customer Retention** | Provide user accounts with order history and profile management | Returning customer rate |
| **Inventory Management** | Track product stock levels and alert admins of low inventory | Stock accuracy, reduced stockouts |

### 1.2 Functional Objectives

#### Customer-Facing Features
- **Product Discovery**: Browse products by category, search, filter by price/prescription requirement
- **Shopping Cart**: Add products, modify quantities, view totals with real-time updates
- **Secure Checkout**: Complete purchases with shipping information and prescription uploads
- **User Accounts**: Register, login, manage profile, view order history, change password
- **Order Tracking**: View order status (pending, processing, shipped, delivered)

#### Administrative Features
- **Dashboard Analytics**: View total orders, revenue, customer count, pending orders
- **Product Management**: Create, edit, delete products with images and stock tracking
- **Category Management**: Organize products into logical categories
- **Order Processing**: Update order statuses, view order details and prescriptions
- **User Management**: View customers, manage user roles (customer/admin)
- **Low Stock Alerts**: Automatic notifications for products below threshold

### 1.3 Technical Objectives

| Objective | Implementation |
|-----------|----------------|
| **Security** | Password hashing, prepared statements, CSRF protection, input sanitization |
| **Responsiveness** | Mobile-first CSS design, responsive navigation |
| **Performance** | AJAX cart operations, optimized database queries |
| **Scalability** | Modular PHP architecture, separation of concerns |

### 1.4 Product Categories

The platform supports 6 primary product categories:
1. Pain Relief
2. Cold & Flu
3. Vitamins & Supplements
4. First Aid
5. Personal Care
6. Prescription Medications

---

## 2. Project Development Phases

### 2.1 Gantt Chart Overview

```
Phase                          | Week 1 | Week 2 | Week 3 | Week 4 | Week 5 | Week 6 |
-------------------------------|--------|--------|--------|--------|--------|--------|
1. Planning & Design           | ██████ | ████   |        |        |        |        |
2. Database & Core Setup       |        | ██████ | ████   |        |        |        |
3. Customer Features           |        |        | ██████ | ██████ |        |        |
4. Admin Panel                 |        |        |        | ██████ | ████   |        |
5. Testing & Refinement        |        |        |        |        | ██████ | ████   |
6. Deployment                  |        |        |        |        |        | ██████ |
```

### 2.2 Phase Details

#### Phase 1: Planning & Design (Week 1-2)
| Task | Deliverables |
|------|--------------|
| Requirements gathering | Feature list, user stories |
| Database design | ER diagram, table schemas |
| UI/UX wireframes | Page layouts, navigation flow |
| Technology stack selection | PHP, MySQL, CSS architecture |

#### Phase 2: Database & Core Setup (Week 2-3)
| Task | Deliverables |
|------|--------------|
| Database creation | 6 tables (users, categories, products, orders, order_items, cart) |
| Configuration files | Database connection, constants |
| Utility functions | Sanitization, session management, flash messages |
| Header/Footer templates | Common page structure |

#### Phase 3: Customer Features (Week 3-4)
| Task | Deliverables |
|------|--------------|
| Homepage | Hero section, featured products, categories |
| Product pages | Listing with filters, detail pages, search |
| Authentication | Registration, login, logout, remember me |
| Shopping cart | AJAX add/update/remove, guest cart support |
| Checkout | Form validation, prescription upload, order creation |
| User profile | Profile editing, order history, password change |

#### Phase 4: Admin Panel (Week 4-5)
| Task | Deliverables |
|------|--------------|
| Dashboard | Statistics cards, recent orders, low stock alerts |
| Product management | CRUD operations, image upload |
| Category management | CRUD operations |
| Order management | Status updates, order details view |
| User management | Role assignment, customer list |

#### Phase 5: Testing & Refinement (Week 5-6)
| Task | Deliverables |
|------|--------------|
| Functional testing | All user flows verified |
| Security testing | SQL injection, XSS, CSRF checks |
| Responsive testing | Mobile, tablet, desktop views |
| Performance optimization | Query optimization, caching |
| Bug fixes | Issue resolution |

#### Phase 6: Deployment (Week 6)
| Task | Deliverables |
|------|--------------|
| Server setup | Production environment configuration |
| Database migration | Production data setup |
| SSL configuration | HTTPS implementation |
| Final testing | Production environment verification |
| Launch | Go-live |

### 2.3 Milestone Summary

| Milestone | Target | Status |
|-----------|--------|--------|
| M1: Database & Core Complete | End of Week 2 | Complete |
| M2: Customer Features Complete | End of Week 4 | Complete |
| M3: Admin Panel Complete | End of Week 5 | Complete |
| M4: Testing Complete | Mid Week 6 | Complete |
| M5: Production Launch | End of Week 6 | Complete |

---

## 3. User Journeys

### 3.1 Guest User Journey - Product Discovery to Registration

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Landing   │───>│   Browse    │───>│   Product   │───>│  Add to     │
│    Page     │    │  Products   │    │   Detail    │    │   Cart      │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                                                                │
                                                                v
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Order     │<───│  Complete   │<───│   Login/    │<───│  Checkout   │
│ Confirmation│    │   Checkout  │    │  Register   │    │   Page      │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
```

**Steps:**
1. Guest visits homepage and sees hero banner and featured products
2. Browses products by category or uses search/filters
3. Views product detail page with description and dosage info
4. Adds product to cart (stored by session ID)
5. Proceeds to checkout
6. Prompted to login or register
7. Completes registration with email verification
8. Guest cart merges with new user account
9. Fills shipping information and uploads prescription (if required)
10. Completes order and receives confirmation

---

### 3.2 Registered Customer Journey - Repeat Purchase

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    Login    │───>│   Browse/   │───>│  Add to     │───>│  View Cart  │
│             │    │   Search    │    │   Cart      │    │             │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                                                                │
                                                                v
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Profile   │<───│   Order     │<───│  Complete   │<───│  Checkout   │
│  (Orders)   │    │ Confirmation│    │   Payment   │    │   Form      │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
```

**Steps:**
1. Customer logs in (with optional "Remember Me")
2. Browses products or searches for specific items
3. Adds items to cart with desired quantities
4. Reviews cart and adjusts quantities if needed
5. Proceeds to checkout (shipping info pre-filled from profile)
6. Uploads prescription if ordering Rx medications
7. Confirms order
8. Views order in profile under "Orders" tab
9. Tracks order status updates

---

### 3.3 Customer Journey - Profile Management

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    Login    │───>│   Profile   │───>│   Update    │
│             │    │    Page     │    │   Details   │
└─────────────┘    └─────────────┘    └─────────────┘
                         │
                         ├───────────────┬───────────────┐
                         v               v               v
                  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
                  │   Profile   │ │   Orders    │ │  Security   │
                  │    Tab      │ │    Tab      │ │    Tab      │
                  └─────────────┘ └─────────────┘ └─────────────┘
```

**Profile Tab Actions:**
- Update first name, last name
- Update phone number
- Update shipping address

**Orders Tab Actions:**
- View order history
- See order status (pending, processing, shipped, delivered)
- View item count and total for each order

**Security Tab Actions:**
- Change password (requires current password)
- Password strength validation

---

### 3.4 Admin Journey - Order Processing

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Admin     │───>│  Dashboard  │───>│   Orders    │───>│   Order     │
│   Login     │    │             │    │    List     │    │   Detail    │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                         │                                      │
                         v                                      v
                  ┌─────────────┐                        ┌─────────────┐
                  │  Low Stock  │                        │   Update    │
                  │   Alert     │                        │   Status    │
                  └─────────────┘                        └─────────────┘
```

**Steps:**
1. Admin logs in and is redirected to dashboard
2. Reviews statistics (orders, revenue, low stock alerts)
3. Clicks on pending order count or navigates to Orders
4. Filters orders by status if needed
5. Clicks on order to view details
6. Reviews order items, shipping address, prescription file
7. Updates order status (pending → processing → shipped → delivered)
8. Customer receives updated status in their profile

---

### 3.5 Admin Journey - Product Management

```
┌─────────────┐    ┌─────────────┐    ┌─────────────────────────────────┐
│  Dashboard  │───>│  Products   │───>│         Product Actions         │
│             │    │    List     │    │  ┌───────┬───────┬───────────┐  │
└─────────────┘    └─────────────┘    │  │ Add   │ Edit  │  Delete   │  │
                                      │  │ New   │       │           │  │
                                      │  └───────┴───────┴───────────┘  │
                                      └─────────────────────────────────┘
```

**Add Product:**
- Enter name, select category
- Write description, set price
- Set stock quantity
- Mark if prescription required
- Add dosage information
- Upload product image
- Set active/inactive status

**Edit Product:**
- Modify any product field
- Update stock levels
- Change product image
- Toggle active status

**Delete Product:**
- Only allowed if product has no associated orders
- Confirmation required

---

## 4. Activity Diagrams

### 4.1 User Registration Activity Diagram

```
                        ┌─────────────────┐
                        │      Start      │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │  Display Reg.   │
                        │      Form       │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │   User Fills    │
                        │   Form Fields   │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │ Submit Form     │
                        └────────┬────────┘
                                 │
                                 v
                   ┌─────────────────────────────┐
                   │   Validate Required Fields   │
                  /└─────────────────────────────┘\
                 /                                 \
           [Invalid]                           [Valid]
               │                                   │
               v                                   v
      ┌─────────────────┐              ┌─────────────────┐
      │  Show Error     │              │  Check Email    │
      │  Messages       │              │  Exists?        │
      └────────┬────────┘             /└─────────────────┘\
               │                     /                     \
               │               [Exists]                [Not Exists]
               │                   │                        │
               │                   v                        v
               │          ┌─────────────────┐    ┌─────────────────┐
               │          │ Show "Email     │    │ Validate        │
               │          │ Already Used"   │    │ Password        │
               │          └────────┬────────┘   /└─────────────────┘\
               │                   │           /                     \
               │                   │      [Weak]                 [Strong]
               │                   │         │                       │
               │                   │         v                       v
               │                   │  ┌─────────────┐     ┌─────────────────┐
               │                   │  │Show Password│     │  Hash Password  │
               │                   │  │Requirements │     │  Create User    │
               │                   │  └──────┬──────┘     └────────┬────────┘
               │                   │         │                     │
               └───────────────────┴─────────┘                     v
                                                         ┌─────────────────┐
                                                         │  Merge Guest    │
                                                         │  Cart (if any)  │
                                                         └────────┬────────┘
                                                                  │
                                                                  v
                                                         ┌─────────────────┐
                                                         │  Auto-Login     │
                                                         │  Create Session │
                                                         └────────┬────────┘
                                                                  │
                                                                  v
                                                         ┌─────────────────┐
                                                         │ Redirect to     │
                                                         │ Homepage        │
                                                         └────────┬────────┘
                                                                  │
                                                                  v
                                                         ┌─────────────────┐
                                                         │      End        │
                                                         └─────────────────┘
```

---

### 4.2 Add to Cart Activity Diagram

```
                        ┌─────────────────┐
                        │      Start      │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │ User Clicks     │
                        │ "Add to Cart"   │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │ AJAX Request    │
                        │ to Server       │
                        └────────┬────────┘
                                 │
                                 v
                   ┌─────────────────────────────┐
                   │    Check Product Stock      │
                  /└─────────────────────────────┘\
                 /                                 \
          [Out of Stock]                    [In Stock]
               │                                   │
               v                                   v
      ┌─────────────────┐              ┌─────────────────┐
      │  Return Error   │              │  Check User     │
      │  "Out of Stock" │              │  Logged In?     │
      └────────┬────────┘             /└─────────────────┘\
               │                     /                     \
               │              [Guest]                  [Logged In]
               │                 │                          │
               │                 v                          v
               │        ┌─────────────────┐      ┌─────────────────┐
               │        │ Use Session ID  │      │ Use User ID     │
               │        │ for Cart        │      │ for Cart        │
               │        └────────┬────────┘      └────────┬────────┘
               │                 │                        │
               │                 └────────────┬───────────┘
               │                              │
               │                              v
               │                 ┌─────────────────────────────┐
               │                 │    Check if Product in Cart │
               │                /└─────────────────────────────┘\
               │               /                                 \
               │         [Exists]                           [New Item]
               │             │                                   │
               │             v                                   v
               │    ┌─────────────────┐              ┌─────────────────┐
               │    │ Update Quantity │              │ Insert New      │
               │    │ (Add to existing)│              │ Cart Item       │
               │    └────────┬────────┘              └────────┬────────┘
               │             │                                │
               │             └────────────┬───────────────────┘
               │                          │
               │                          v
               │                 ┌─────────────────┐
               │                 │ Return Success  │
               │                 │ with New Count  │
               │                 └────────┬────────┘
               │                          │
               └──────────────────────────┤
                                          v
                                 ┌─────────────────┐
                                 │ Update Cart     │
                                 │ Badge (UI)      │
                                 └────────┬────────┘
                                          │
                                          v
                                 ┌─────────────────┐
                                 │      End        │
                                 └─────────────────┘
```

---

### 4.3 Checkout Process Activity Diagram

```
                        ┌─────────────────┐
                        │      Start      │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │ User Clicks     │
                        │ "Checkout"      │
                        └────────┬────────┘
                                 │
                                 v
                   ┌─────────────────────────────┐
                   │      User Logged In?        │
                  /└─────────────────────────────┘\
                 /                                 \
              [No]                               [Yes]
               │                                   │
               v                                   │
      ┌─────────────────┐                         │
      │ Redirect to     │                         │
      │ Login Page      │                         │
      └────────┬────────┘                         │
               │                                   │
               v                                   │
      ┌─────────────────┐                         │
      │ User Logs In    │                         │
      └────────┬────────┘                         │
               │                                   │
               └───────────────────────────────────┤
                                                   │
                                                   v
                                         ┌─────────────────┐
                                         │ Display Checkout│
                                         │ Form            │
                                         └────────┬────────┘
                                                  │
                                                  v
                                         ┌─────────────────┐
                                         │ Pre-fill User   │
                                         │ Profile Data    │
                                         └────────┬────────┘
                                                  │
                                                  v
                                    ┌─────────────────────────────┐
                                    │  Cart Has Rx Products?      │
                                   /└─────────────────────────────┘\
                                  /                                 \
                               [Yes]                              [No]
                                 │                                  │
                                 v                                  │
                        ┌─────────────────┐                        │
                        │ Show Prescription│                        │
                        │ Upload Field     │                        │
                        └────────┬────────┘                        │
                                 │                                  │
                                 └──────────────────────────────────┤
                                                                    │
                                                                    v
                                                           ┌─────────────────┐
                                                           │ User Submits    │
                                                           │ Order           │
                                                           └────────┬────────┘
                                                                    │
                                                                    v
                                                      ┌─────────────────────────────┐
                                                      │   Validate All Fields       │
                                                     /└─────────────────────────────┘\
                                                    /                                 \
                                              [Invalid]                           [Valid]
                                                  │                                   │
                                                  v                                   v
                                         ┌─────────────────┐              ┌─────────────────┐
                                         │ Show Validation │              │ Check Stock     │
                                         │ Errors          │              │ Availability    │
                                         └────────┬────────┘             /└─────────────────┘\
                                                  │                     /                     \
                                                  │           [Insufficient]             [Available]
                                                  │                   │                       │
                                                  │                   v                       v
                                                  │          ┌─────────────────┐   ┌─────────────────┐
                                                  │          │ Show Stock      │   │ Process         │
                                                  │          │ Error           │   │ Prescription    │
                                                  │          └────────┬────────┘   └────────┬────────┘
                                                  │                   │                     │
                                                  └───────────────────┘                     v
                                                                                  ┌─────────────────┐
                                                                                  │ Create Order    │
                                                                                  │ Record          │
                                                                                  └────────┬────────┘
                                                                                           │
                                                                                           v
                                                                                  ┌─────────────────┐
                                                                                  │ Create Order    │
                                                                                  │ Items           │
                                                                                  └────────┬────────┘
                                                                                           │
                                                                                           v
                                                                                  ┌─────────────────┐
                                                                                  │ Update Product  │
                                                                                  │ Stock           │
                                                                                  └────────┬────────┘
                                                                                           │
                                                                                           v
                                                                                  ┌─────────────────┐
                                                                                  │ Clear Cart      │
                                                                                  └────────┬────────┘
                                                                                           │
                                                                                           v
                                                                                  ┌─────────────────┐
                                                                                  │ Redirect to     │
                                                                                  │ Order Confirm   │
                                                                                  └────────┬────────┘
                                                                                           │
                                                                                           v
                                                                                  ┌─────────────────┐
                                                                                  │      End        │
                                                                                  └─────────────────┘
```

---

### 4.4 Order Processing (Admin) Activity Diagram

```
                        ┌─────────────────┐
                        │      Start      │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │ Admin Views     │
                        │ Order List      │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │ Select Order    │
                        │ to Process      │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │ View Order      │
                        │ Details         │
                        └────────┬────────┘
                                 │
                                 v
                   ┌─────────────────────────────┐
                   │  Order Has Prescription?    │
                  /└─────────────────────────────┘\
                 /                                 \
              [Yes]                              [No]
               │                                   │
               v                                   │
      ┌─────────────────┐                         │
      │ Review          │                         │
      │ Prescription    │                         │
      │ File            │                         │
      └────────┬────────┘                         │
               │                                   │
               └───────────────────────────────────┤
                                                   │
                                                   v
                                    ┌─────────────────────────────┐
                                    │    Current Status?          │
                                   /└─────────────────────────────┘\
                                  /          |           \          \
                            [Pending]  [Processing]  [Shipped]  [Delivered]
                                 │           │           │           │
                                 v           v           v           │
                        ┌────────────┐ ┌────────────┐ ┌────────────┐ │
                        │ Update to  │ │ Update to  │ │ Update to  │ │
                        │ Processing │ │ Shipped    │ │ Delivered  │ │
                        └─────┬──────┘ └─────┬──────┘ └─────┬──────┘ │
                              │              │              │        │
                              v              v              v        │
                        ┌────────────────────────────────────────────┤
                        │                                            │
                        v                                            │
               ┌─────────────────┐                                   │
               │ Save Status     │                                   │
               │ Change          │                                   │
               └────────┬────────┘                                   │
                        │                                            │
                        v                                            │
               ┌─────────────────┐                                   │
               │ Display Success │<──────────────────────────────────┘
               │ Message         │
               └────────┬────────┘
                        │
                        v
               ┌─────────────────┐
               │      End        │
               └─────────────────┘
```

**Order Status Workflow:**
```
┌──────────┐     ┌────────────┐     ┌──────────┐     ┌───────────┐
│ Pending  │────>│ Processing │────>│ Shipped  │────>│ Delivered │
└──────────┘     └────────────┘     └──────────┘     └───────────┘
      │
      │          ┌────────────┐
      └─────────>│ Cancelled  │
                 └────────────┘
```

---

### 4.5 User Login Activity Diagram

```
                        ┌─────────────────┐
                        │      Start      │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │ Display Login   │
                        │ Form            │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │ User Enters     │
                        │ Credentials     │
                        └────────┬────────┘
                                 │
                                 v
                        ┌─────────────────┐
                        │ Submit Form     │
                        └────────┬────────┘
                                 │
                                 v
                   ┌─────────────────────────────┐
                   │      Find User by Email     │
                  /└─────────────────────────────┘\
                 /                                 \
          [Not Found]                          [Found]
               │                                   │
               v                                   v
      ┌─────────────────┐              ┌─────────────────┐
      │ Show "Invalid   │              │ Verify Password │
      │ Credentials"    │             /└─────────────────┘\
      └────────┬────────┘            /                     \
               │               [Invalid]               [Valid]
               │                   │                       │
               │                   v                       v
               │          ┌─────────────────┐   ┌─────────────────┐
               │          │ Show "Invalid   │   │ Regenerate      │
               │          │ Credentials"    │   │ Session ID      │
               │          └────────┬────────┘   └────────┬────────┘
               │                   │                     │
               └───────────────────┘                     v
                                              ┌─────────────────┐
                                              │ Store User Data │
                                              │ in Session      │
                                              └────────┬────────┘
                                                       │
                                                       v
                                         ┌─────────────────────────────┐
                                         │    "Remember Me" Checked?   │
                                        /└─────────────────────────────┘\
                                       /                                 \
                                    [Yes]                              [No]
                                      │                                  │
                                      v                                  │
                             ┌─────────────────┐                        │
                             │ Set 30-day      │                        │
                             │ Cookie          │                        │
                             └────────┬────────┘                        │
                                      │                                  │
                                      └──────────────────────────────────┤
                                                                         │
                                                                         v
                                                              ┌─────────────────┐
                                                              │ Merge Guest     │
                                                              │ Cart to User    │
                                                              └────────┬────────┘
                                                                       │
                                                                       v
                                                         ┌─────────────────────────────┐
                                                         │       User Role?            │
                                                        /└─────────────────────────────┘\
                                                       /                                 \
                                                  [Admin]                          [Customer]
                                                     │                                  │
                                                     v                                  v
                                            ┌─────────────────┐              ┌─────────────────┐
                                            │ Redirect to     │              │ Redirect to     │
                                            │ Admin Dashboard │              │ Homepage        │
                                            └────────┬────────┘              └────────┬────────┘
                                                     │                                │
                                                     └────────────────────────────────┤
                                                                                      │
                                                                                      v
                                                                             ┌─────────────────┐
                                                                             │      End        │
                                                                             └─────────────────┘
```

---

## 5. Database Design

### 5.1 Entity-Relationship (ER) Schema

The ER diagram represents the conceptual design of the PharmaCare database, identifying entities, their attributes, and relationships.

#### 5.1.1 ER Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                     PHARMACARE ER DIAGRAM                                           │
└─────────────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌───────────────────────┐                                    ┌───────────────────────┐
    │        USERS          │                                    │      CATEGORIES       │
    ├───────────────────────┤                                    ├───────────────────────┤
    │ • id (PK)             │                                    │ • id (PK)             │
    │ • email (UNIQUE)      │                                    │ • name                │
    │ • password            │                                    │ • slug (UNIQUE)       │
    │ • first_name          │                                    │ • description         │
    │ • last_name           │                                    │ • image               │
    │ • phone               │                                    └───────────┬───────────┘
    │ • address             │                                                │
    │ • role                │                                                │ 1
    │ • created_at          │                                                │
    └───────────┬───────────┘                                                │
                │                                                            │ contains
                │ 1                                                          │
                │                                                            │ M
                │ places                                    ┌───────────────────────────────┐
                │                                          │          PRODUCTS             │
                │ M                                        ├───────────────────────────────┤
    ┌───────────────────────┐                              │ • id (PK)                     │
    │        ORDERS         │                              │ • category_id (FK)            │
    ├───────────────────────┤                              │ • name                        │
    │ • id (PK)             │                              │ • slug (UNIQUE)               │
    │ • user_id (FK)        │                              │ • description                 │
    │ • total_amount        │                              │ • price                       │
    │ • status              │                              │ • stock_quantity              │
    │ • shipping_address    │                              │ • image                       │
    │ • prescription_file   │                              │ • requires_prescription       │
    │ • notes               │                              │ • dosage_info                 │
    │ • created_at          │                              │ • is_active                   │
    └───────────┬───────────┘                              │ • created_at                  │
                │                                          └───────────────┬───────────────┘
                │ 1                                                        │
                │                                                          │ 1
                │ contains                                                 │
                │                                        ┌─────────────────┴─────────────────┐
                │ M                                      │                                   │
    ┌───────────────────────┐                           M│                                   │M
    │     ORDER_ITEMS       │                  ┌─────────────────────┐          ┌─────────────────────┐
    ├───────────────────────┤                  │      CART           │          │    ORDER_ITEMS      │
    │ • id (PK)             │                  ├─────────────────────┤          │    (referenced)     │
    │ • order_id (FK)       │                  │ • id (PK)           │          └─────────────────────┘
    │ • product_id (FK)     │ ←────────────────│ • user_id (FK)      │
    │ • quantity            │                  │ • session_id        │
    │ • unit_price          │                  │ • product_id (FK)   │
    └───────────────────────┘                  │ • quantity          │
                                               │ • created_at        │
              ┌────────────────────────────────└─────────────────────┘
              │ M
              │
              │ has
              │
              │ 0..1
    ┌─────────────────────┐
    │       USERS         │
    │     (optional)      │
    └─────────────────────┘
```

#### 5.1.2 Entity Descriptions

| Entity | Description | Key Attributes |
|--------|-------------|----------------|
| **USERS** | Represents customers and administrators | id (PK), email, password, role |
| **CATEGORIES** | Product categories for organization | id (PK), name, slug |
| **PRODUCTS** | Items available for sale | id (PK), category_id (FK), name, price, stock_quantity |
| **ORDERS** | Customer purchase transactions | id (PK), user_id (FK), total_amount, status |
| **ORDER_ITEMS** | Line items within an order | id (PK), order_id (FK), product_id (FK), quantity |
| **CART** | Shopping cart items (temporary storage) | id (PK), user_id (FK)/session_id, product_id (FK) |

#### 5.1.3 Relationships

| Relationship | Entities | Cardinality | Description |
|--------------|----------|-------------|-------------|
| **places** | USERS → ORDERS | 1:M | One user can place many orders |
| **contains** | ORDERS → ORDER_ITEMS | 1:M | One order contains many order items |
| **references** | ORDER_ITEMS → PRODUCTS | M:1 | Many order items reference one product |
| **belongs_to** | PRODUCTS → CATEGORIES | M:1 | Many products belong to one category |
| **has_cart** | USERS → CART | 1:M | One user can have many cart items |
| **cart_contains** | CART → PRODUCTS | M:1 | Many cart items reference one product |

---

### 5.2 Mapping Algorithm (ER Schema → Relational Schema)

The mapping algorithm transforms the conceptual ER model into a logical relational schema following standard database design rules.

#### 5.2.1 Mapping Rules Applied

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                           ER TO RELATIONAL MAPPING ALGORITHM                            │
└─────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────┐
│ STEP 1: Map Regular Entity Types                                                        │
│                                                                                         │
│ Rule: Each strong entity becomes a relation with all simple attributes                  │
│                                                                                         │
│ ┌─────────────────┐         ┌─────────────────────────────────────────────────────┐    │
│ │ USERS (Entity)  │ ──────> │ users (id, email, password, first_name, last_name,  │    │
│ └─────────────────┘         │        phone, address, role, created_at)            │    │
│                             └─────────────────────────────────────────────────────┘    │
│                                                                                         │
│ ┌─────────────────┐         ┌─────────────────────────────────────────────────────┐    │
│ │ CATEGORIES      │ ──────> │ categories (id, name, slug, description, image)     │    │
│ │ (Entity)        │         └─────────────────────────────────────────────────────┘    │
│ └─────────────────┘                                                                     │
│                                                                                         │
│ ┌─────────────────┐         ┌─────────────────────────────────────────────────────┐    │
│ │ PRODUCTS        │ ──────> │ products (id, name, slug, description, price,       │    │
│ │ (Entity)        │         │          stock_quantity, image, requires_prescription│    │
│ └─────────────────┘         │          dosage_info, is_active, created_at)        │    │
│                             └─────────────────────────────────────────────────────┘    │
│                                                                                         │
│ ┌─────────────────┐         ┌─────────────────────────────────────────────────────┐    │
│ │ ORDERS (Entity) │ ──────> │ orders (id, total_amount, status, shipping_address, │    │
│ └─────────────────┘         │         prescription_file, notes, created_at)       │    │
│                             └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────┐
│ STEP 2: Map 1:M (One-to-Many) Relationships                                             │
│                                                                                         │
│ Rule: Add foreign key to the "Many" side referencing the "One" side's primary key      │
│                                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────────────────┐    │
│ │ Relationship: USERS (1) ─────places─────> (M) ORDERS                            │    │
│ │                                                                                 │    │
│ │ Result: Add user_id as FK in orders table                                       │    │
│ │         orders (..., user_id FK → users.id, ...)                                │    │
│ └─────────────────────────────────────────────────────────────────────────────────┘    │
│                                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────────────────┐    │
│ │ Relationship: CATEGORIES (1) ─────contains─────> (M) PRODUCTS                   │    │
│ │                                                                                 │    │
│ │ Result: Add category_id as FK in products table                                 │    │
│ │         products (..., category_id FK → categories.id, ...)                     │    │
│ └─────────────────────────────────────────────────────────────────────────────────┘    │
│                                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────────────────┐    │
│ │ Relationship: ORDERS (1) ─────contains─────> (M) ORDER_ITEMS                    │    │
│ │                                                                                 │    │
│ │ Result: Add order_id as FK in order_items table                                 │    │
│ │         order_items (..., order_id FK → orders.id, ...)                         │    │
│ └─────────────────────────────────────────────────────────────────────────────────┘    │
│                                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────────────────┐    │
│ │ Relationship: PRODUCTS (1) ─────referenced_in─────> (M) ORDER_ITEMS             │    │
│ │                                                                                 │    │
│ │ Result: Add product_id as FK in order_items table                               │    │
│ │         order_items (..., product_id FK → products.id, ...)                     │    │
│ └─────────────────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────┐
│ STEP 3: Map Associative Entity (CART - Optional Participation)                          │
│                                                                                         │
│ Rule: CART represents a ternary relationship with optional user participation           │
│                                                                                         │
│ ┌─────────────────────────────────────────────────────────────────────────────────┐    │
│ │ Special Case: CART with optional user (guest cart support)                      │    │
│ │                                                                                 │    │
│ │ USERS (0..1) ←─────has_cart─────> (M) CART (M) ←─────contains─────> (1) PRODUCTS│    │
│ │                                                                                 │    │
│ │ Result: cart (id, user_id FK (NULLABLE), session_id, product_id FK, ...)        │    │
│ │                                                                                 │    │
│ │ • user_id is NULLABLE to support guest users (identified by session_id)         │    │
│ │ • Either user_id OR session_id must be present (application-level constraint)   │    │
│ └─────────────────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────┐
│ STEP 4: Define Primary Keys                                                             │
│                                                                                         │
│ Rule: Each relation must have a primary key (surrogate key chosen: AUTO_INCREMENT id)   │
│                                                                                         │
│ ┌──────────────────┬───────────────────────────────────────────────────────────────┐   │
│ │ Relation         │ Primary Key                                                   │   │
│ ├──────────────────┼───────────────────────────────────────────────────────────────┤   │
│ │ users            │ id (INT, AUTO_INCREMENT)                                      │   │
│ │ categories       │ id (INT, AUTO_INCREMENT)                                      │   │
│ │ products         │ id (INT, AUTO_INCREMENT)                                      │   │
│ │ orders           │ id (INT, AUTO_INCREMENT)                                      │   │
│ │ order_items      │ id (INT, AUTO_INCREMENT)                                      │   │
│ │ cart             │ id (INT, AUTO_INCREMENT)                                      │   │
│ └──────────────────┴───────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────┐
│ STEP 5: Define Referential Integrity Constraints (ON DELETE Actions)                    │
│                                                                                         │
│ Rule: Define what happens when referenced record is deleted                             │
│                                                                                         │
│ ┌──────────────────────────────────────────────────────────────────────────────────┐   │
│ │ Foreign Key              │ References       │ ON DELETE Action  │ Rationale      │   │
│ ├──────────────────────────┼──────────────────┼───────────────────┼────────────────┤   │
│ │ products.category_id     │ categories.id    │ CASCADE           │ Delete products│   │
│ │                          │                  │                   │ with category  │   │
│ ├──────────────────────────┼──────────────────┼───────────────────┼────────────────┤   │
│ │ orders.user_id           │ users.id         │ CASCADE           │ Delete orders  │   │
│ │                          │                  │                   │ with user      │   │
│ ├──────────────────────────┼──────────────────┼───────────────────┼────────────────┤   │
│ │ order_items.order_id     │ orders.id        │ CASCADE           │ Delete items   │   │
│ │                          │                  │                   │ with order     │   │
│ ├──────────────────────────┼──────────────────┼───────────────────┼────────────────┤   │
│ │ order_items.product_id   │ products.id      │ CASCADE           │ Delete items   │   │
│ │                          │                  │                   │ with product   │   │
│ ├──────────────────────────┼──────────────────┼───────────────────┼────────────────┤   │
│ │ cart.user_id             │ users.id         │ CASCADE           │ Clear cart     │   │
│ │                          │                  │                   │ with user      │   │
│ ├──────────────────────────┼──────────────────┼───────────────────┼────────────────┤   │
│ │ cart.product_id          │ products.id      │ CASCADE           │ Remove from    │   │
│ │                          │                  │                   │ cart if deleted│   │
│ └──────────────────────────┴──────────────────┴───────────────────┴────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

---

### 5.3 Relational Schema

The final relational schema resulting from the mapping algorithm.

#### 5.3.1 Schema Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                              PHARMACARE RELATIONAL SCHEMA                               │
└─────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                      users                                              │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│  PK   │ id          │ INT            │ AUTO_INCREMENT                                   │
│       │ email       │ VARCHAR(255)   │ NOT NULL, UNIQUE                                 │
│       │ password    │ VARCHAR(255)   │ NOT NULL                                         │
│       │ first_name  │ VARCHAR(100)   │ NOT NULL                                         │
│       │ last_name   │ VARCHAR(100)   │ NOT NULL                                         │
│       │ phone       │ VARCHAR(20)    │ NULL                                             │
│       │ address     │ TEXT           │ NULL                                             │
│       │ role        │ ENUM           │ ('customer', 'admin') DEFAULT 'customer'         │
│       │ created_at  │ TIMESTAMP      │ DEFAULT CURRENT_TIMESTAMP                        │
└─────────────────────────────────────────────────────────────────────────────────────────┘
                                            │
                                            │ 1
                                            │
                            ┌───────────────┴───────────────┐
                            │                               │
                            │ M                             │ M (optional)
                            ▼                               ▼
┌─────────────────────────────────────────┐   ┌─────────────────────────────────────────┐
│              orders                      │   │                cart                     │
├─────────────────────────────────────────┤   ├─────────────────────────────────────────┤
│  PK   │ id               │ INT          │   │  PK   │ id          │ INT               │
│  FK   │ user_id          │ INT NOT NULL │   │  FK   │ user_id     │ INT NULL          │
│       │ total_amount     │ DECIMAL(10,2)│   │       │ session_id  │ VARCHAR(255) NULL │
│       │ status           │ ENUM         │   │  FK   │ product_id  │ INT NOT NULL      │
│       │ shipping_address │ TEXT NOT NULL│   │       │ quantity    │ INT DEFAULT 1     │
│       │ prescription_file│ VARCHAR(255) │   │       │ created_at  │ TIMESTAMP         │
│       │ notes            │ TEXT         │   └───────┴─────────────┴───────────────────┘
│       │ created_at       │ TIMESTAMP    │                         │
└───────┴──────────────────┴──────────────┘                         │
            │                                                       │
            │ 1                                                     │
            │                                                       │
            │ M                                                     │
            ▼                                                       │
┌─────────────────────────────────────────┐                         │
│           order_items                    │                         │
├─────────────────────────────────────────┤                         │
│  PK   │ id          │ INT               │                         │
│  FK   │ order_id    │ INT NOT NULL      │                         │
│  FK   │ product_id  │ INT NOT NULL      │────────────┐            │
│       │ quantity    │ INT NOT NULL      │            │            │
│       │ unit_price  │ DECIMAL(10,2)     │            │            │
└───────┴─────────────┴───────────────────┘            │            │
                                                       │            │
                                                       │ M          │ M
                                                       │            │
                                                       ▼            │
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                     products                                            │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│  PK   │ id                    │ INT            │ AUTO_INCREMENT                         │
│  FK   │ category_id           │ INT            │ NOT NULL                               │
│       │ name                  │ VARCHAR(255)   │ NOT NULL                               │
│       │ slug                  │ VARCHAR(255)   │ NOT NULL, UNIQUE                       │
│       │ description           │ TEXT           │ NULL                                   │
│       │ price                 │ DECIMAL(10,2)  │ NOT NULL                               │
│       │ stock_quantity        │ INT            │ NOT NULL, DEFAULT 0                    │
│       │ image                 │ VARCHAR(255)   │ NULL                                   │
│       │ requires_prescription │ BOOLEAN        │ DEFAULT FALSE                          │
│       │ dosage_info           │ TEXT           │ NULL                                   │
│       │ is_active             │ BOOLEAN        │ DEFAULT TRUE                           │
│       │ created_at            │ TIMESTAMP      │ DEFAULT CURRENT_TIMESTAMP              │
└─────────────────────────────────────────────────────────────────────────────────────────┘
                            │
                            │ M
                            │
                            │ 1
                            ▼
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                   categories                                            │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│  PK   │ id          │ INT            │ AUTO_INCREMENT                                   │
│       │ name        │ VARCHAR(100)   │ NOT NULL                                         │
│       │ slug        │ VARCHAR(100)   │ NOT NULL, UNIQUE                                 │
│       │ description │ TEXT           │ NULL                                             │
│       │ image       │ VARCHAR(255)   │ NULL                                             │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

#### 5.3.2 Table Definitions Summary

| Table | Primary Key | Foreign Keys | Description |
|-------|-------------|--------------|-------------|
| **users** | id | - | Stores customer and admin accounts |
| **categories** | id | - | Product categories |
| **products** | id | category_id → categories(id) | Product catalog |
| **orders** | id | user_id → users(id) | Customer orders |
| **order_items** | id | order_id → orders(id), product_id → products(id) | Order line items |
| **cart** | id | user_id → users(id), product_id → products(id) | Shopping cart |

#### 5.3.3 Indexes

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                   DATABASE INDEXES                                      │
├─────────────────────────────────────────────────────────────────────────────────────────┤
│  Index Name              │ Table       │ Column(s)    │ Purpose                         │
├──────────────────────────┼─────────────┼──────────────┼─────────────────────────────────┤
│  idx_products_category   │ products    │ category_id  │ Fast product filtering by       │
│                          │             │              │ category                        │
├──────────────────────────┼─────────────┼──────────────┼─────────────────────────────────┤
│  idx_products_slug       │ products    │ slug         │ Fast product lookup by URL slug │
├──────────────────────────┼─────────────┼──────────────┼─────────────────────────────────┤
│  idx_orders_user         │ orders      │ user_id      │ Fast retrieval of user's orders │
├──────────────────────────┼─────────────┼──────────────┼─────────────────────────────────┤
│  idx_orders_status       │ orders      │ status       │ Fast filtering by order status  │
├──────────────────────────┼─────────────┼──────────────┼─────────────────────────────────┤
│  idx_cart_user           │ cart        │ user_id      │ Fast cart retrieval for users   │
├──────────────────────────┼─────────────┼──────────────┼─────────────────────────────────┤
│  idx_cart_session        │ cart        │ session_id   │ Fast cart retrieval for guests  │
└──────────────────────────┴─────────────┴──────────────┴─────────────────────────────────┘
```

#### 5.3.4 Data Types and Constraints

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                              DATA TYPES & CONSTRAINTS                                   │
└─────────────────────────────────────────────────────────────────────────────────────────┘

ENUM Types:
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│  users.role    = ENUM('customer', 'admin')                                              │
│  orders.status = ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled')    │
└─────────────────────────────────────────────────────────────────────────────────────────┘

Unique Constraints:
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│  users.email           - Ensures no duplicate email registrations                       │
│  categories.slug       - Ensures unique URL-friendly category identifiers               │
│  products.slug         - Ensures unique URL-friendly product identifiers                │
└─────────────────────────────────────────────────────────────────────────────────────────┘

NOT NULL Constraints:
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│  users:       email, password, first_name, last_name                                    │
│  categories:  name, slug                                                                │
│  products:    category_id, name, slug, price, stock_quantity                            │
│  orders:      user_id, total_amount, shipping_address                                   │
│  order_items: order_id, product_id, quantity, unit_price                                │
│  cart:        product_id, quantity                                                      │
└─────────────────────────────────────────────────────────────────────────────────────────┘

Default Values:
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│  users.role                  = 'customer'                                               │
│  products.stock_quantity     = 0                                                        │
│  products.requires_prescription = FALSE                                                 │
│  products.is_active          = TRUE                                                     │
│  orders.status               = 'pending'                                                │
│  cart.quantity               = 1                                                        │
│  *.created_at                = CURRENT_TIMESTAMP                                        │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Document Information

| Field | Value |
|-------|-------|
| **Project Name** | PharmaCare Online Pharmacy |
| **Document Version** | 1.0 |
| **Phase** | First Phase Analysis |
| **Technology Stack** | PHP 8.x, MySQL 8.x, HTML5, CSS3, JavaScript |
| **Target Platform** | Web (Responsive) |

---

*This document serves as the foundational analysis for the PharmaCare pharmacy e-commerce platform, outlining objectives, development phases, user journeys, and activity diagrams based on the current implementation.*
