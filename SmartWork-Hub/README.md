# SmartWork-Hub

ระบบจัดการพนักงานและงานในองค์กร

## Technology Stack
- Frontend: HTML5, CSS3, Vanilla JS
- Backend: PHP
- Database: TiDB Cloud (MySQL/PDO)
- Version Control: Git + GitHub

## Project Structure
- `frontend/`: UI files (HTML, CSS, JS)
- `backend/`: PHP backend API, Models, Controllers
- `database/`: SQL schema
- `docs/`: Documentation

## การตั้งค่า TiDB และ Database
1. สร้าง Cluster ใน TiDB Cloud
2. นำข้อมูล Host, Port, User, Password มาใส่ใน `.env`
3. รันสคริปต์ `database/schema.sql` เพื่อสร้างตาราง

## วิธีติดตั้งและรันโปรเจกต์
1. Clone โปรเจกต์
2. คัดลอก `.env.example` เป็น `.env` และแก้ไขค่า Database
3. เข้าโฟลเดอร์ root ของโปรเจกต์
4. รันคำสั่ง `php -S localhost:8000` เพื่อเปิด Backend และ Frontend
5. เปิดเบราว์เซอร์ไปที่ `http://localhost:8000/frontend/login.html`

## ข้อมูล Login เริ่มต้น (สร้างเองใน DB หรือใช้ script)
- สร้าง Admin User โดยนำรหัสผ่านไปแฮชผ่าน `password_hash()` ใน PHP ก่อนบันทึกลงฐานข้อมูล

## API
- ทุก API อยู่ใน `backend/api/`
- ตัวอย่าง: POST `/backend/api/auth.php` สำหรับ Login
