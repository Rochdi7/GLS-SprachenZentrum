# GLS Sprachen Zentrum Website

A multilingual, scalable website built with **Laravel** and **Tailwind CSS** for the GLS Sprachen Zentrum. This project supports dynamic content management via a custom dashboard and is designed to be extendable with multilingual features and additional modules (e.g., blog, group/course listings).

---

## 🚀 Tech Stack

- **Laravel 11** — backend framework
- **Tailwind CSS** — utility-first styling
- **MySQL** — database engine
- **Vite** — asset bundler
- **Custom CMS Dashboard** — admin panel (no Filament)
- **Multilingual Support** — primary language English, secondary German
- **Component-based Sections** — dynamic page content using section types

---

## 🧱 Project Structure

```bash
.
├── app/
│   ├── Models/        # Page, Section, Post, Group, etc.
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/SetLocaleFromUrl.php
├── resources/
│   ├── views/
│   │   ├── pages/     # Static page templates
│   │   ├── sections/  # Reusable section components
│   │   ├── dashboard/ # Custom dashboard views
├── routes/
│   └── web.php
├── public/
├── database/
│   ├── migrations/
│   ├── seeders/
├── lang/
│   └── en/, de/       # Laravel native translation files (optional)

#Comming-Sooon!