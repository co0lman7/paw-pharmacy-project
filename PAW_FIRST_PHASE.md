# PharmaCare - Project Analysis & Planning Document
## First Phase Documentation

---

## Table of Contents
1. [Website Objectives](#1-website-objectives)
2. [Project Development Phases](#2-project-development-phases)
3. [User Journeys](#3-user-journeys)
4. [Activity Diagrams](#4-activity-diagrams)

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
