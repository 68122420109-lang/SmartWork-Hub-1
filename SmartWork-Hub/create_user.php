<?php
require_once '../config/database.php'; // หรือพาธเชื่อมต่อ DB ของคุณ

// กำหนด อีเมล และ รหัสผ่าน ที่ต้องการใช้ล็อกอิน
$email = '68122420109@srru.ac.th';
$password = '123456'; 
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // เพิ่มข้อมูลลงตาราง users (ปรับชื่อคอลัมน์ให้ตรงกับ DB ของคุณ)
    $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$email, $hashed_password]);
    echo "สร้างผู้ใช้สำเร็จ! อีเมล: $email | รหัสผ่าน: $password";
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>
