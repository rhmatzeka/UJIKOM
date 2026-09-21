# FAKTUR.ID: Sales Invoice Manager

A web app for managing **sales invoices**: companies that issue invoices, the customers who receive them, the product catalog, sales transactions, and printable sales reports.

It was built for a **programmer skills certification exam** (UKK, Programmer scheme). The interface is in Indonesian.

## Features

1. **Dashboard**
   - Totals for companies, customers, products, and transactions
   - Total sales revenue, updated in real time
   - The 3 latest transactions

2. **Companies** (who issues the invoice)
   - Add, edit, and delete companies: name, address, phone, fax

3. **Customers** (who receives the invoice)
   - Add, edit, and delete customers and their company
   - Print a formal **customer card**

4. **Products**
   - Add, edit, and delete products: name, price, category, stock

5. **Sales transactions** (the invoice itself)
   - Add and remove product rows on the fly with jQuery (no page reload)
   - Subtotal, **VAT (%)**, **down payment**, and **grand total** are calculated automatically
   - Saved as a single database transaction, and **stock is reduced automatically**
   - Print an A4 invoice (navigation is hidden when printing)

6. **Sales report**
   - Filter by date range, issuing company, customer, and payment method
   - Totals (subtotal, down payment, grand total) in the report footer
   - Printable report with a signature space for the manager

## Tech stack

PHP 8 (no framework), MySQL/MariaDB, Tailwind CSS and jQuery (both from a CDN)

## Getting started

You need PHP 8+ and MySQL or MariaDB (XAMPP works well).

1. Copy the project into your web server folder, for example `C:/xampp/htdocs/ujikom/`.
2. Start **Apache** and **MySQL**.
3. Open http://localhost/ujikom/index.php.

**The database sets itself up.** On the first visit, the app creates a database called `ujikom_faktur`, creates all the tables, and loads sample data from `database.sql`. No manual import is needed.

Database settings are in `config.php`.

## Project structure

| File | What it is |
| --- | --- |
| `index.php` | Dashboard |
| `perusahaan.php` | Companies |
| `customer.php` | Customers |
| `produk.php` | Products |
| `penjualan.php` | Sales transactions and invoices |
| `laporan.php` | Sales report |
| `config.php` | Database connection and auto-installer |
| `database.sql` | Tables and sample data |
| `dokumentasi.md` | Extra documentation |
