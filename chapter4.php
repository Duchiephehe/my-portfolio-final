<?php
// ===========================================
// PHẦN 1: THIẾT LẬP KẾT NỐI VÀ LOGIC XỬ LÝ CRUD
// ===========================================
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "cse485_web"; 

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối (Quan trọng: Đảm bảo không có lỗi đánh máy $conn)
if ($conn->connect_error) { 
    die("Kết nối CSDL thất bại: " . $conn->connect_error); 
}

$message = ""; 

// --- LOGIC XỬ LÝ (CREATE/UPDATE) ---

// 1. Logic Thêm sinh viên (CREATE - INSERT)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
    $ten_sinh_vien = trim($_POST['ten_sinh_vien']);
    $email = trim($_POST['email']);

    if (!empty($ten_sinh_vien) && !empty($email)) {
        $sql_insert = "INSERT INTO sinhvien (ten_sinh_vien, email) VALUES (?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("ss", $ten_sinh_vien, $email); 
        
        if ($stmt->execute()) {
            $message = "<p style='color: green; font-weight: bold;'>✅ Thêm sinh viên thành công!</p>";
        } else {
            $message = "<p style='color: red; font-weight: bold;'>❌ Lỗi khi thêm: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// 2. Logic Cập nhật sinh viên (UPDATE)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_student'])) {
    $id_to_update = trim($_POST['id']);
    $ten_sinh_vien = trim($_POST['ten_sinh_vien']);
    $email = trim($_POST['email']);

    if (!empty($ten_sinh_vien) && !empty($email)) {
        $sql_update = "UPDATE sinhvien SET ten_sinh_vien = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ssi", $ten_sinh_vien, $email, $id_to_update); 

        if ($stmt->execute()) {
            $message = "<p style='color: green; font-weight: bold;'>✅ Cập nhật sinh viên ID " . $id_to_update . " thành công!</p>";
        } else {
            $message = "<p style='color: red; font-weight: bold;'>❌ Lỗi khi cập nhật: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// 3. Logic Xóa sinh viên (DELETE)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = $_GET['id'];
    $sql_delete = "DELETE FROM sinhvien WHERE id = ?";
    
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $id_to_delete); 

    if ($stmt->execute()) {
        $message = "<p style='color: green; font-weight: bold;'>✅ Xóa sinh viên ID " . $id_to_delete . " thành công!</p>";
    } else {
        $message = "<p style='color: red; font-weight: bold;'>❌ Lỗi khi xóa: " . $stmt->error . "</p>";
    }
    $stmt->close();
    // Chuyển hướng để xóa tham số trên URL
    header("Location: chapter4.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Sinh Viên Hoàn Chỉnh</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        form { margin-bottom: 25px; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        input[type="text"], input[type="email"] { padding: 10px; margin: 8px 0; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        input[type="submit"] { padding: 10px 20px; background-color: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; }
        input[type="submit"]:hover { background-color: #0056b3; }
    </style>
</head>
<body>

    <?php
    // Kiểm tra và lấy dữ liệu nếu đang ở chế độ chỉnh sửa
    $is_editing = false;
    $edit_student = null;
    
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id_to_edit = $_GET['id'];
        $sql_select_one = "SELECT id, ten_sinh_vien, email FROM sinhvien WHERE id = ?";
        $stmt = $conn->prepare($sql_select_one);
        $stmt->bind_param("i", $id_to_edit);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $is_editing = true;
            $edit_student = $result->fetch_assoc();
        }
        $stmt->close();
    }
    ?>

    <h2>📝 
        <?php echo $is_editing ? 'Chỉnh Sửa Sinh Viên' : 'Thêm Sinh Viên Mới'; ?>
    </h2>
    <?php echo $message; // Hiển thị thông báo ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <?php if ($is_editing): ?>
            <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
        <?php endif; ?>

        <label for="ten_sinh_vien">Tên Sinh Viên:</label><br>
        <input type="text" id="ten_sinh_vien" name="ten_sinh_vien" required 
            value="<?php echo $is_editing ? htmlspecialchars($edit_student['ten_sinh_vien']) : ''; ?>"><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required 
            value="<?php echo $is_editing ? htmlspecialchars($edit_student['email']) : ''; ?>"><br><br>

        <input type="submit" 
               name="<?php echo $is_editing ? 'update_student' : 'add_student'; ?>"
               value="<?php echo $is_editing ? 'Cập Nhật' : 'Thêm Sinh Viên'; ?>">
        
        <?php if ($is_editing): ?>
            <a href="chapter4.php" style="margin-left: 15px;">Hủy bỏ</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>📚 Danh Sách Sinh Viên</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Sinh Viên</th>
                <th>Email</th>
                <th>Ngày Tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 4. Logic Hiển thị toàn bộ sinh viên (READ - SELECT)
            $sql_select = "SELECT id, ten_sinh_vien, email, ngay_tao FROM sinhvien ORDER BY id DESC";
            $result = $conn->query($sql_select);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["ten_sinh_vien"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                    echo "<td>" . $row["ngay_tao"] . "</td>";
                    // Các nút thao tác SỬA và XÓA
                    echo "<td>";
                    echo "<a href='chapter4.php?action=edit&id=" . $row["id"] . "' style='color: blue; margin-right: 10px;'>Sửa</a>"; 
                    echo "<a href='chapter4.php?action=delete&id=" . $row["id"] . "' style='color: red;' onclick='return confirm(\"Bạn có chắc muốn xóa sinh viên ID: " . $row["id"] . "?\")'>Xóa</a>"; 
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Chưa có sinh viên nào trong CSDL.</td></tr>";
            }

            // Đóng kết nối CSDL
            $conn->close();
            ?>
        </tbody>
    </table>

</body>
</html>