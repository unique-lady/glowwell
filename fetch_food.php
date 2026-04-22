<?php
include 'config.php';
mysqli_set_charset($conn, "utf8mb4");

if (isset($_GET['query'])) {
    $search = "%" . $_GET['query'] . "%";
    $stmt = $conn->prepare("SELECT * FROM food_dictionary WHERE food_name_en LIKE ? OR food_name_ar LIKE ? LIMIT 10");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $measure = !empty($row['serving_desc']) ? ' (' . $row['serving_desc'] . ')' : '';
        $data[] = array(
            'food_name' => $row['food_name_ar'] . ' / ' . $row['food_name_en'] . $measure,
            'calories' => (int)$row['calories'], // إجبار الأرقام لتكون بالإنجليزية
            'protein' => (float)$row['protein'],
            'fat' => (float)$row['fat'],
            'carbs' => (float)$row['carbs']
        );
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
}
?>