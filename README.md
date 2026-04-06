# 會員管理系統 Member Management System

> 原生 PHP + MySQL 實作，具備完整 RBAC 權限控制的會員後台管理系統

---

## 專案簡介

這是一個從零開始手刻的會員管理系統，沒有使用任何 PHP 框架，目的是深入理解 Web 後端的核心概念，包含 Session 管理、資料庫設計、權限控制架構等。

---

## 技術亮點

### RBAC 權限控制（Role-Based Access Control）
- 設計 `users → user_roles → roles → role_permissions → permissions` 五表架構
- 透過 `hasPermission()` 動態查詢使用者權限，不依賴 hardcode
- `middleware.php` 實作守門員機制，概念類似 Django `@login_required` 裝飾器
- 支援多角色（admin / member / guest），可彈性擴充

### 資料庫設計
- 正規化至 3NF，避免資料重複
- 多對多關係透過 pivot table 處理（user_roles、role_permissions）
- 使用 PDO Prepared Statement 防止 SQL Injection

### 後台管理介面
- 使用者管理：新增、搜尋、分頁、停用/啟用、變更角色
- 角色管理：新增、編輯、刪除、顯示擁有權限
- 權限管理：矩陣式介面，點擊即可指派/移除權限

---

## 功能列表

| 功能 | 說明 |
|---|---|
| 會員註冊/登入/登出 | Session 管理、bcrypt 密碼雜湊 |
| 忘記密碼 | 重設密碼流程 |
| 個人資料編輯 | 修改名稱、密碼 |
| 使用者管理 | 新增、搜尋、分頁、停用/啟用 |
| 角色管理 | CRUD + 顯示權限 |
| 權限管理 | 矩陣式介面，彈性指派 |

---

## 技術棧

| 項目 | 技術 |
|---|---|
| 後端 | 原生 PHP 8.2 |
| 資料庫 | MySQL 8.0 |
| 前端 | HTML5 / CSS3（原生） |
| 伺服器 | Apache（XAMPP） |
| 版本控制 | Git / GitHub |

---

## 資料庫架構
users            會員資料（id, username, email, password_hash, is_active）
roles            角色定義（id, name, description）
permissions      權限定義（id, name, resource, action）
user_roles       會員 ↔ 角色（多對多 pivot）
role_permissions 角色 ↔ 權限（多對多 pivot）

---

## 安裝方式
```bash
# 1. clone 專案
git clone https://github.com/zsxdc9563-create/member-system.git

# 2. 匯入資料庫
mysql -u root -p < database.sql

# 3. 設定連線
cp db.example.php db.php
# 修改 db.php 填入你的資料庫資訊

# 4. 放到 XAMPP htdocs/ 資料夾，開啟 Apache
```

---


## 專案結構
MEMBER/
├── admin/
│   ├── navbar.php       後台導覽列
│   ├── user.php         使用者管理
│   ├── role.php         角色管理
│   └── permission.php   權限管理
├── auth.php             權限查詢函式（hasPermission / getUserRoles）
├── middleware.php       守門員（requireLogin / requirePermission / requireAdmin）
├── db.php               資料庫連線（不上傳）
├── db.example.php       連線設定範例
├── dashboard.php        會員首頁
├── profile.php          個人資料
├── login.html           登入頁面
├── register.html        註冊頁面
├── forgot_password.php  忘記密碼
├── style.css            共用樣式
└── database.sql         資料庫結構與初始資料

## 作者

[@zsxdc9563-create](https://github.com/zsxdc9563-create)