<?php
session_start();
if (!isset($_SESSION['user_authenticated'])) {
    header('Location: index.php');
    exit;
}

$dbPath = __DIR__ . '/_db/';
$uploadPath = __DIR__ . '/_uploads/';
$folderDbPath = __DIR__ . '/folder_db/';

// Ensure directories exist
foreach ([$dbPath, $uploadPath, $folderDbPath] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0750, true);
}

$message = '';
$messageType = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cmdb_file'])) {
    $file = $_FILES['cmdb_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['xlsx', 'xls', 'csv'];
        
        if (in_array($ext, $allowedExts)) {
            $uploadedFile = $uploadPath . 'cmdb_' . time() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $uploadedFile)) {
                // Process Excel to JSON
                $jsonData = processExcelToJson($uploadedFile);
                if ($jsonData) {
                    $jsonFile = $dbPath . 'cmdb_database.json';
                    file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
                    $message = 'Database uploaded and processed successfully! Total records: ' . count($jsonData);
                    $messageType = 'success';
                } else {
                    $message = 'Error processing Excel file. Please check the format.';
                    $messageType = 'error';
                }
            }
        } else {
            $message = 'Invalid file type. Please upload .xlsx, .xls, or .csv files.';
            $messageType = 'error';
        }
    }
}

// Check if database exists
$dbExists = file_exists($dbPath . 'cmdb_database.json');
$dbRecords = 0;
if ($dbExists) {
    $dbData = json_decode(file_get_contents($dbPath . 'cmdb_database.json'), true);
    $dbRecords = count($dbData ?? []);
}

/**
 * Process Excel file to JSON using PhpSpreadsheet or basic CSV parsing
 */
function processExcelToJson($filePath) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $data = [];
    
    if ($ext === 'csv') {
        // Parse CSV
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            $headers = array_map('trim', $headers);
            
            while (($row = fgetcsv($handle)) !== false) {
                $record = [];
                foreach ($headers as $i => $header) {
                    $record[$header] = $row[$i] ?? '';
                }
                $data[] = $record;
            }
            fclose($handle);
        }
    } else {
        // For xlsx/xls, use SimpleXLSX or PhpSpreadsheet
        // Basic implementation using zip reading for xlsx
        if ($ext === 'xlsx') {
            $data = parseXlsxBasic($filePath);
        }
    }
    
    return $data;
}

function parseXlsxBasic($filePath) {
    $data = [];
    $zip = new ZipArchive();
    
    if ($zip->open($filePath) === true) {
        // Read shared strings
        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $xml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            foreach ($xml->si as $si) {
                $sharedStrings[] = (string)$si->t;
            }
        }
        
        // Read first worksheet
        if ($zip->locateName('xl/worksheets/sheet1.xml') !== false) {
            $sheetXml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
            $rows = [];
            
            foreach ($sheetXml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $value = (string)$cell->v;
                    if (isset($cell['t']) && $cell['t'] == 's' && isset($sharedStrings[(int)$value])) {
                        $value = $sharedStrings[(int)$value];
                    }
                    $rowData[] = $value;
                }
                $rows[] = $rowData;
            }
            
            if (count($rows) > 1) {
                $headers = array_shift($rows);
                foreach ($rows as $row) {
                    $record = [];
                    foreach ($headers as $i => $header) {
                        $record[$header] = $row[$i] ?? '';
                    }
                    $data[] = $record;
                }
            }
        }
        
        $zip->close();
    }
    
    return $data;
}

