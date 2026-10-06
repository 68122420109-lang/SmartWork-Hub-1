FROM php:8.2-apache

# ติดตั้ง Extension สำหรับจัดการฐานข้อมูล MySQL / TiDB
RUN docker-php-ext-install mysqli pdo pdo_mysql

# เปิดใช้งาน mod_rewrite สำหรับ Apache
RUN a2enmod rewrite

# คัดลอกไฟล์โปรเจกต์ทั้งหมดเข้าสู่เซิร์ฟเวอร์
COPY . /var/www/html/

EXPOSE 80
