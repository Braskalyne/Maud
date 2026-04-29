# 🚀 Guide de Déploiement - 4fam.fr

Guide étape par étape pour déployer le site sur votre VPS Ubuntu 24.04.

---

## 📋 Prérequis vérifiés ✅
- Ubuntu 24.04
- PHP 8.2+
- PostgreSQL 16.13
- Nginx 1.24.0
- Domaine : 4fam.fr / www.4fam.fr
- Chemin d'installation : `/var/www/4fam`

---

## 🔧 ÉTAPE 1 : Cloner le projet

Connectez-vous en SSH à votre VPS et exécutez :

```bash
# Aller dans le dossier www
cd /var/www

# Cloner le projet depuis GitHub
git clone https://github.com/Braskalyne/Maud.git 4fam

# Entrer dans le dossier
cd 4fam
```

---

## 📦 ÉTAPE 2 : Installer les dépendances

```bash
# Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# Vérifier que tout est OK
php bin/console about
```

---

## 🗄️ ÉTAPE 3 : Créer la base de données PostgreSQL

```bash
# Se connecter à PostgreSQL
sudo -u postgres psql

# Dans psql, créer l'utilisateur et la base de données :
CREATE USER fourfa WITH PASSWORD 'CHOISIR_UN_MOT_DE_PASSE_FORT';
CREATE DATABASE fourfam_db OWNER fourfam;
GRANT ALL PRIVILEGES ON DATABASE fourfam_db TO fourfam;

# Quitter psql
\q
```

---

## ⚙️ ÉTAPE 4 : Configurer l'environnement

```bash
# Créer le fichier .env.local
nano .env.local
```

Copiez-collez ce contenu (adaptez les valeurs) :

```env
APP_ENV=prod
APP_SECRET=GÉNÉRER_UNE_CLÉ_ALÉATOIRE_ICI

# Base de données
DATABASE_URL="postgresql://fourfam:VOTRE_MOT_DE_PASSE@127.0.0.1:5432/fourfam_db?serverVersion=16&charset=utf8"

# Email (utilisez vos vraies valeurs Gmail)
MAILER_DSN=gmail+smtp://VOTRE_EMAIL@gmail.com:VOTRE_MOT_DE_PASSE_APP@default
```

**💡 Générer APP_SECRET :**
```bash
php bin/console secret:generate
```

Sauvegardez avec `Ctrl+O` puis `Enter`, quittez avec `Ctrl+X`.

---

## 🏗️ ÉTAPE 5 : Initialiser la base de données

```bash
# Créer les tables
php bin/console doctrine:migrations:migrate --no-interaction

# Vérifier que tout fonctionne
php bin/console doctrine:schema:validate
```

---

## 📁 ÉTAPE 6 : Permissions et cache

```bash
# Configurer les permissions
chown -R www-data:www-data /var/www/4fam
chmod -R 755 /var/www/4fam

# Permissions spéciales pour var/
chmod -R 775 /var/www/4fam/var

# Vider et préchauffer le cache en production
APP_ENV=prod php bin/console cache:clear
APP_ENV=prod php bin/console cache:warmup
```

---

## 🌐 ÉTAPE 7 : Configuration Nginx

```bash
# Créer le fichier de configuration
sudo nano /etc/nginx/sites-available/4fam
```

Copiez-collez cette configuration :

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 4fam.fr www.4fam.fr;

    root /var/www/4fam/public;
    index index.php;

    # Logs
    access_log /var/log/nginx/4fam_access.log;
    error_log /var/log/nginx/4fam_error.log;

    # Désactiver les logs d'accès pour les fichiers statiques
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|webp)$ {
        access_log off;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Point d'entrée principal
    location / {
        try_files $uri /index.php$is_args$args;
    }

    # PHP-FPM
    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        internal;
    }

    # Interdire l'accès aux autres fichiers PHP
    location ~ \.php$ {
        return 404;
    }

    # Sécurité : bloquer les fichiers sensibles
    location ~ /\. {
        deny all;
    }
}
```

**⚠️ Important :** Vérifiez la version de PHP-FPM. Si vous avez PHP 8.2 :
```bash
php -v  # Voir la version exacte
```
Ajustez la ligne `fastcgi_pass unix:/var/run/php/php8.X-fpm.sock;` avec votre version.

---

## 🔗 ÉTAPE 8 : Activer le site

```bash
# Créer le lien symbolique
sudo ln -s /etc/nginx/sites-available/4fam /etc/nginx/sites-enabled/

# Tester la configuration Nginx
sudo nginx -t