$nonce = bin2hex(random_bytes(16));
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Tools - NetTech Suite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/animations.css">
</head>
<body class="dashboard-body">
    <div id="loadingScreen" class="loading-screen">
        <div class="loader-wrapper">
            <div class="loader-ring"></div>
            <div class="loader-text">Loading Main Tools...</div>
        </div>
    </div>
    
    <?php include 'sidebar_include.php'; ?>
    
    <main class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <h2 class="page-title"><i class="ri-database-2-line"></i> Main Tools - CMDB Database</h2>
            </div>
            <div class="top-bar-right">
                <div class="top-clock"><span id="clockDisplay">--:--:--</span></div>
                <button class="theme-toggle-btn"><i class="ri-sun-line"></i><i class="ri-moon-line"></i></button>
            </div>
        </header>
        
        <div class="content-area">
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> fade-in">
                <i class="ri-<?php echo $messageType === 'success' ? 'check' : 'error'; ?>-line"></i>
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Database Status Card -->
            <div class="db-status-card glass-morphism fade-in">
                <div class="db-status-icon">
                    <i class="ri-database-2-line"></i>
                </div>
                <div class="db-status-info">
                    <h3>CMDB Database Status</h3>
                    <p>
                        <span class="status-indicator <?php echo $dbExists ? 'active' : 'inactive'; ?>"></span>
                        <?php echo $dbExists ? "Database Active - $dbRecords Records" : 'No Database Found'; ?>
                    </p>
                    <?php if (!$dbExists): ?>
                    <div class="no-db-warning">
                        <i class="ri-alert-line"></i>
                        Please upload your CMDB Excel file to initialize the database.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Upload Section -->
            <div class="upload-section glass-morphism fade-in-delayed">
                <h3><i class="ri-upload-cloud-2-line"></i> Upload CMDB Database</h3>
                <p>Upload your Excel file (.xlsx, .xls, .csv) containing site data with the standard headers.</p>
                
                <form method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                    <div class="file-drop-zone" id="dropZone">
                        <input type="file" name="cmdb_file" id="fileInput" 
                               accept=".xlsx,.xls,.csv" class="file-input-hidden">
                        <div class="drop-content">
                            <i class="ri-file-excel-2-line"></i>
                            <p>Drag & drop your Excel file here</p>
                            <span>or</span>
                            <button type="button" class="btn-secondary" id="browseBtn">
                                Browse Files
                            </button>
                            <small>Supported: .xlsx, .xls, .csv (No size limit)</small>
                        </div>
                        <div class="file-preview" id="filePreview" style="display:none;">
                            <i class="ri-file-excel-2-line"></i>
                            <span id="fileName"></span>
                            <span id="fileSize"></span>
                            <button type="button" class="btn-remove" id="removeFile">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary" id="uploadBtn">
                        <i class="ri-upload-line"></i> Process & Upload Database
                    </button>
                </form>
                
                <!-- Upload Progress -->
                <div class="upload-progress" id="uploadProgress" style="display:none;">
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar" id="progressBar"></div>
                    </div>
                    <span class="progress-text" id="progressText">Processing...</span>
                </div>
            </div>
            
            <!-- Database Preview -->
            <?php if ($dbExists && $dbRecords > 0): ?>
            <div class="db-preview glass-morphism fade-in-delayed-2">
                <h3><i class="ri-table-line"></i> Database Preview (First 10 Records)</h3>
                <div class="table-scroll">
                    <table class="data-table compact">
                        <thead>
                            <tr>
                                <th>Site ID</th>
                                <th>Site Name</th>
                                <th>Region</th>
                                <th>Area</th>
                                <th>Circle ID</th>
                                <th>MC Cluster</th>
                                <th>Longitude</th>
                                <th>Latitude</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $previewData = array_slice($dbData ?? [], 0, 10);
                            foreach ($previewData as $row): 
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Site ID*'] ?? $row['Site ID'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['Site Name*'] ?? $row['Site Name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['Region(Site)*'] ?? $row['Region'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['Area'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['Circle ID'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['MC Cluster'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['Longitude'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['Latitude'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($dbRecords > 10): ?>
                <p class="text-muted">Showing 10 of <?php echo $dbRecords; ?> total records</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script src="assets/js/theme.js"></script>
    <script>
        // File upload handling
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const browseBtn = document.getElementById('browseBtn');
        const filePreview = document.getElementById('filePreview');
        const dropContent = dropZone.querySelector('.drop-content');
        
        browseBtn.addEventListener('click', () => fileInput.click());
        
        fileInput.addEventListener('change', handleFileSelect);
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect();
            }
        });
        
        function handleFileSelect() {
            const file = fileInput.files[0];
            if (file) {
                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = formatFileSize(file.size);
                dropContent.style.display = 'none';
                filePreview.style.display = 'flex';
            }
        }
        
        document.getElementById('removeFile').addEventListener('click', () => {
            fileInput.value = '';
            dropContent.style.display = 'flex';
            filePreview.style.display = 'none';
        });
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Clock
        function updateClock() {
            document.getElementById('clockDisplay').textContent = 
                new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        }
        setInterval(updateClock, 1000);
        updateClock();
        
        // Loading
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loadingScreen').classList.add('loaded');
                setTimeout(() => document.getElementById('loadingScreen').remove(), 500);
            }, 600);
        });
    </script>
</body>
</html>