<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $session_id = session_id();

    error_log("Update request: item_id=$item_id, quantity=$quantity, session_id=$session_id");

    // Validate input
    if ($item_id <= 0) {
        $response['message'] = 'Invalid item ID.';
        echo json_encode($response);
        exit;
    }
    
    if ($quantity <= 0) {
        // If quantity is 0 or less, treat it as a removal request
        $sql = "DELETE FROM cart_items WHERE id = ? AND session_id = ?";
        try {
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            
            $stmt->bind_param("is", $item_id, $session_id);
            if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

            if ($stmt->affected_rows > 0) {
                 $response = ['success' => true, 'message' => 'Item removed successfully.', 'removed' => true];
            } else {
                 $response['message'] = 'Item not found or already removed.';
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error removing item: " . $e->getMessage());
            $response['message'] = 'Error removing item: ' . $e->getMessage();
        }

    } else {
         // Update quantity (ensure quantity is not excessive, e.g., max 99)
         $quantity = min($quantity, 99); 
         
        $sql = "UPDATE cart_items SET quantity = ? WHERE id = ? AND session_id = ?";
        try {
            $stmt = $conn->prepare($sql);
             if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            $stmt->bind_param("iis", $quantity, $item_id, $session_id);
             if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

            if ($stmt->affected_rows > 0) {
                $response = ['success' => true, 'message' => 'Quantity updated successfully.'];
            } else {
                // Could be that the quantity was already set to the requested value, or item not found for this session
                 $response['message'] = 'Item not found for this session or quantity unchanged.';
                 // To be safe, let's still report success if the item exists with the target quantity
                 $check_sql = "SELECT id FROM cart_items WHERE id = ? AND session_id = ? AND quantity = ?";
                 $check_stmt = $conn->prepare($check_sql);
                 $check_stmt->bind_param("isi", $item_id, $session_id, $quantity);
                 $check_stmt->execute();
                 $check_result = $check_stmt->get_result();
                 if ($check_result->num_rows > 0) {
                      $response = ['success' => true, 'message' => 'Quantity is already set to the requested value.'];
                 }
                 $check_stmt->close();
            }
             $stmt->close();
        } catch (Exception $e) {
            error_log("Error updating quantity: " . $e->getMessage());
            $response['message'] = 'Error updating quantity: ' . $e->getMessage();
        }
    }
}

// Calculate and include the new cart count
if ($conn) {
    $count_sql = "SELECT SUM(quantity) as total FROM cart_items WHERE session_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    if($count_stmt){
        $count_stmt->bind_param("s", $session_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $response['cart_count'] = $count_result->fetch_assoc()['total'] ?? 0;
        $count_stmt->close();
    } else {
         error_log("Error preparing cart count statement: " . $conn->error);
         $response['cart_count'] = null; // Indicate count error
    }
} else {
    $response['cart_count'] = null; // Indicate count error due to db connection
}


echo json_encode($response);
?> 