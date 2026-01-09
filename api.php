<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database Connection
 $host = 'localhost';
 $db   = 'task_db';
 $user = 'root';
 $pass = ''; // Default XAMPP password is empty. Change if you set one.

 $conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

 $action = isset($_GET['action']) ? $_GET['action'] : '';

// --- HELPER FUNCTIONS ---
function sendJson($data) {
    echo json_encode($data);
    exit;
}

function getInput() {
    return json_decode(file_get_contents('php://input'), true);
}

// --- ROUTING ---

// 1. GET ALL USERS
if ($action === 'get_users') {
    $result = $conn->query("SELECT * FROM users ORDER BY name ASC");
    $users = [];
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    sendJson(['status' => 'success', 'data' => $users]);
}

// 2. ADD USER
if ($action === 'add_user') {
    $input = getInput();
    $name = $conn->real_escape_string($input['name']);
    
    // Check exists
    $check = $conn->query("SELECT id FROM users WHERE name='$name'");
    if($check->num_rows > 0) {
        sendJson(['status' => 'error', 'message' => 'User already exists']);
    }

    $conn->query("INSERT INTO users (name) VALUES ('$name')");
    sendJson(['status' => 'success']);
}

// 3. DELETE USER
if ($action === 'delete_user') {
    $input = getInput();
    $id = (int)$input['id'];
    $conn->query("DELETE FROM users WHERE id=$id");
    sendJson(['status' => 'success']);
}

