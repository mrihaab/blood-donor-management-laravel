# LifeBlood Platform — Production Deployment & Disaster Recovery Guide

## 1. Prerequisites & Server Environment
- **Operating System**: Ubuntu 22.04 LTS or RHEL 9
- **Web Server**: Nginx 1.24+ / Apache 2.4+ (Reverse proxy to PHP-FPM 8.2+)
- **Database Engine**: PostgreSQL 14+ or MySQL 8.0+
- **PHP Version**: PHP 8.2+ with required extensions:
  `php8.2-fpm php8.2-cli php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl`

---

## 2. Server Provisioning Steps

```bash
# 1. Clone Application Codebase
cd /var/www
git clone https://github.com/your-org/blood-donor-management-laravel.git lifeblood
cd lifeblood

# 2. Install Composer Dependencies (Production mode)
composer install --no-dev --optimize-autoloader

# 3. Environment Configuration
cp .env.example .env
php artisan key:generate

# Edit .env file with actual production secrets
# Set APP_ENV=production, APP_DEBUG=false, DB_CONNECTION=pgsql, etc.

# 4. Storage & Directory Permissions
chown -R www-data:www-data /var/www/lifeblood/storage /var/www/lifeblood/bootstrap/cache
chmod -R 775 /var/www/lifeblood/storage /var/www/lifeblood/bootstrap/cache

# 5. Database Migrations
php artisan migrate --force

# 6. Production Cache Optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. Background Services Setup

### Cron Scheduler (`/etc/cron.d/lifeblood-scheduler`)
```cron
* * * * * www-data cd /var/www/lifeblood && php artisan schedule:run >> /dev/null 2>&1
```

### Supervisor Queue Worker (`/etc/supervisor/conf.d/lifeblood-worker.conf`)
```ini
[program:lifeblood-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lifeblood/artisan queue:work --tries=3 --backoff=10,30,60
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/lifeblood/storage/logs/worker.log
```

---

## 4. Database Backup & Disaster Recovery

### Daily Automated Backup Cron Script (`/usr/local/bin/lifeblood-db-backup.sh`)
```bash
#!/bin/bash
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/var/backups/lifeblood"
mkdir -p $BACKUP_DIR

# PostgreSQL backup
pg_dump -U lifeblood_user -h 127.0.0.1 lifeblood_prod | gzip > "$BACKUP_DIR/lifeblood_backup_$TIMESTAMP.sql.gz"

# Retain last 30 days of backups
find $BACKUP_DIR -type f -name "*.sql.gz" -mtime +30 -delete
```

### Disaster Recovery Restoration
```bash
gunzip -c /var/backups/lifeblood/lifeblood_backup_YYYYMMDD_HHMMSS.sql.gz | psql -U lifeblood_user -d lifeblood_prod
```
