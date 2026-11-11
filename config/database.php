<?php
// config/database.php

// ការពារ Session Warnings
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    @ini_set('session.cookie_httponly', 1);
    @ini_set('session.use_only_cookies', 1);
    @session_start();
}

define('SHOP_NAME', 'ម្ហូបអាហារភូមិយើង');
define('SHOP_NAME_KH', 'ហាងម្ហូប និងភេសជ្ជៈស្រុកយើង');

define('PHONE_1', '0979933004');
define('PHONE_1_DISPLAY', '097 993 3004');
define('PHONE_2', '087211681');
define('PHONE_2_DISPLAY', '087 211 681');

define('TELEGRAM_USERNAME', 'solphearom'); // Telegram Username ទទួល Order (https://t.me/solphearom)

// Neon Cloud PostgreSQL Credentials
define('DB_HOST', 'ep-patient-dream-azay4gg2-pooler.c-3.ap-southeast-1.aws.neon.tech');
define('DB_PORT', '5432');
define('DB_NAME', 'neondb');
define('DB_USER', 'neondb_owner');
define('DB_PASS', 'npg_pbCl6rhVwon7');
define('DB_SSLMODE', 'require');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        // ១. សាកល្បងភ្ជាប់ទៅកាន់ Neon Cloud PostgreSQL (បើមាន Driver)
        if (extension_loaded('pdo_pgsql')) {
            try {
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=" . DB_SSLMODE;
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);

                // ពិនិត្យមើលថាតើ Table មានឬនៅ បើមិនទាន់មាន នឹងបង្កើតស្វ័យប្រវត្តិ
                $check = $pdo->query("SELECT to_regclass('public.categories')")->fetchColumn();
                if (!$check) {
                    $sqlFile = __DIR__ . '/../database/SimpleFoodShop.sql';
                    if (file_exists($sqlFile)) {
                        $sql = file_get_contents($sqlFile);
                        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
                        $pdo->exec($sql);
                    }
                }

                return $pdo;
            } catch (Exception $e) {
                // ប្រសិនបើភ្ជាប់ Neon មិនបាន នឹងចូលទៅកាន់ SQLite ស្វ័យប្រវត្តិ
            }
        }

        // ២. Auto Fallback ទៅកាន់ SQLite (ធានាដំណើរការ ១០០% គ្រប់កុំព្យូទ័រ មិនបាច់ដំឡើងអ្វីទាំងអស់)
        $dbDir = __DIR__ . '/../database';
        if (!is_dir($dbDir)) {
            @mkdir($dbDir, 0755, true);
        }
        $dbFile = $dbDir . '/food_shop.sqlite';
        $isNew = !file_exists($dbFile) || filesize($dbFile) === 0;

        $pdo = new PDO("sqlite:" . $dbFile, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        if ($isNew) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS admins (
                    admin_id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    password_hash TEXT NOT NULL,
                    full_name TEXT NOT NULL,
                    phone TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS categories (
                    category_id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name_kh TEXT NOT NULL,
                    name_en TEXT NOT NULL,
                    slug TEXT UNIQUE NOT NULL,
                    display_order INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS products (
                    product_id INTEGER PRIMARY KEY AUTOINCREMENT,
                    category_id INTEGER NOT NULL,
                    name_kh TEXT NOT NULL,
                    name_en TEXT NOT NULL,
                    description_kh TEXT,
                    description_en TEXT,
                    price REAL NOT NULL,
                    image TEXT DEFAULT 'default-food.png',
                    is_available INTEGER DEFAULT 1,
                    is_featured INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                INSERT INTO categories (name_kh, name_en, slug, display_order) VALUES
                ('ម្ហូបអាហារ', 'Food', 'food', 1),
                ('ភេសជ្ជៈ', 'Drinks', 'drinks', 2),
                ('អាហារសម្រន់', 'Snacks', 'snacks', 3),
                ('គ្រឿងស្រវឹង', 'Alcohol', 'alcohol', 4);

                INSERT INTO admins (username, password_hash, full_name, phone) VALUES
                ('admin', '$2y$12$qQVmWksRyZ2L9jGgi1/atu8lgzaQvL.qrCUDjkJyRHSOySVwUIRJG', 'អ្នកគ្រប់គ្រងហាង', '0979933004');

                INSERT INTO products (category_id, name_kh, name_en, description_kh, description_en, price, image, is_available, is_featured) VALUES
                (1, 'បាយឆាសាច់ជ្រូកពងទា', 'Pork Fried Rice with Egg', 'បាយឆាឈ្ងុយឆ្ងាញ់ ជាមួយសាច់ជ្រូកស្រស់ និងពងទាចៀនក្រៀម', 'Savory fried rice with tender pork and a crispy fried egg', 2.50, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600&auto=format&fit=crop&q=80', 1, 1),
                (1, 'គុយទាវសាច់គោពិសេស', 'Special Beef Noodle Soup', 'គុយទាវទឹកស៊ុបឆ្អឹងគោផ្អែមឈ្ងុយ សាច់គោផុយទន់', 'Flavorful beef broth with soft rice noodles and tender beef slices', 3.00, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&auto=format&fit=crop&q=80', 1, 1),
                (1, 'ឡុកឡាក់សាច់គោបាយស', 'Beef Lok Lak with Rice', 'សាច់គោផុយឆាទឹកជ្រលក់ពិសេស ញ៉ាំជាមួយទឹកម្រេចក្រូចឆ្មា', 'Tender stir-fried beef cubes served with fresh salad, rice, and pepper lime dip', 3.50, 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&auto=format&fit=crop&q=80', 1, 0),
                (2, 'កាហ្វេទឹកដោះគោទឹកកក', 'Iced Milk Coffee', 'កាហ្វេគ្រាប់ឈ្ងុយឆ្ងាញ់ ទឹកដោះគោផ្អែមស្រទន់', 'Rich brewed coffee mixed with sweetened condensed milk over ice', 1.25, 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=600&auto=format&fit=crop&q=80', 1, 1),
                (2, 'តែបៃតងទឹកដោះគោ', 'Iced Green Tea Latte', 'តែបៃតងដាំស្រស់ រសជាតិឈ្ងុយផ្អែមត្រជាក់ចិត្ត', 'Freshly brewed green tea with silky milk and ice', 1.50, 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=600&auto=format&fit=crop&q=80', 1, 0),
                (2, 'ទឹកក្រូចច្របាច់ស្រស់', 'Fresh Orange Juice', 'ទឹកក្រូចស្រស់ធម្មជាតិ ១០០% មានវីតាមីន C ខ្ពស់', '100% freshly squeezed sweet natural orange juice', 1.50, 'https://images.unsplash.com/photo-1613478223719-2ab802602423?w=600&auto=format&fit=crop&q=80', 1, 0),
                (3, 'ប្រហិតចៀនចម្រុះ', 'Mixed Fried Meatballs', 'ប្រហិតសាច់គោ ត្រី បង្គា ចៀនស្រួយជាមួយទឹកជ្រលក់ពិសេស', 'Crispy assorted fried meatballs served with sweet chili dip and veggies', 2.00, 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=600&auto=format&fit=crop&q=80', 1, 0),
                (3, 'ស្លាបមាន់បំពងទឹកឃ្មុំ', 'Crispy Honey Chicken Wings', 'ស្លាបមាន់បំពងស្រួយស្រោបទឹកឃ្មុំឈ្ងុយផ្អែម', 'Golden fried chicken wings coated in sweet savory honey glaze', 2.75, 'https://images.unsplash.com/photo-1567620832903-9fc6debc209f?w=600&auto=format&fit=crop&q=80', 0, 0),
                (4, 'ស្រាបៀរអង្គរ (កំប៉ុង)', 'Angkor Beer (Can)', 'ស្រាបៀរជាតិពិតៗ ត្រជាក់ស្រួលបំពង់ក (៣៣០មល)', 'Cambodia iconic lager beer, served ice cold (330ml)', 1.00, 'https://images.unsplash.com/photo-1608270104037-1e5927ad6e61?w=600&auto=format&fit=crop&q=80', 1, 0),
                (4, 'ស្រាបៀរហនុមាន (កំប៉ុង)', 'Hanuman Beer (Can)', 'ស្រាបៀរប្រណិត រសជាតិស្រទន់ (៣៣០មល)', 'Premium smooth craft lager beer (330ml)', 1.25, 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=600&auto=format&fit=crop&q=80', 1, 0);
            ");
        }
    }
    return $pdo;
}