// 4. GET TASKS (by Shift)
if ($action === 'get_tasks') {
    $shift = $conn->real_escape_string($_GET['shift']);
    $result = $conn->query("SELECT * FROM tasks WHERE shift='$shift' ORDER BY id ASC");
    $tasks = [];
    while($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    sendJson(['status' => 'success', 'data' => $tasks]);
}

// 5. ADD TASK
if ($action === 'add_task') {
    $input = getInput();
    $shift = $conn->real_escape_string($input['shift']);
    $text = $conn->real_escape_string($input['text']);
    $conn->query("INSERT INTO tasks (shift, text) VALUES ('$shift', '$text')");
    sendJson(['status' => 'success']);
}

// 6. UPDATE TASK
if ($action === 'update_task') {
    $input = getInput();
    $id = (int)$input['id'];
    $text = $conn->real_escape_string($input['text']);
    $conn->query("UPDATE tasks SET text='$text' WHERE id=$id");
    sendJson(['status' => 'success']);
}

// 7. TOGGLE TASK STATUS
if ($action === 'toggle_task') {
    $input = getInput();
    $id = (int)$input['id'];
    $conn->query("UPDATE tasks SET is_active = NOT is_active WHERE id=$id");
    sendJson(['status' => 'success']);
}

// 8. DELETE TASK
if ($action === 'delete_task') {
    $input = getInput();
    $id = (int)$input['id'];
    $conn->query("DELETE FROM tasks WHERE id=$id");
    sendJson(['status' => 'success']);
}

// 9. GET REPORT BY CONTEXT (User, Date, Shift) -> For History Loading
if ($action === 'get_report_context') {
    $user = $conn->real_escape_string($_GET['user']);
    $date = $conn->real_escape_string($_GET['date']);
    $shift = $conn->real_escape_string($_GET['shift']);

    // Get user ID first
    $uRes = $conn->query("SELECT id FROM users WHERE name='$user'");
    if($uRes->num_rows > 0) {
        $uRow = $uRes->fetch_assoc();
        $userId = $uRow['id'];

        // Get latest report for this context
        $sql = "SELECT * FROM reports WHERE user_id=$userId AND report_date='$date' AND shift='$shift' ORDER BY timestamp DESC LIMIT 1";
        $res = $conn->query($sql);
        if($res->num_rows > 0) {
            sendJson(['status' => 'success', 'data' => $res->fetch_assoc()]);
        } else {
            sendJson(['status' => 'success', 'data' => null]);
        }
    } else {
        sendJson(['status' => 'success', 'data' => null]);
    }
}


// 10. SUBMIT REPORT (WITH UPSERT LOGIC TO PREVENT DUPLICATES)
if ($action === 'submit_report') {
    $input = getInput();
    $user = $conn->real_escape_string($input['user']);
    $date = $conn->real_escape_string($input['reportDate']);
    $shift = $conn->real_escape_string($input['shift']);
    $total = (int)$input['total'];
    $completed = (int)$input['completed'];
    $percentage = (int)$input['percentage'];
    // Encode array of IDs to JSON string
    $checkedIds = $conn->real_escape_string(json_encode($input['checkedTaskIds']));

    // Get User ID
    $uRes = $conn->query("SELECT id FROM users WHERE name='$user'");
    if($uRes->num_rows === 0) {
        sendJson(['status' => 'error', 'message' => 'User not found']);
    }
    $userId = $uRes->fetch_assoc()['id'];

    // CHECK IF REPORT ALREADY EXISTS FOR THIS CONTEXT
    $checkSql = "SELECT id FROM reports WHERE user_id = $userId AND report_date = '$date' AND shift = '$shift'";
    $existingRes = $conn->query($checkSql);

    if ($existingRes && $existingRes->num_rows > 0) {
        // --- UPDATE EXISTING RECORD ---
        $row = $existingRes->fetch_assoc();
        $existingId = $row['id'];

        $updateSql = "UPDATE reports 
                     SET total_tasks = $total, 
                         completed_tasks = $completed, 
                         percentage = $percentage, 
                         checked_ids = '$checkedIds',
                         timestamp = CURRENT_TIMESTAMP 
                     WHERE id = $existingId";
        
        if($conn->query($updateSql)) {
            sendJson(['status' => 'success', 'message' => 'Report updated successfully', 'action' => 'updated']);
        } else {
            sendJson(['status' => 'error', 'message' => 'Update failed: ' . $conn->error]);
        }

    } else {
        // --- INSERT NEW RECORD ---
        $insertSql = "INSERT INTO reports (user_id, report_date, shift, total_tasks, completed_tasks, percentage, checked_ids) 
                     VALUES ($userId, '$date', '$shift', $total, $completed, $percentage, '$checkedIds')";
        
        if($conn->query($insertSql)) {
            sendJson(['status' => 'success', 'message' => 'Report submitted successfully', 'action' => 'inserted']);
        } else {
            sendJson(['status' => 'error', 'message' => 'Insert failed: ' . $conn->error]);
        }
    }
}


// // 10. SUBMIT REPORT
// if ($action === 'submit_report') {
//     $input = getInput();
//     $user = $conn->real_escape_string($input['user']);
//     $date = $conn->real_escape_string($input['reportDate']);
//     $shift = $conn->real_escape_string($input['shift']);
//     $total = (int)$input['total'];
//     $completed = (int)$input['completed'];
//     $percentage = (int)$input['percentage'];
//     // Encode array of IDs to JSON string
//     $checkedIds = json_encode($input['checkedTaskIds']);

//     // Get User ID
//     $uRes = $conn->query("SELECT id FROM users WHERE name='$user'");
//     if($uRes->num_rows === 0) {
//         sendJson(['status' => 'error', 'message' => 'User not found']);
//     }
//     $userId = $uRes->fetch_assoc()['id'];

//     $sql = "INSERT INTO reports (user_id, report_date, shift, total_tasks, completed_tasks, percentage, checked_ids) 
//             VALUES ($userId, '$date', '$shift', $total, $completed, $percentage, '$checkedIds')";
    
//     if($conn->query($sql)) {
//         sendJson(['status' => 'success']);
//     } else {
//         sendJson(['status' => 'error', 'message' => $conn->error]);
//     }
// }

// 11. GET DASHBOARD LOGS
if ($action === 'get_logs') {
    $startDate = isset($_GET['start']) ? $conn->real_escape_string($_GET['start']) : null;
    $endDate = isset($_GET['end']) ? $conn->real_escape_string($_GET['end']) : null;
    $userFilter = isset($_GET['user']) ? $conn->real_escape_string($_GET['user']) : 'ALL';

    $sql = "SELECT r.*, u.name as user_name FROM reports r JOIN users u ON r.user_id = u.id WHERE 1=1";

    if ($startDate && $endDate) {
        $sql .= " AND report_date BETWEEN '$startDate' AND '$endDate'";
    }

    if ($userFilter !== 'ALL') {
        // Join is needed, so we filter by user_name
        $sql .= " AND u.name = '$userFilter'";
    }

    $sql .= " ORDER BY report_date DESC, timestamp DESC";

    $result = $conn->query($sql);
    $logs = [];
    while($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    sendJson(['status' => 'success', 'data' => $logs]);
}

 $conn->close();
?>