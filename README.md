# E-commerce Shopping Cart

Laravel e-commerce application with Vue.js frontend, shopping cart, and automated notifications.

---

## Docker

### Requirements
- Docker
- Docker Compose

### Setup Steps

#### 1. Clone the project
```bash
git clone <repository-url>
cd cart
```

#### 2. Start Docker containers
```bash
docker-compose up -d --build
```

**That's it!** This command will automatically:
- Install all dependencies
- Create `.env` file
- Generate application key
- Run database migrations
- Seed the database with sample data
- Start queue worker (for emails)
- Start scheduler (for daily reports)

---

## Access the Application

**Application URL:** http://localhost:8080

---

## 🛠️ Useful Docker Commands

### View logs
```bash
docker-compose logs -f
```

### Stop containers
```bash
docker-compose down
```

### Restart containers
```bash
docker-compose restart
```

### Run artisan commands
```bash
docker exec -it cart_app php artisan <command>
```

### Examples:
```bash
# Send daily sales report
docker exec -it cart_app php artisan sales:report-daily

# Create new user
docker exec -it cart_app php artisan tinker
```

---

##  Email Configuration

By default, emails are logged to `storage/logs/laravel.log`.

To send real emails, update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

CART_ADMIN_EMAIL=admin@example.com
```

Then restart containers:
```bash
docker-compose restart
```

---
### Database reset
```bash
docker exec -it cart_app php artisan migrate:fresh --seed
```

---

##  Tech Stack

- **Backend:** Laravel 12
- **Frontend:** Vue.js + Tailwind CSS
- **Database:** MySQL
- **Server:** Nginx + PHP-FPM
- **Queue:** Database driver
- **Email:** Log/SMTP

---
