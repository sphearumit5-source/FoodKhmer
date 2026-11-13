<?php
// init_db.php
// រៀបចំ Tables ក្នុង Neon PostgreSQL ដោយស្វ័យប្រវត្តិ

require_once __DIR__ . '/config/database.php';

// HTML Header
echo "
<!DOCTYPE html>
<html lang='km'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Installation</title>
</head>
<body style='
    font-family: sans-serif;
    background: #f1f5f9;
    padding: 20px;
'>
";

echo "
<div style='
    max-width: 700px;
    margin: 40px auto;
    padding: 30px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
'>
";

echo "
<h2 style='color:#ff6b00;'>
    រៀបចំ Database លើ Neon PostgreSQL
</h2>
";

try {

    // 1. Connect Database
    $pdo = getDB();

    // 2. Check SQL File
    $sqlFile = __DIR__ . '/database/SimpleFoodShop.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception(
            'រកមិនឃើញឯកសារ database/SimpleFoodShop.sql ទេ!'
        );
    }

    // 3. Read SQL File
    $sql = file_get_contents($sqlFile);

    if ($sql === false || trim($sql) === '') {
        throw new Exception(
            'មិនអាចអានឯកសារ SQL ឬឯកសារទទេ!'
        );
    }

    // 4. Remove UTF-8 BOM
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

    // 5. Execute SQL
    $pdo->exec($sql);

    // 6. Success Message
    echo "
    <div style='
        padding: 20px;
        background: #dcfce7;
        color: #166534;
        border-radius: 8px;
        margin-top: 20px;
    '>
        <h3>✅ ដំណើរការជោគជ័យ!</h3>

        <p>
            Database Tables និងទិន្នន័យគំរូ
            ត្រូវបានបង្កើតរួចរាល់លើ Neon PostgreSQL។
        </p>
    </div>
    ";

    // 7. Admin Account Information
    echo "
    <div style='
        margin-top: 20px;
        padding: 20px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    '>

        <h3>គណនី Admin លំនាំដើម</h3>

        <p>
            Username:
            <strong>admin</strong>
        </p>

        <p>
            Password:
            <strong>Admin@12345</strong>
        </p>

        <p style='color:#dc2626;font-size:13px;'>
            សូមប្ដូរ Password បន្ទាប់ពីដំឡើងរួច។
        </p>

    </div>
    ";

    // 8. Website Button
    echo "
    <div style='margin-top:25px;'>

        <a href='index.php'
           style='
                display:inline-block;
                padding:12px 24px;
                background:#ff6b00;
                color:#ffffff;
                text-decoration:none;
                border-radius:6px;
                font-weight:bold;
           '>

            ចូលទៅកាន់គេហទំព័រ

        </a>

    </div>
    ";

} catch (Throwable $e) {

    // Error Message
    echo "
    <div style='
        padding:20px;
        background:#fee2e2;
        color:#991b1b;
        border-radius:8px;
        margin-top:20px;
    '>

        <h3>❌ មានកំហុសក្នុងការរៀបចំ Database!</h3>

        <p>"
            . htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            )
        . "</p>

    </div>
    ";

}

echo "
</div>
</body>
</html>
";
