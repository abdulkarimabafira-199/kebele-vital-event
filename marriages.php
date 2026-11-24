<?php
// marriages.php - Marriage Certificate Registration & CRUD
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();
$error = '';
$success = '';

// Handle DELETE
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM marriage_certificates WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $success = "Marriage certificate deleted.";
}

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Husband
    $h_first = trim($_POST['h_first_name']);
    $h_father = trim($_POST['h_father_name']);
    $h_grandfather = trim($_POST['h_grandfather_name']);
    $h_sex = 'Male';
    $h_dob = $_POST['h_date_of_birth'];
    $h_pob = trim($_POST['h_place_of_birth']);
    $h_nationality = trim($_POST['h_nationality']);

    // Wife
    $w_first = trim($_POST['w_first_name']);
    $w_father = trim($_POST['w_father_name']);
    $w_grandfather = trim($_POST['w_grandfather_name']);
    $w_sex = 'Female';
    $w_dob = $_POST['w_date_of_birth'];
    $w_pob = trim($_POST['w_place_of_birth']);
    $w_nationality = trim($_POST['w_nationality']);

    // Marriage Details
    $place_of_marriage = trim($_POST['place_of_marriage']);
    $date_of_marriage = $_POST['date_of_marriage'];

    $cert_num = "MR-" . rand(1000, 9999) . "-" . date('Y');
    $registrar_id = $_SESSION['user_id'];
    $registered_date = date('Y-m-d');

    try {
        $conn->beginTransaction();

        // Insert Husband
        $stmt = $conn->prepare("INSERT INTO persons (first_name, father_name, grandfather_name, sex, date_of_birth, place_of_birth, nationality, marital_status) VALUES (?,?,?,?,?,?,?,'Married')");
        $stmt->execute([$h_first, $h_father, $h_grandfather, $h_sex, $h_dob, $h_pob, $h_nationality]);
        $husband_id = $conn->lastInsertId();

        // Insert Wife
        $stmt->execute([$w_first, $w_father, $w_grandfather, $w_sex, $w_dob, $w_pob, $w_nationality]);
        $wife_id = $conn->lastInsertId();

        // Insert Marriage Certificate
        $stmt = $conn->prepare("INSERT INTO marriage_certificates (certificate_number, husband_id, wife_id, place_of_marriage, date_of_marriage, registrar_id, registered_date) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$cert_num, $husband_id, $wife_id, $place_of_marriage, $date_of_marriage, $registrar_id, $registered_date]);
        $cert_id = $conn->lastInsertId();

        $conn->commit();
        header("Location: print_marriage.php?id=" . $cert_id);
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Failed: " . $e->getMessage();
    }
}

