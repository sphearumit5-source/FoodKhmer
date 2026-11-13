
<?php
// init_db.php
// Neon PostgreSQL Database Installer

require_once __DIR__ . '/config/database.php';

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate');

function output($title, $message, $success = true) {
    $color = $success ? '#166534' : '#991b1b';
    $background = $success ? '#dcfce7' : '#fee2e2';

    echo '<div style="
        padding:20px;
        background:' . $background . ';
        color:' . $color . ';
        border-radius:10px;
        margin-top:20px;
    ">';

    echo '<h3>' . ($success ? '✅ ' : '❌ ') .
         htmlspecialchars($title, ENT_QUOTES, 'UTF-8') .
         '</h3>';

    echo '<p>' .
         htmlspecialchars($message, ENT_QUOTES, 'UTF-8') .
         '</p>';

    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodKhmer Database Installation</title>

    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Kantumruy Pro', sans-serif;
        }

        body {
            background: #f1f5f9;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 25px rgba(0,0,0,.06);
        }

        h2 {
            color: #f97316;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #f97316;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #ea580c;
        }

        .warning {
            background: #fff7ed;
            color: #9a3412;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>FoodKhmer Database Installer</h2>

    <p>រៀបចំ Database នៅលើ Neon PostgreSQL</p>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        try {

            // Connect database
            $pdo = getDB();

            // SQL file location
            $sqlFile = __DIR__ . '/database/SimpleFoodShop.sql';

            if (!is_file($sqlFile)) {
                throw new Exception(
                    'រកមិនឃើញ database/SimpleFoodShop.sql'
                );
            }

            // Read SQL
            $sql = file_get_contents($sqlFile);

            if ($sql === false || trim($sql) === '') {
                throw new Exception('SQL File ទទេ ឬមិនអាចអានបាន។');
            }

            // Remove UTF-8 BOM
            $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

            // Execute SQL
            $pdo->exec($sql);

            output(
                'Database ដំណើរការជោគជ័យ!',
                'Tables និងទិន្នន័យដែលមានក្នុង SQL File ត្រូវបានដំណើរការ។'
            );

            echo '
                <div style="margin-top:20px">
                    <a href="index.php" class="btn">
                        ចូលទៅកាន់គេហទំព័រ
                    </a>
                </div>
            ';

            echo '
                <div class="warning">
                    <strong>សុវត្ថិភាព៖</strong>
                    សូមបិទ ឬលុប init_db.php ចេញពី Server
                    បន្ទាប់ពីដំឡើងរួច។
                </div>
            ';

        } catch (Throwable $e) {

            error_log('Database installation error: ' . $e->getMessage());

            output(
                'មានបញ្ហាក្នុងការដំឡើង Database',
                'សូមពិនិត្យ Database Connection និង SQL File។',
                false
            );
        }
    } else {
    ?>

        <div class="warning">
            <strong>សូមប្រុងប្រយ័ត្ន!</strong>
            ឯកសារនេះនឹងដំណើរការ SQL ទៅលើ Database។
            សូម Backup ទិន្នន័យជាមុន។
        </div>

        <form method="POST">
            <button type="submit" class="btn">
                <i>⚙</i> ចាប់ផ្ដើមដំឡើង Database
            </button>
        </form>

    <?php } ?>

</div>

</body>
</html>
