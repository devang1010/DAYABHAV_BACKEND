<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    // get requirements for a specific NGO
    if (isset($_GET["ngo_id"])) {
        $ngoId = intval($_GET["ngo_id"]);
        $sql = "SELECT * FROM requirements WHERE ngo_id = $ngoId";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $requirements = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $requirements[] = $row;
            }

            echo json_encode(["status" => "success", "data" => $requirements]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to fetch requirements"]);
        }

        mysqli_close($conn);
        exit();
    } 
    // Get all requirements
    else {
        $sql = "SELECT * FROM requirements";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $requirements = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $requirements[] = $row;
            }

            echo json_encode(["status" => "success", "data" => $requirements]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to fetch requirements"]);
        }

        mysqli_close($conn);
        exit();
    }
}

// add requirements
if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['ngo_id'], $data['item_name'], $data["quantity"], $data['ngoname'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    $ngoId = intval($data['ngo_id']);
    $itemname = htmlspecialchars(strip_tags($data['item_name']));
    $quantity = intval($data['quantity']);  // Ensure it's an integer
    $ngoname = htmlspecialchars(strip_tags($data['ngoname']));
    
    // Get priority with default value of 1 if not set
    $priority = isset($data['priority']) ? intval($data['priority']) : 1;
    
    // Validate priority (should be between 1 and 5)
    if ($priority < 1 || $priority > 5) {
        $priority = 1; // Set to default if invalid
    }

    // Check if the NGO exists
    $stmt = $conn->prepare("SELECT ngo_id FROM ngos WHERE ngo_id = ?");
    $stmt->bind_param("i", $ngoId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "NGO not found"]);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Add the requirement with priority
    $stmt = $conn->prepare("INSERT INTO requirements (ngo_id, item_name, quantity, priority, ngoname) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $ngoId, $itemname, $quantity, $priority, $ngoname);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Requirement added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add requirement"]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Delete requirement
if ($method == "DELETE") {
    // Retrieve requirement_id properly
    if (!isset($_REQUEST["requirement_id"]) || empty($_REQUEST["requirement_id"])) {
        echo json_encode(["status" => "error", "message" => "Missing requirement_id"]);
        exit();
    }

    $requirementId = intval($_REQUEST["requirement_id"]);

    // Ensure valid requirement_id
    if ($requirementId <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid requirement_id"]);
        exit();
    }

    // Check if the requirement exists
    $stmt = $conn->prepare("SELECT requirement_id FROM requirements WHERE requirement_id = ?");
    $stmt->bind_param("i", $requirementId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Requirement not found"]);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Delete the requirement
    $stmt = $conn->prepare("DELETE FROM requirements WHERE requirement_id = ?");
    $stmt->bind_param("i", $requirementId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Requirement deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete requirement"]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Handle unsupported methods
http_response_code(405);
echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
exit();