// Fetch all
$certs = $conn->query("SELECT m.*, 
    h.first_name as h_first, h.father_name as h_father, h.grandfather_name as h_grand,
    w.first_name as w_first, w.father_name as w_father, w.grandfather_name as w_grand
    FROM marriage_certificates m 
    JOIN persons h ON m.husband_id = h.id 
    JOIN persons w ON m.wife_id = w.id 
    ORDER BY m.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="marriageDocumentTitle">Marriages - Kebele Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .section-title { grid-column: 1 / -1; margin: 15px 0 5px; border-bottom: 2px solid var(--primary-color); padding-bottom: 5px; color: var(--primary-color); }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .btn-sm { padding: 5px 10px; font-size: 0.85em; width: auto; display: inline-block; }
        .btn-danger { background: var(--danger-color); color: #fff; }
        .btn-info { background: #5bc0de; color: #fff; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <h2 data-i18n="marriagePageTitle">Marriage Certificate Registration</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="btn btn-primary btn-sm" style="margin-left:10px;"><i class="fas fa-sign-out-alt"></i></a>
                <select id="language-selector" class="btn btn-primary btn-sm" style="margin-left:10px; padding: 6px 12px; width: auto; display: inline-block; font-size: 0.85em;">
                    <option value="en" data-i18n-option="langEnglish">English</option>
                    <option value="amh" data-i18n-option="langAmharic">አማርኛ</option>
                </select>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>

            <div class="card-table" style="margin-bottom:25px;">
                <h3 data-i18n="marriageRegisterTitle">Register Marriage</h3>
                <form method="POST">
                    <div class="form-grid">
                        <!-- HUSBAND -->
                        <div class="section-title" data-i18n="marriageHusbandSection">🤵 Husband's Details</div>
                        <div class="form-group">
                            <label data-i18n="marriageLabelHusbandFirstName">Husband's First Name</label>
                            <input type="text" name="h_first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="marriageLabelHusbandFatherName">Husband's Father's Name</label>
                            <input type="text" name="h_father_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="marriageLabelHusbandGrandfatherName">Husband's Grandfather's Name</label>
                            <input type="text" name="h_grandfather_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="labelDateOfBirth">Date of Birth</label>
                            <input type="date" name="h_date_of_birth" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="labelPlaceOfBirth">Place of Birth</label>
                            <input type="text" name="h_place_of_birth" class="form-control">
                        </div>
                        <div class="form-group">
                            <label data-i18n="labelNationality">Nationality</label>
                            <input type="text" name="h_nationality" class="form-control" value="Ethiopian">
                        </div>

                        <!-- WIFE -->
                        <div class="section-title" data-i18n="marriageWifeSection">👰 Wife's Details</div>
                        <div class="form-group">
                            <label data-i18n="marriageLabelWifeFirstName">Wife's First Name</label>
                            <input type="text" name="w_first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="marriageLabelWifeFatherName">Wife's Father's Name</label>
                            <input type="text" name="w_father_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="marriageLabelWifeGrandfatherName">Wife's Grandfather's Name</label>
                            <input type="text" name="w_grandfather_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="labelDateOfBirth">Date of Birth</label>
                            <input type="date" name="w_date_of_birth" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="labelPlaceOfBirth">Place of Birth</label>
                            <input type="text" name="w_place_of_birth" class="form-control">
                        </div>
                        <div class="form-group">
                            <label data-i18n="labelNationality">Nationality</label>
                            <input type="text" name="w_nationality" class="form-control" value="Ethiopian">
                        </div>

                        <!-- Marriage Info -->
                        <div class="section-title" data-i18n="marriageDetailsSection">💍 Marriage Details</div>
                        <div class="form-group">
                            <label data-i18n="marriageLabelDateOfMarriage">Date of Marriage</label>
                            <input type="date" name="date_of_marriage" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label data-i18n="marriageLabelPlaceOfMarriage">Place of Marriage</label>
                            <input type="text" name="place_of_marriage" class="form-control" required>
                        </div>

                        <div class="form-group" style="grid-column:1/-1; margin-top:15px;">
                            <button type="submit" class="btn btn-primary" data-i18n="marriageButtonRegister"><i class="fas fa-print"></i> Register & Print Certificate</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="card-table">
                <h3 data-i18n="marriagePageTitle">Marriage Certificates (<?php echo count($certs); ?>)</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th data-i18n="marriageTableCertNo">Cert #</th>
                                <th data-i18n="marriageTableHusband">Husband</th>
                                <th data-i18n="marriageTableWife">Wife</th>
                                <th data-i18n="marriageTableDateOfMarriage">Date of Marriage</th>
                                <th data-i18n="marriageTableActions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(count($certs) > 0): foreach($certs as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['certificate_number']); ?></td>
                                <td><?php echo htmlspecialchars($c['h_first'].' '.$c['h_father'].' '.$c['h_grand']); ?></td>
                                <td><?php echo htmlspecialchars($c['w_first'].' '.$c['w_father'].' '.$c['w_grand']); ?></td>
                                <td><?php echo $c['date_of_marriage']; ?></td>
                                <td>
                                    <a href="print_marriage.php?id=<?php echo $c['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-print"></i></a>
                                    <a href="marriages.php?delete=<?php echo $c['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm(translations[LanguageSwitcher.getCurrentLanguage()]?.marriageDeleteConfirm || 'Delete this certificate?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;" data-i18n="marriageNoRecords">No marriage certificates found.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/js/language.js"></script>
    <script>
        // Helper to access translations from language.js
        const translations = window.LanguageSwitcher?.translations || {
            en: <?php echo json_encode($translations_en ?? []); ?>,
            amh: <?php echo json_encode($translations_amh ?? []); ?>
        };
        document.addEventListener('DOMContentLoaded', function() {
            if (window.LanguageSwitcher && typeof window.LanguageSwitcher.init === 'function') {
                window.LanguageSwitcher.init('en');
            }
        });
    </script>
</body>
</html>