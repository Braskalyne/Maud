# 4fam - Artistes Muralistes

Portfolio website for a duo of professional mural artists, showcasing artistic creations, galleries, and project inquiries.

## 🎨 Features

- **Public Pages**
  - Home page with hero section
  - Artist presentation
  - Gallery showcasing (Trompe-l'œil, Creative projects, Youth universe, Events)
  - Media gallery (videos, press articles)
  - Contact form with email notifications
  - Legal pages (Privacy policy, Legal mentions)

- **Admin Dashboard**
  - Gallery management (CRUD operations)
  - Media management
  - Secure authentication
  - Image upload handling

- **Responsive Design**
  - Mobile-first approach
  - Burger menu for mobile devices
  - Optimized for all screen sizes

## 🛠️ Tech Stack

- **Backend**: Symfony 7.2
- **Database**: PostgreSQL 16
- **Frontend**: Stimulus, Turbo, Asset Mapper
- **Email**: Gmail SMTP
- **Containerization**: Docker (FrankenPHP)

## 📋 Prerequisites

- PHP 8.2+
- Composer
- PostgreSQL 16+
- Docker & Docker Compose (optional)

## ⚙️ Installation

### 1. Clone the repository

```bash
git clone https://github.com/Braskalyne/Maud.git
cd maud
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

Copy the `.env` file to `.env.local` and configure your environment variables:

```bash
cp .env .env.local
```

Edit `.env.local`:

```env
# Database configuration
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/maud?serverVersion=16&charset=utf8"

# Mailer configuration (Gmail example)
MAILER_DSN=gmail://your-email@gmail.com:your-app-password@default

# App secret (generate with: php bin/console secrets:generate-keys)
APP_SECRET=your-secret-key
```

### 4. Create the database

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Load fixtures (optional)

```bash
php bin/console doctrine:fixtures:load
```

### 6. Start the server

**Option A: Symfony CLI**
```bash
symfony server:start
```

**Option B: Docker**
```bash
docker compose up -d
```

Visit: http://localhost:8000

## 🔐 Admin Access

Default admin credentials (if using fixtures):
- URL: `/admin`
- Username: `admin`
- Password: (configure in your security settings)

## 📁 Project Structure

```
├── assets/              # Frontend assets (JS, CSS)
├── config/              # Symfony configuration
├── migrations/          # Database migrations
├── public/              # Public web files
│   ├── images/         # Static images
│   └── uploads/        # Uploaded files
├── src/
│   ├── Command/        # Console commands
│   ├── Controller/     # Controllers
│   ├── Entity/         # Doctrine entities
│   ├── Form/           # Form types
│   └── Repository/     # Doctrine repositories
├── templates/           # Twig templates
│   ├── admin/          # Admin templates
│   ├── emails/         # Email templates
│   └── home/           # Public pages
└── tests/              # Unit & functional tests
```

## 🚀 Deployment

### Production Setup

1. Set environment to production:
```bash
APP_ENV=prod
```

2. Clear cache:
```bash
php bin/console cache:clear --env=prod
```

3. Dump environment:
```bash
composer dump-env prod
```

4. Configure your web server (Nginx/Apache) to point to `/public`

## 📧 Email Configuration

The contact form requires email configuration. For Gmail:

1. Enable 2-factor authentication on your Google account
2. Generate an App Password: https://myaccount.google.com/apppasswords
3. Update `MAILER_DSN` in `.env.local`:
```env
MAILER_DSN=gmail://your-email@gmail.com:app-password@default
```

## 🧪 Testing

Run tests:
```bash
php bin/phpunit
```

## 📝 License

All rights reserved © 2026 4fam - Artistes Muralistes

## 👤 Authors

**4fam**
- Duo of Professional Mural Artists
- Specializing in Trompe-l'œil and decorative painting

## 🤝 Contributing

This is a private portfolio project. For inquiries, please use the contact form on the website.

## 📞 Support

For technical issues or questions, please contact the development team.
