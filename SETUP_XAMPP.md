# SignED — XAMPP Local Setup Guide (Laptop)

Mao kini ang pinakasayon ug kumpleto nga giya sa pag-setup sa SignED gamit ang **XAMPP** sa imong laptop.

---

## 1. Pag-clone sa Repository gikan sa GitHub

Ablihi ang Terminal o PowerShell sulod sa imong XAMPP `htdocs` folder (`C:\xampp\htdocs`):

```bash
cd C:\xampp\htdocs
git clone -b deploy https://github.com/artastele/Signedd.git Signedd
cd Signedd
```

*(Kung naa na nimo ang folder o ge-extract nimo, siguroha nga ang ngalan sa folder kay `Signedd` sulod sa `C:\xampp\htdocs\Signedd`)*.

---

## 2. Paghimo sa `.env` File

Sulod sa `C:\xampp\htdocs\Signedd`:
1. Kopyaha ang `.env.example` ug i-rename ngadto sa `.env`.
2. Sa terminal/PowerShell, pwede nimo i-type:
   ```cmd
   copy .env.example .env
   ```
   *(Naka-configure na daan ang `.env.example` para sa default XAMPP settings: `localhost`, root user nga walay password, database `sped_lms`, ug live Gmail SMTP).*

---

## 3. Pag-start sa XAMPP Services

1. Ablihi ang **XAMPP Control Panel** as Administrator.
2. I-click ang **Start** button para sa:
   - **Apache**
   - **MySQL**
3. Siguroha nga nag-green ang duha ka modules.

---

## 4. Pag-import sa Database sa phpMyAdmin

1. Ablihi ang imong browser (Chrome/Edge/Brave) ug adtoa ang:
   **[http://localhost/phpmyadmin](http://localhost/phpmyadmin)**
2. Sa left sidebar, i-click ang **New**.
3. Database name: i-type ang **`sped_lms`** (Collation: `utf8mb4_unicode_ci` o default).
4. I-click ang **Create**.
5. Pagkahuman ma-create, i-click ang database nga **`sped_lms`** sa left menu.
6. I-click ang **Import** tab sa ibabaw nga menu bar.
7. I-click ang **Choose File** / **Browse** button, unya pilia ang file:
   `C:\xampp\htdocs\Signedd\database.sql`
   *(o naa sab sa `config/database_dump.sql`)*
8. I-scroll paubos ug i-click ang **Import** (o **Go**).
9. Hulata mahuman hangtod mogawas ang green checkmark "Import has been successfully finished".

---

## 5. Pag-access sa SignED System

Ablihi ang browser ug adtoa:
👉 **[http://localhost/Signedd/public](http://localhost/Signedd/public)**
*(o [http://localhost/Signedd](http://localhost/Signedd))*

### Mga Account nga Magamit sa Pagsulay (Test Accounts):

| Role | Email / Username | Password |
|---|---|---|
| **System Admin** | `admin@spedlms.local` | `password` o Google Login |
| **Principal** | `allysacanonizado43@gmail.com` | `password` o Google Login |
| **SPED Teacher** | `asteleackerman@gmail.com` | `password` o Google Login |
| **Master Teacher** | `masterteacher@spedlms.local` | `password` |
| **Parent Demo** | `maria.santos@gmail.com` | `password` |
| **Learner Demo** | `onetest_kid` | `password` |

---

## 6. Troubleshooting Tips sa XAMPP

- **Error: 404 Not Found sa mga Subpages**:
  - Siguroha nga active ang Apache `mod_rewrite` module. Sa XAMPP, default kining naka-on.
  - Siguroha nga ang `.htaccess` anaa sa root ug sa `public/` directory.
- **Port 80 Conflict sa Apache**:
  - Kung dili mag-start ang Apache tungod kay gigamit ang Port 80 (pananglitan sa Skype, IIS, o VMware):
    - Sa XAMPP Control Panel, i-click ang **Config** sa Apache -> `httpd.conf`.
    - Usba ang `Listen 80` ngadto sa `Listen 8080`.
    - Sa `.env`, usba ang `APP_URL=http://localhost:8080/Signedd/public`.
    - I-access ang system pinaagi sa `http://localhost:8080/Signedd/public`.
- **Upload Permissions**:
  - Siguroha nga ang folder nga `public/uploads/` kay accessible ug writable.
