# BookMeUp - Project Documentation

## 1. Overview
**BookMeUp** is a modern web application designed for booking appointments and managing service-based businesses. It provides a seamless interface for both clients (to find and book services) and business owners (to manage their offerings and appointments).

---

## 2. Architecture & Design

### MVC Pattern
The project follows the **Model-View-Controller (MVC)** architectural pattern to ensure a clean separation of concerns:
- **Models (Repositories)**: Located in `src/repository/`, these handle all database interactions and data-related logic.
- **Views**: Located in `public/views/`, these contain the HTML templates for the user interface.
- **Controllers**: Located in `src/controllers/`, these manage the application logic, process user input, and coordinate between models and views.

### Routing
The application uses a custom **Routing** system (`Routing.php`) that maps URLs to specific controller actions. The entry point is `index.php`, which initializes the session and handles request dispatching.

### Database
- **Engine**: PostgreSQL.
- **Connection**: Managed via a singleton `Database` class in `Database.php`.
- **Schema**: Management scripts like `update_schema.php` and `backup.sql` are provided for environment setup.

---

## 3. Feature Set

### User Authentication & Management
- **Security**: Password hashing using BCRYPT.
- **Registration**: Support for both standard users and business owners (`SecurityController.php`).
- **Profile**: Users can update their personal information, bio, and avatar (`NavigationController@editProfile`).

### Business Features
- **Business Dashboard**: Specialized view for owners to manage their business status.
- **Service Management**: Owners can add and manage services offered by their business.
- **Business Profile**: Public-facing page showcasing services, ratings, and contact info.

### Appointment System
- **Booking**: Clients can choose services and book available slots (`AppointmentController`).
- **Management**: Users can view and cancel their upcoming appointments.

### Reviews & Ratings
- Clients can leave reviews and ratings for businesses they have visited, which are displayed on the business profile.

---

## 4. Technical Stack

- **Backend**: PHP 8.x (Native).
- **Database**: PostgreSQL.
- **Frontend**: Vanilla HTML5, CSS3, and JavaScript.
- **Cloud Storage**: **Supabase** is used for secure image/avatar storage.
- **Containerization**: **Docker** and **Docker Compose** for consistent environment management.

---

## 5. Project Structure

```text
├── docker/                 # Docker configuration (Nginx, PHP, Postgres)
├── public/
│   ├── scripts/            # Client-side JavaScript logic
│   ├── styles/             # CSS stylesheets for various pages
│   └── views/              # HTML templates (rendered via PHP)
├── src/
│   ├── controllers/        # Application logic (AppController, SecurityController, etc.)
│   └── repository/         # Database access layer (UserRepository, BusinessRepository, etc.)
├── index.php               # Main entry point
├── Routing.php             # Custom router
├── Database.php            # Singleton for DB connection
├── config.php              # Environment-specific configuration
└── docker-compose.yaml     # Orchestration for the dev environment
```

---

## 6. Getting Started

### Prerequisites
- Docker & Docker Compose installed.

### Setup Instructions
1. **Clone the repository**.
2. **Configure Environment**: Create a `config.php` file based on your environment settings (Database credentials, Supabase keys).
3. **Launch Containers**:
   ```bash
   docker-compose up -d
   ```
4. **Initialize Database**:
   The `docker-compose` setup automatically initializes the DB using the scripts in `docker/db`. You can manually trigger updates via the `/update-schema` route if necessary.
5. **Access Application**: Open `http://localhost:8080` in your browser.

---

## 7. API & Integration
- **Supabase**: Integration for file uploads using CURL in `SecurityController::uploadToSupabase`.
- **JSON Endpoints**: Various routes like `/updateSettings` or `/getBookedSlots` return JSON for dynamic frontend updates.
