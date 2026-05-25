<?php
$file = 'index.html';
$content = file_get_contents($file);

// إصلاح اسم المجلد العربي
$content = str_replace(
    './لوحة التحكم · صندوق التحكم_files/',
    './files/',
    $content
);

// إصلاح الرابط المكسور
$content = str_replace(
    'https://www.https://',
    'https://',
    $content
);

// حفظ الملف
file_put_contents($file, $content);
echo 'تم الإصلاح بنجاح!';
?>