# Si tout est OK, recharger Nginx
sudo systemctl reload nginx
```

---

## 🌍 ÉTAPE 9 : Configuration DNS chez OVH

Connectez-vous à votre espace client OVH, puis :

1. **Allez dans "Noms de domaine"** → Sélectionnez `4fam.fr`
2. **Cliquez sur "Zone DNS"**
3. **Ajoutez/Modifiez ces enregistrements :**

| Type | Sous-domaine | Cible | TTL |
|------|--------------|-------|-----|
| A    | @            | VOTRE_IP_VPS | 3600 |
| A    | www          | VOTRE_IP_VPS | 3600 |

4. **Enregistrez les modifications**

⏱️ **Propagation DNS** : Peut prendre 1 à 24h (généralement 1-2h).

**Vérifier la propagation :**
```bash
# Sur votre PC Windows (PowerShell)
nslookup 4fam.fr
nslookup www.4fam.fr
```

---

## 🔒 ÉTAPE 10 : HTTPS avec Let's Encrypt (Certbot)

Une fois le DNS propagé :

```bash
# Installer Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtenir le certificat SSL (automatique)
sudo certbot --nginx -d 4fam.fr -d www.4fam.fr

# Suivre les instructions :
# - Entrer votre email
# - Accepter les conditions
# - Choisir de rediriger HTTP → HTTPS (option 2)
```

Certbot va automatiquement :
- Obtenir le certificat SSL gratuit
- Modifier la config Nginx
- Configurer le renouvellement automatique

**Vérifier le renouvellement automatique :**
```bash
sudo certbot renew --dry-run
```

---

## 🎨 ÉTAPE 11 : Uploader les images (si nécessaire)

Si vous avez des images dans `public/uploads/` en local :

```bash
# Sur votre PC Windows (PowerShell)
scp -r C:\Users\augus\maud\public\uploads\* root@VOTRE_IP:/var/www/4fam/public/uploads/

# Sur le VPS, corriger les permissions
sudo chown -R www-data:www-data /var/www/4fam/public/uploads
sudo chmod -R 755 /var/www/4fam/public/uploads
```

---

## ✅ ÉTAPE 12 : Vérification finale

**1. Tester l'accès au site :**
```bash
curl -I https://4fam.fr
```

**2. Visitez dans votre navigateur :**
- https://4fam.fr
- https://www.4fam.fr
- https://4fam.fr/admin (page de login)

**3. Vérifier les logs en cas de problème :**
```bash
# Logs Nginx
sudo tail -f /var/log/nginx/4fam_error.log

# Logs Symfony
tail -f /var/www/4fam/var/log/prod.log
```

---

## 🔧 Maintenance et commandes utiles

### Mettre à jour le site après un git push

```bash
cd /var/www/4fam
git pull origin master
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --env=prod
sudo chown -R www-data:www-data /var/www/4fam
```

### Voir les logs en temps réel

```bash
# Logs Symfony
tail -f /var/www/4fam/var/log/prod.log

# Logs Nginx
sudo tail -f /var/log/nginx/4fam_error.log
```

### Tester la configuration Nginx

```bash
sudo nginx -t
sudo systemctl reload nginx
```

### Vider le cache Symfony

```bash
cd /var/www/4fam
APP_ENV=prod php bin/console cache:clear
sudo chown -R www-data:www-data var/
```

---

## 🆘 Problèmes courants

### Erreur 500 - Internal Server Error

```bash
# Vérifier les logs Symfony
tail -50 /var/www/4fam/var/log/prod.log

# Souvent un problème de permissions :
sudo chown -R www-data:www-data /var/www/4fam/var
sudo chmod -R 775 /var/www/4fam/var
```

### Erreur 502 - Bad Gateway

```bash
# Vérifier que PHP-FPM tourne
sudo systemctl status php8.3-fpm
sudo systemctl restart php8.3-fpm
```

### Page blanche ou erreur cache

```bash
cd /var/www/4fam
rm -rf var/cache/*
APP_ENV=prod php bin/console cache:warmup
sudo chown -R www-data:www-data var/
```

### Images ne s'affichent pas

```bash
sudo chown -R www-data:www-data /var/www/4fam/public/uploads
sudo chmod -R 755 /var/www/4fam/public/uploads
```

---

## 📧 Configuration email en production

Si les emails ne partent pas, vérifiez dans `.env.local` :

```env
MAILER_DSN=gmail+smtp://votre.email@gmail.com:votre_mot_de_passe_app@default
```

**Important :** Utilisez un "mot de passe d'application" Google, pas votre mot de passe principal.

Générer un mot de passe d'application :
1. https://myaccount.google.com/security
2. Validation en 2 étapes (activer si besoin)
3. Mots de passe d'application → Générer

---

## 🎉 C'est terminé !

Votre site 4fam.fr est maintenant en ligne avec :
- ✅ HTTPS (SSL)
- ✅ Base de données PostgreSQL
- ✅ Emails fonctionnels
- ✅ Admin sécurisé
- ✅ Renouvellement SSL automatique

**Pensez à :**
- Créer votre compte admin : `php bin/console security:hash-password`
- Uploader vos premières galeries via l'admin
- Tester le formulaire de contact

---

**Besoin d'aide ?** Consultez la section "Problèmes courants" ci-dessus ! 🚀
