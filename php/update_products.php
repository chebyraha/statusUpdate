<?php
error_reporting(E_ERROR);
ini_set('display_errors', 1);
set_time_limit(0);

require_once './config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('web');

$logFile = '/log/update_log.txt'; // Имя файла журнала
$reportFile = '/log/update_report.txt'; // Имя файла для отчета

// Функция для логирования событий
function logEvent($message) {
    global $logFile;
    $logEntry = date('[Y-m-d H:i:s]') . ' ' . $message . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Обработка запроса на обновление товара по ID
if(isset($_POST['productId'])) {
    $productId = $_POST['productId'];
    updateProductById($modx, $productId);
}

// Обработка запроса на обновление всех товаров
if(isset($_POST['updateAll'])) {
    updateAllProducts($modx);
}

// Обработка запроса на очистку статуса popular для всех товаров
if(isset($_POST['clearPopular'])) {
    clearPopularStatus($modx);
}

// Обработка запроса на очистку статуса new для всех товаров
if(isset($_POST['clearNew'])) {
    clearNewStatus($modx);
}

// Обработка заброса обнвление по бренду 
if(isset($_POST['vendorId'])) {
  $vendorId = $_POST['vendorId'];
  updateProductsByVendor($modx, $vendorId);
}

function updateProductById(modX $modx, $productId) {
    // Получаем текущую дату и вычитаем 2 месяца для определения границы
    $dateSixMonthsAgo = date('Y-m-d H:i:s', strtotime('-6 months'));

    // Запрос для получения данных о товаре
    $productQuery = "SELECT sc.id, sc.createdon, mp.vendor, mp.new
                     FROM modx_site_content sc
                     JOIN modx_ms2_products mp ON sc.id = mp.id
                     WHERE sc.id = :productId";

    $stmt = $modx->prepare($productQuery);
    $stmt->execute([':productId' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $vendor = $product['vendor'];
        $newStatus = $product['new'];
        // $createdon = $product['createdon'];
        // $timestamp = date('Y-m-d H:i:s', $createdon);
        $createdon = date($product['createdon']);
        $currentDate = strtotime(date('Y-m-d H:i:s'));
        $monthsPassed = (($currentDate - $createdon) / (60 * 60 * 24 * 30)); // Разница в месяцах
        
        $menuindexUpdate = 0; // Инициализация переменной для обновления menuindex
        
        // Массив соответствий vendor и menuindex
        $vendorMenuindexMap = [
            1 => [-99999, -9999999], 
            
        ];
        
        // Выбор соответствующего menuindex на основе vendor и newStatus
        $menuindexUpdate = isset($vendorMenuindexMap[$vendor][$newStatus]) ? $vendorMenuindexMap[$vendor][$newStatus] : null;

        // Обновление menuindex в modx_site_content
        if ($menuindexUpdate !== null) {
            $updateMenuindexQuery = "UPDATE modx_site_content SET menuindex = {$menuindexUpdate} WHERE id = {$productId}";
            $modx->query($updateMenuindexQuery);
            $menuindexMessage = "updateProductById – Menuindex of product ID {$productID} updated to {$menuindexUpdate} successfully!";
        }
        
        if($monthsPassed <= 2) {
          $newStatus = 1;
          $popularStatus = 0;
        } elseif ($monthsPassed > 2 && $monthsPassed <= 6) {
          $newStatus = 0;
          $popularStatus = 1;
        } else {
          $newStatus = 0;
          $popularStatus = 0;
        } 
        
        $updateStatusQuery = "UPDATE modx_ms2_products
                              SET new = :newStatus, popular = :popularStatus
                              WHERE id = :productId";
        $updateStatusStmt = $modx->prepare($updateStatusQuery);
        $updateStatusStmt->execute([
          ':newStatus' => $newStatus,
          ':popularStatus' => $popularStatus,
          ':productId' => $productId
          ]);
          
        $statusMessage = "updateProductById – Status of product ID={$productId} updated to new = {$newStatus}, popular = {$popularStatus}.";
        
        if(isset($menuindexMessage) && isset($statusMessage)) {
            echo "Обновление товара закончено!";
        } else {
          echo "Произошла ошибка обновления! Обратиться к администратору!";
        }
        
    }
    
    // Логирование события
    logEvent($menuindexMessage);
    logEvent($statusMessage);
}

function updateAllProducts(modX $modx) {
    // Получаем текущую дату и вычитаем 2 месяца для определения границы
    $dateSixMonthsAgo = date('Y-m-d H:i:s', strtotime('-6 months'));

    // Запрос для получения данных о всех товарах
    $productQuery = "SELECT sc.id, sc.createdon, mp.vendor, mp.new
                     FROM modx_site_content sc
                     JOIN modx_ms2_products mp ON sc.id = mp.id";

    $stmt = $modx->prepare($productQuery);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $productId = $product['id'];
        $vendor = $product['vendor'];
        $newStatus = $product['new'];
        $createdon = date($product['createdon']);
        $currentDate = strtotime(date('Y-m-d H:i:s'));
        $monthsPassed = (($currentDate - $createdon) / (60 * 60 * 24 * 30)); // Разница в месяцах
        $menuindexUpdate = 0; // Инициализация переменной для обновления menuindex
        
        // Массив соответствий vendor и menuindex
        $vendorMenuindexMap = [
            1 => [-99999, -9999999],
            
        ];
        
        // Выбор соответствующего menuindex на основе vendor и newStatus
        $menuindexUpdate = isset($vendorMenuindexMap[$vendor][$newStatus]) ? $vendorMenuindexMap[$vendor][$newStatus] : null;
        
        $menuindexMessage = "updateAllProducts – Menuindex of product ID {$productID} updated to {$menuindexUpdate} successfully!";
        
        if($monthsPassed <= 2) {
          $newStatus = 1;
          $popularStatus = 0;
        } elseif ($monthsPassed > 2 && $monthsPassed <= 6) {
          $newStatus = 0;
          $popularStatus = 1;
        } else {
          $newStatus = 0;
          $popularStatus = 0;
        } 
        
        $updateStatusQuery = "UPDATE modx_ms2_products
                              SET new = :newStatus, popular = :popularStatus
                              WHERE id = :productId";
        $updateStatusStmt = $modx->prepare($updateStatusQuery);
        $updateStatusStmt->execute([
          ':newStatus' => $newStatus,
          ':popularStatus' => $popularStatus,
          ':productId' => $productId
          ]);
          
        $statusMessage = "updateAllProducts – Status of product ID={$productId} updated to new = {$newStatus}, popular = {$popularStatus}.\n";
    
        if(isset($menuindexMessage) && isset($statusMessage)) {
            echo "Обновление товаров закончено!";
        } else {
          echo "Произошла ошибка обновления! Обратиться к администратору!";
        }
      
    }
    
    // Логирование события
     logEvent("All products updated successfully!");
     logEvent($menuindexMessage);
     logEvent($statusMessage);
}

// / Функция для очистки статуса popular для всех товаров
function clearPopularStatus(modX $modx) {
    global $logFile;
    // Очистка статуса popular для всех товаров
    $modx->exec("UPDATE modx_ms2_products SET popular = 0");
    // Логирование события
    logEvent("clearPopularStatus – Cleared popular status for all products.");
}

// Функция обновления по бренду
function updateProductsByVendor(modx $modx, $vendorId) {
  
  $dateTwoMonthsAgo = date('Y-m-d H:i:s', strtotime('-2 months'));
  
  $productQuery = "SELECT sc.id, sc.createdon, mp.vendor, mp.new
                   FROM modx_site_content sc
                   JOIN modx_ms2_products mp ON sc.id = mp.id
                   WHERE mp.vendor = :vendorId";
                   
  $stmt = $modx->prepare($productQuery);
  $stmt->execute([':vendorId' => $vendorId]);
  $products = $stmt->fetchALl(PDO::FETCH_ASSOC);
  
  foreach ($products as $product) {
    $productId = $product['id'];
    $newStatus = $product['new'];
    $createdon = date($product['createdon']);
    $currentDate = strtotime(date('Y-m-d H:i:s'));
    $monthsPassed = (($currentDate - $createdon) / (60 * 60 * 24 * 30)); // Разница в месяцах
    
    if($monthsPassed <= 2) {
        $newStatus = 1;
        $popularStatus = 0;
    } elseif ($monthsPassed > 2 && $monthsPassed <= 6) {
        $newStatus = 0;
        $popularStatus = 1;
    } else {
        $newStatus = 0;
        $popularStatus = 0;
    } 
        
    $updateStatusQuery = "UPDATE modx_ms2_products
                          SET new = :newStatus, popular = :popularStatus
                          WHERE id = :productId";
    $updateStatusStmt = $modx->prepare($updateStatusQuery);
    $updateStatusStmt->execute([
      ':newStatus' => $newStatus,
      ':popularStatus' => $popularStatus,
      ':productId' => $productId
    ]);
          
    $statusMessage = "updateProductsByVendor – Status of product ID={$productId} updated to new = {$newStatus}, popular = {$popularStatus}.";
    
    if(isset($statusMessage)) {
        echo 'Обвление по бренду закончено!';
    } else {
        echo 'Произошла ошибка обновления! Обратиться к администратору!';
    }
    
    logEvent("$statusMessage");
  }
}

// / Функция для очистки статуса popular для всех товаров
function clearNewStatus(modX $modx) {
    global $logFile;
    // Очистка статуса popular для всех товаров
    $modx->exec("UPDATE modx_ms2_products SET new = 0");
    // Логирование события
    logEvent("clearNewStatus – Cleared new status for all products.");
}

// Создание отчета
function createReport() {
    global $logFile, $reportFile;
    $logContent = file_get_contents($logFile);
    // Запись содержимого журнала в файл отчета
    file_put_contents($reportFile, $logContent);
}

// Создаем отчет по окончании всех операций
register_shutdown_function('createReport');

?>