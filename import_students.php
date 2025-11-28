<?php
require 'includes/db.php';
include 'includes/header.php';

// Security: Only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$success = '';
$error = '';
$imported_count = 0;

// Handle CSV/Excel upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "File upload error.";
    } else {
        $mime_type = mime_content_type($file['tmp_name']);
        $allowed_types = ['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        
        // Read CSV file
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            $header = fgetcsv($handle);
            
            // Expected columns: first_name, last_name, email, student_group, password
            $expected = ['first_name', 'last_name', 'email', 'student_group', 'password'];
            
            // Check if header matches
            if (array_map('strtolower', $header) === $expected || count($header) >= 5) {
                $row_num = 1;
                while (($row = fgetcsv($handle)) !== false) {
                    if (empty($row[0])) continue; // Skip empty rows
                    
                    $first_name = trim($row[0] ?? '');
                    $last_name = trim($row[1] ?? '');
                    $email = trim($row[2] ?? '');
                    $student_group = trim($row[3] ?? 'G1');
                    $password = trim($row[4] ?? '');
                    
                    if (!$first_name || !$last_name || !$email || !$password) {
                        continue; // Skip incomplete rows
                    }
                    
                    try {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $sql = "INSERT IGNORE INTO users (first_name, last_name, email, password, role, student_group) 
                                VALUES (?, ?, ?, ?, 'student', ?)";
                        $stmt = $pdo->prepare($sql);
                        if ($stmt->execute([$first_name, $last_name, $email, $hashed_password, $student_group])) {
                            $imported_count++;
                        }
                    } catch (PDOException $e) {
                        // Silently skip duplicates
                    }
                    
                    $row_num++;
                }
                
                if ($imported_count > 0) {
                    $success = "Successfully imported $imported_count students!";
                } else {
                    $error = "No new students were imported. Check if emails already exist.";
                }
            } else {
                $error = "CSV file format incorrect. Expected columns: first_name, last_name, email, student_group, password";
            }
            
            fclose($handle);
        } else {
            $error = "Failed to read file.";
        }
    }
}
?>

<h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
    <i class="fas fa-upload"></i> Import Students from Excel/CSV
</h2>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Import Form -->
<div class="card" style="max-width: 600px;">
    <h3><i class="fas fa-file-import"></i> Upload File</h3>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="import_file">CSV or Excel File <span style="color: var(--danger-color);">*</span></label>
            <input type="file" id="import_file" name="import_file" class="form-control" accept=".csv,.xls,.xlsx" required>
            <small style="color: #666;">Supported formats: CSV, XLS, XLSX</small>
        </div>
        
        <button type="submit" class="btn btn-success btn-block">
            <i class="fas fa-upload"></i> Import Students
        </button>
    </form>
</div>

<!-- Template Instructions -->
<div class="card" style="margin-top: 2rem;">
    <h3><i class="fas fa-list"></i> File Format Template</h3>
    
    <p style="margin-bottom: 1rem;">Your CSV/Excel file should have the following columns (in this order):</p>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Column</th>
                <th>Description</th>
                <th>Example</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>first_name</strong></td>
                <td>Student's first name</td>
                <td>Ahmed</td>
            </tr>
            <tr>
                <td><strong>last_name</strong></td>
                <td>Student's last name</td>
                <td>Benali</td>
            </tr>
            <tr>
                <td><strong>email</strong></td>
                <td>Student's email (unique)</td>
                <td>ahmed.benali@univ-alger.dz</td>
            </tr>
            <tr>
                <td><strong>student_group</strong></td>
                <td>Student's group (G1, G2, G3, or G4)</td>
                <td>G1</td>
            </tr>
            <tr>
                <td><strong>password</strong></td>
                <td>Initial password (min 6 chars)</td>
                <td>Pass1234</td>
            </tr>
        </tbody>
    </table>
    
    <h4 style="margin-top: 1.5rem; margin-bottom: 0.5rem;">Sample CSV Content:</h4>
    <pre style="background: #f5f5f5; padding: 1rem; border-radius: 4px; overflow-x: auto;">first_name,last_name,email,student_group,password
Ahmed,Benali,ahmed.benali@univ-alger.dz,G1,Pass1234
Fatima,Zahra,fatima.zahra@univ-alger.dz,G1,Pass1234
Mohammed,Amin,mohammed.amin@univ-alger.dz,G2,Pass1234
Leila,Hassan,leila.hassan@univ-alger.dz,G2,Pass1234
Karim,Ali,karim.ali@univ-alger.dz,G3,Pass1234</pre>
    
    <p style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
        <i class="fas fa-info-circle"></i> <strong>Note:</strong> Duplicate emails will be skipped. Passwords should be sent separately to students for security.
    </p>
</div>

<!-- Download Sample -->
<div class="card" style="margin-top: 2rem; text-align: center;">
    <h3><i class="fas fa-download"></i> Download Template</h3>
    <p style="margin-bottom: 1rem;">Download a blank CSV template to fill with your student data:</p>
    
    <?php
    // Generate CSV download
    if (isset($_GET['download_template'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="students_import_template.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['first_name', 'last_name', 'email', 'student_group', 'password']);
        
        // Add example rows
        for ($i = 1; $i <= 5; $i++) {
            fputcsv($output, [
                "Student$i",
                "Name$i",
                "student$i@univ-alger.dz",
                "G" . (($i % 4) + 1),
                "Pass1234"
            ]);
        }
        
        fclose($output);
        exit;
    }
    ?>
    
    <a href="?download_template=true" class="btn btn-success">
        <i class="fas fa-download"></i> Download CSV Template
    </a>
</div>

<?php include 'includes/footer.php'; ?>
