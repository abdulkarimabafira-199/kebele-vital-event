<?php
// dashboard.php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Fetch basic stats
function getCount($conn, $table) {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM $table");
    return $stmt->fetch()['total'];
}

$total_persons = getCount($conn, 'persons');
$total_births = getCount($conn, 'birth_certificates');
$total_deaths = getCount($conn, 'death_certificates');
$total_marriages = getCount($conn, 'marriage_certificates');
$total_divorces = getCount($conn, 'divorce_certificates');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="dashboardDocumentTitle">Dashboard - Bekke Agalo Kebele</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card a { text-decoration: none; color: inherit; display: block; }
        .stat-card { cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .stat-card .view-link { font-size: 0.8em; color: var(--primary-color); margin-top: 8px; display: block; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <h2 data-i18n="dashboardOverview">Dashboard Overview</h2>
            <div class="user-info">
                <span><span data-i18n="welcomeText">Welcome</span>, <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                <a href="logout.php" class="btn btn-primary" style="padding: 6px 16px; margin-left: 25px; width: auto; display: inline-block; font-size: 0.85em;"><i class="fas fa-sign-out-alt"></i> <span data-i18n="logoutBtn">Logout</span></a>
                <select id="language-selector" class="btn btn-primary" style="margin-left: 15px; padding: 6px 12px; width: auto; display: inline-block; font-size: 0.85em;">
                    <option value="en" data-i18n-option="langEnglish">English</option>
                    <option value="amh" data-i18n-option="langAmharic">አማርኛ</option>
                </select>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="stats-grid">
                <div class="stat-card">
                    <a href="persons.php">
                        <h3><i class="fas fa-users"></i> <span data-i18n="dashboardMembersTitle">Total Kebele Members</span></h3>
                        <div class="value"><?php echo number_format($total_persons); ?></div>
                        <span class="view-link" data-i18n="dashboardViewMembers">View all members →</span>
                    </a>
                </div>
                <div class="stat-card">
                    <a href="births.php">
                        <h3><i class="fas fa-baby"></i> <span data-i18n="dashboardBirthsTitle">Birth Certificates</span></h3>
                        <div class="value"><?php echo number_format($total_births); ?></div>
                        <span class="view-link" data-i18n="dashboardViewBirths">View all births →</span>
                    </a>
                </div>
                <div class="stat-card">
                    <a href="deaths.php">
                        <h3><i class="fas fa-cross"></i> <span data-i18n="dashboardDeathsTitle">Death Certificates</span></h3>
                        <div class="value"><?php echo number_format($total_deaths); ?></div>
                        <span class="view-link" data-i18n="dashboardViewDeaths">View all deaths →</span>
                    </a>
                </div>
                <div class="stat-card">
                    <a href="marriages.php">
                        <h3><i class="fas fa-ring"></i> <span data-i18n="dashboardMarriagesTitle">Marriage Certificates</span></h3>
                        <div class="value"><?php echo number_format($total_marriages); ?></div>
                        <span class="view-link" data-i18n="dashboardViewMarriages">View all marriages →</span>
                    </a>
                </div>
                <div class="stat-card">
                    <a href="divorces.php">
                        <h3><i class="fas fa-file-contract"></i> <span data-i18n="dashboardDivorcesTitle">Divorce Certificates</span></h3>
                        <div class="value"><?php echo number_format($total_divorces); ?></div>
                        <span class="view-link" data-i18n="dashboardViewDivorces">View all divorces →</span>
                    </a>
                </div>
            </div>

            <div class="card-table">
                <h3 data-i18n="dashboardRecentRecords">Recent Records</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th data-i18n="dashboardHeaderId">ID</th>
                                <th data-i18n="dashboardHeaderFullName">Full Name</th>
                                <th data-i18n="dashboardHeaderSex">Sex</th>
                                <th data-i18n="dashboardHeaderDob">Date of Birth</th>
                                <th data-i18n="dashboardHeaderDateAdded">Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = $conn->query("SELECT * FROM persons ORDER BY id DESC LIMIT 5");
                            if($recent->rowCount() > 0):
                                while($row = $recent->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['father_name'] . ' ' . $row['grandfather_name']); ?></td>
                                <td><?php echo $row['sex']; ?> <!-- Dynamic DB value, not translated --></td>
                                <td><?php echo $row['date_of_birth']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" style="text-align: center;" data-i18n="dashboardNoRecords">No records yet.</td>
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
        document.addEventListener('DOMContentLoaded', function() {
            if (window.LanguageSwitcher && typeof window.LanguageSwitcher.init === 'function') {
                window.LanguageSwitcher.init('en');
            }
        });
    </script>
</body>
</html>