<?php

$filename = 'articles.php'; // Исходный файл
$partSize = 500; // Количество артикулов в одной части

// Чтение содержимого файла
$fileContent = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!$fileContent) {
    die("Не удалось прочитать файл.");
}

// Разбиение файла на части
$partNumber = 1;
for ($i = 0; $i < count($fileContent); $i += $partSize) {
    $partContent = array_slice($fileContent, $i, $partSize);
    $newFilename = "articles_part{$partNumber}.php";
    if (file_put_contents($newFilename, implode("\n", $partContent)) === false) {
        echo "Ошибка при записи файла $newFilename";
    } else {
        echo "Файл $newFilename успешно создан.\n";
    }
    $partNumber++;
}
?>
