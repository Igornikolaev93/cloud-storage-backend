<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudDrive - Ваше облачное хранилище</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --border-color: #dee2e6;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark-color);
            line-height: 1.6;
        }

        /* Header Styles */
        .header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .logo i {
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Main Container */
        .container {
            display: flex;
            max-width: 1400px;
            margin: 2rem auto;
            gap: 2rem;
            padding: 0 1rem;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            height: fit-content;
        }

        .sidebar h3 {
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 0.8rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark-color);
            transition: all 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }

        .storage-info {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .storage-bar {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin: 10px 0;
            overflow: hidden;
        }

        .storage-progress {
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 4px;
            width: 65%;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .content-header h1 {
            color: var(--dark-color);
            font-size: 1.8rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-outline:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        /* File Grid */
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .file-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }

        .file-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .file-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .file-icon.folder {
            color: #ffb703;
        }

        .file-icon.pdf {
            color: #e63946;
        }

        .file-icon.image {
            color: #2a9d8f;
        }

        .file-icon.document {
            color: var(--primary-color);
        }

        .file-name {
            font-weight: 500;
            margin-bottom: 0.5rem;
            word-break: break-word;
        }

        .file-size {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .file-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 1rem;
        }

        .action-btn {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s;
        }

        .action-btn:hover {
            color: var(--primary-color);
        }

        /* Upload Area */
        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            padding: 3rem 2rem;
            text-align: center;
            margin-bottom: 2rem;
            transition: all 0.3s;
        }

        .upload-area:hover, .upload-area.dragover {
            border-color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.05);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Table View */
        .file-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .file-table th {
            text-align: left;
            padding: 1rem;
            border-bottom: 2px solid var(--border-color);
            color: var(--dark-color);
            font-weight: 600;
        }

        .file-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .file-table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Login/Register Forms */
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            padding: 2rem;
        }

        .auth-box {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h2 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .nav-links {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .file-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header with Navigation -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <i class="fas fa-cloud"></i>
                <span>CloudDrive</span>
            </a>
            
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Главная</a>
                <a href="files.php"><i class="fas fa-folder"></i> Мои файлы</a>
                <a href="shared.php"><i class="fas fa-share-alt"></i> Общие файлы</a>
                <a href="recent.php"><i class="fas fa-history"></i> Недавние</a>
            </div>
            
            <div class="user-menu">
                <div class="user-avatar">ИИ</div>
                <span>Иван Иванов</span>
                <a href="logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i> Выйти</a>
            </div>
        </nav>
    </header>

    <!-- Main Container -->
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3>Навигация</h3>
            <ul class="sidebar-menu">
                <li><a href="files.php" class="active"><i class="fas fa-folder"></i> Мои файлы</a></li>
                <li><a href="photos.php"><i class="fas fa-images"></i> Фотографии</a></li>
                <li><a href="documents.php"><i class="fas fa-file-alt"></i> Документы</a></li>
                <li><a href="shared.php"><i class="fas fa-share-alt"></i> Общий доступ</a></li>
                <li><a href="trash.php"><i class="fas fa-trash-alt"></i> Корзина</a></li>
            </ul>
            
            <div class="storage-info">
                <p>Использовано: <strong>8.2 ГБ</strong> из <strong>15 ГБ</strong></p>
                <div class="storage-bar">
                    <div class="storage-progress"></div>
                </div>
                <p><a href="#" style="color: var(--primary-color); text-decoration: none;">Увеличить хранилище</a></p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Мои файлы</h1>
                <div>
                    <button class="btn btn-outline" id="view-toggle">
                        <i class="fas fa-th"></i> Вид
                    </button>
                    <button class="btn btn-primary" id="upload-btn">
                        <i class="fas fa-upload"></i> Загрузить
                    </button>
                </div>
            </div>

            <!-- Upload Area -->
            <div class="upload-area" id="upload-area">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <h3>Перетащите файлы сюда</h3>
                <p>или</p>
                <button class="btn btn-primary" id="browse-btn">
                    Выберите файлы
                </button>
                <p style="margin-top: 1rem; color: #6c757d; font-size: 0.9rem;">
                    Максимальный размер файла: 2 ГБ
                </p>
            </div>

            <!-- File List/Grid -->
            <div id="file-container">
                <!-- Grid View (default) -->
                <div class="file-grid" id="grid-view">
                    <div class="file-card">
                        <div class="file-icon folder">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="file-name">Рабочие документы</div>
                        <div class="file-size">15 файлов</div>
                        <div class="file-actions">
                            <button class="action-btn"><i class="fas fa-share-alt"></i></button>
                            <button class="action-btn"><i class="fas fa-ellipsis-v"></i></button>
                        </div>
                    </div>
                    
                    <div class="file-card">
                        <div class="file-icon pdf">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="file-name">Отчет 2023.pdf</div>
                        <div class="file-size">2.4 МБ</div>
                        <div class="file-actions">
                            <button class="action-btn"><i class="fas fa-download"></i></button>
                            <button class="action-btn"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                    
                    <div class="file-card">
                        <div class="file-icon image">
                            <i class="fas fa-file-image"></i>
                        </div>
                        <div class="file-name">Фото отпуска.jpg</div>
                        <div class="file-size">4.7 МБ</div>
                        <div class="file-actions">
                            <button class="action-btn"><i class="fas fa-download"></i></button>
                            <button class="action-btn"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                    
                    <div class="file-card">
                        <div class="file-icon document">
                            <i class="fas fa-file-word"></i>
                        </div>
                        <div class="file-name">Документ.docx</div>
                        <div class="file-size">1.2 МБ</div>
                        <div class="file-actions">
                            <button class="action-btn"><i class="fas fa-download"></i></button>
                            <button class="action-btn"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Table View (hidden by default) -->
                <table class="file-table" id="table-view" style="display: none;">
                    <thead>
                        <tr>
                            <th>Имя</th>
                            <th>Размер</th>
                            <th>Изменен</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <i class="fas fa-folder" style="color: #ffb703; margin-right: 10px;"></i>
                                Рабочие документы
                            </td>
                            <td>15 файлов</td>
                            <td>Вчера</td>
                            <td>
                                <button class="action-btn"><i class="fas fa-share-alt"></i></button>
                                <button class="action-btn"><i class="fas fa-ellipsis-v"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <i class="fas fa-file-pdf" style="color: #e63946; margin-right: 10px;"></i>
                                Отчет 2023.pdf
                            </td>
                            <td>2.4 МБ</td>
                            <td>3 дня назад</td>
                            <td>
                                <button class="action-btn"><i class="fas fa-download"></i></button>
                                <button class="action-btn"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // View Toggle
        document.getElementById('view-toggle').addEventListener('click', function() {
            const gridView = document.getElementById('grid-view');
            const tableView = document.getElementById('table-view');
            const icon = this.querySelector('i');
            
            if (gridView.style.display === 'none') {
                gridView.style.display = 'grid';
                tableView.style.display = 'none';
                icon.className = 'fas fa-th';
                this.innerHTML = '<i class="fas fa-th"></i> Вид';
            } else {
                gridView.style.display = 'none';
                tableView.style.display = 'table';
                icon.className = 'fas fa-list';
                this.innerHTML = '<i class="fas fa-list"></i> Вид';
            }
        });

        // Upload Area Drag & Drop
        const uploadArea = document.getElementById('upload-area');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            uploadArea.classList.add('dragover');
        }

        function unhighlight() {
            uploadArea.classList.remove('dragover');
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        function handleFiles(files) {
            ([...files]).forEach(uploadFile);
        }

        function uploadFile(file) {
            // Здесь будет логика загрузки файла на сервер
            console.log('Загружаем файл:', file.name);
            alert(`Файл "${file.name}" будет загружен после реализации бэкенда`);
        }

        // Browse Files Button
        document.getElementById('browse-btn').addEventListener('click', function() {
            const input = document.createElement('input');
            input.type = 'file';
            input.multiple = true;
            input.onchange = function(e) {
                handleFiles(e.target.files);
            };
            input.click();
        });

        // Upload Button
        document.getElementById('upload-btn').addEventListener('click', function() {
            document.getElementById('browse-btn').click();
        });
    </script>
</body>
</html>
