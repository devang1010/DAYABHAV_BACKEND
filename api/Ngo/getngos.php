<?php 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require("../config/db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    if (isset($_GET['ngo_id'])) {
        // Fetch a specific NGO
        $ngoId = intval($_GET['ngo_id']); // Sanitize input

        $stmt = $conn->prepare("SELECT ngo_id, ngoname, email, phonenumber, address FROM ngos WHERE ngo_id = ?");
        $stmt->bind_param("i", $ngoId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $ngo = $result->fetch_assoc();
            echo json_encode(["status" => "success", "ngo" => $ngo]);
        } else {
            echo json_encode(["status" => "error", "message" => "NGO not found"]);
        }

        $stmt->close();
    } else {
        // Fetch all NGOs if no specific ID is provided
        $sql = "SELECT * FROM ngos";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $ngos = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $ngos[] = $row;
            }

            echo json_encode(["status" => "success", "data" => $ngos]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to fetch NGOs data"]);
        }
    }

    mysqli_close($conn);
}
?>
