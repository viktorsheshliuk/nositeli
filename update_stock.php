<?php
function var_dump_f ($val) {
  ob_start();
  var_dump($val);
  $output = ob_get_clean();
  file_put_contents('var_dump.txt', $output, FILE_APPEND|LOCK_EX);
}
// config.php содержит конфигурацию доступа к базе данных
require_once 'config.php';

$filename = "my_log.txt";
$present = date('l | jS \of F Y h:i:s A', time());
$entry = $present." ";

$entry .= "Работает скрипт обновления остатков\n";

header('Content-Type: application/json');

 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
//    var_dump_f($data);
     if (isset($data['model']) && isset($data['quantity'])) {

         $model = (int)$data['model'];
         $quantity = (int)$data['quantity'];

        $db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }

        $stmt = $db->prepare("UPDATE " . DB_PREFIX . "product SET quantity = quantity - ? WHERE model = ?");
        $stmt->bind_param('ii', $quantity, $model);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            $entry .= $stmt->error . "\n";
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }

        $stmt->close();
        $db->close();
    } else {
        $entry .= "Invalid data\n";
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
} else {
    $entry .= "Invalid request method\n";
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

    //file_put_contents($filename, $entry, FILE_APPEND|LOCK_EX);
    //test
?>
