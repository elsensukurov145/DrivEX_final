<?php
session_start();
require_once "php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$page = $_GET['page'] ?? 'dashboard';

$roadTypes = $conn->query("SELECT * FROM RoadType")->fetchAll();
$visibilities = $conn->query("SELECT * FROM Visibility")->fetchAll();
$weathers = $conn->query("SELECT * FROM WeatherCondition")->fetchAll();
$trafficConditions = $conn->query("SELECT * FROM TrafficCondition")->fetchAll();

$tripsStmt = $conn->prepare("
    SELECT 
        ds.session_id, ds.start_date, ds.end_date, ds.mileage,
        wc.weather_condition, 
        v.visibility,
        GROUP_CONCAT(DISTINCT rt.road_type SEPARATOR ', ') as road_types,
        GROUP_CONCAT(DISTINCT tc.traffic_condition SEPARATOR ', ') as traffic_conditions
    FROM DrivingSession ds
    JOIN WeatherCondition wc ON ds.weather_condition_id = wc.weather_condition_id
    JOIN Visibility v ON ds.visibility_id = v.visibility_id
    LEFT JOIN OccursOn oo ON ds.session_id = oo.session_id
    LEFT JOIN RoadType rt ON oo.road_type_id = rt.road_type_id
    LEFT JOIN TakesPlace tp ON ds.session_id = tp.session_id
    LEFT JOIN TrafficCondition tc ON tp.traffic_condition_id = tc.traffic_condition_id
    WHERE ds.user_id = ? 
    GROUP BY ds.session_id
    ORDER BY ds.start_date ASC
");
$tripsStmt->execute([$userId]);
$trips = $tripsStmt->fetchAll();

$weatherStats = [];
$roadStats = [];
$dateStats = [];
$visStats = [];
$trafficStats = [];

foreach ($trips as $trip) {

    $w = $trip['weather_condition'];
    $weatherStats[$w] = ($weatherStats[$w] ?? 0) + 1;

    $rList = $trip['road_types'] ? explode(', ', $trip['road_types']) : ['Unknown'];
    foreach ($rList as $r) {
        $roadStats[$r] = ($roadStats[$r] ?? 0) + 1;
    }

    $date = date('Y-m-d', strtotime($trip['start_date']));
    if (!isset($dateStats[$date])) $dateStats[$date] = 0;
    $dateStats[$date] += $trip['mileage'];

    $v = $trip['visibility'];
    $visStats[$v] = ($visStats[$v] ?? 0) + 1;

    $tList = $trip['traffic_conditions'] ? explode(', ', $trip['traffic_conditions']) : ['Unknown'];
    foreach ($tList as $t) {
        $trafficStats[$t] = ($trafficStats[$t] ?? 0) + 1;
    }
}

$weatherLabels = json_encode(array_keys($weatherStats));
$weatherData = json_encode(array_values($weatherStats));

$roadLabels = json_encode(array_keys($roadStats));
$roadData = json_encode(array_values($roadStats));

$visLabels = json_encode(array_keys($visStats ?? []));
$visData = json_encode(array_values($visStats ?? []));

$trafficLabels = json_encode(array_keys($trafficStats ?? []));
$trafficData = json_encode(array_values($trafficStats ?? []));

$lineLabels = [];
$lineData = [];
$cumul = 0;
foreach ($dateStats as $date => $miles) {
    $cumul += $miles;
    $lineLabels[] = $date;
    $lineData[] = $cumul;
}
$lineLabelsJson = json_encode($lineLabels);
$lineDataJson = json_encode($lineData);

$totalDistance = array_sum(array_column($trips, 'mileage'));
$totalTrips = count($trips);

$userStmt = $conn->prepare("SELECT email, created_at FROM Users WHERE user_id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();
$userEmail = $user['email'] ?? 'user@example.com';
$memberSince = $user['created_at'] ? date('M Y', strtotime($user['created_at'])) : 'Jan 2025';

$totalDurationSeconds = 0;
foreach ($trips as $t) {
    $start = strtotime($t['start_date']);
    $end = strtotime($t['end_date']);
    $totalDurationSeconds += ($end - $start);
}
$totalHours = $totalDurationSeconds > 0 ? $totalDurationSeconds / 3600 : 0;
$avgSpeed = $totalHours > 0 ? $totalDistance / $totalHours : 0;

/* Fuel saved tam silinir — lazım deyil */
// $fuelSaved = $totalDistance * 0.12;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DrivEx Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- cache qırmaq üçün v=5 -->
    <link rel="stylesheet" href="css/style.css?v=5">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- ionicons qalır (chart/table üçün lazım ola bilər), amma ikonları HTML-dən sildik -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="d-flex" id="wrapper">

    <!-- SIDEBAR -->
    <div id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4 primary-text fs-4 fw-bold text-uppercase border-bottom">
            <span class="ms-2 brand-text">DRIVEX</span>
        </div>

        <div class="list-group list-group-flush my-3">
            <!-- LOGOLAR (ion-icon) silindi -->
            <a href="?page=dashboard" class="list-group-item list-group-item-action bg-transparent second-text <?php echo $page=='dashboard'?'active':''; ?>">
                <span class="link-text">Dashboard</span>
            </a>

            <a href="?page=add_trip" class="list-group-item list-group-item-action bg-transparent second-text <?php echo $page=='add_trip'?'active':''; ?>">
                <span class="link-text">Add Trip</span>
            </a>

            <a href="php/logout.php" class="list-group-item list-group-item-action bg-transparent text-danger fw-bold mt-auto">
                <span class="link-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- PAGE CONTENT -->
    <div id="page-content-wrapper">

        <!-- TOP NAVBAR TAM SILINDI (DrivEx + Dashboard Add Trip Logout yazısı yoxdu artıq) -->

        <div class="container-fluid" style="padding: 20px;">

            <?php if ($page === 'dashboard'): ?>

            <div class="analytics-strip">

                <!-- Avg Speed: ikon silindi -->
                <div class="analytics-item">
                    <div>
                        <h4><?php echo number_format($avgSpeed, 1); ?> km/h</h4>
                        <span>Avg Speed</span>
                    </div>
                </div>

                <!-- Fuel Saved: TAM SILINDI -->

                <!-- Total Drive Time: ikon silindi -->
                <div class="analytics-item">
                    <div>
                        <h4><?php echo number_format($totalHours, 1); ?> h</h4>
                        <span>Total Drive Time</span>
                    </div>
                </div>

            </div>

            <div class="summary-cards">
                <div class="card stat-card">
                    <h3>Total Distance</h3>
                    <p><?php echo number_format($totalDistance, 1); ?> km</p>
                </div>
                <div class="card stat-card">
                    <h3>Total Trips</h3>
                    <p><?php echo $totalTrips; ?></p>
                </div>

            </div>

            <div class="charts-container">
                <div class="chart-wrapper"><canvas id="weatherChart"></canvas></div>
                <div class="chart-wrapper"><canvas id="roadChart"></canvas></div>
                <div class="chart-wrapper"><canvas id="visibilityChart"></canvas></div>
                <div class="chart-wrapper"><canvas id="trafficChart"></canvas></div>
                <div class="chart-wrapper wide"><canvas id="experiencesChart"></canvas></div>
            </div>

            <div class="table-container">
    <div class="history-header">
        <h2>Driving History</h2>

        <form action="php/clear_all_trips.php" method="POST"
              onsubmit="return confirm('Are you sure you want to delete ALL driving sessions? This cannot be undone.');">
            <input type="hidden" name="redirect_page" value="dashboard">
            <button type="submit" class="btn btn-danger btn-sm">Clear All</button>
        </form>
    </div>

    <table id="experiencesTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Date</th>
                <th>Mileage</th>
                <th>Visibility</th>
                <th>Weather</th>
                <th>Traffic</th>
                <th>Road Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach(array_reverse($trips) as $trip): ?>
            <tr>
                <td><?php echo date('M d, Y', strtotime($trip['start_date'])); ?></td>
                <td><?php echo number_format($trip['mileage'], 1); ?> km</td>
                <td><?php echo $trip['visibility']; ?></td>
                <td><?php echo $trip['weather_condition']; ?></td>
                <td><?php echo $trip['traffic_conditions'] ?? 'None'; ?></td>
                <td><?php echo $trip['road_types'] ?? 'None'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    initCharts(
                        <?php echo $weatherLabels; ?>,
                        <?php echo $weatherData; ?>,
                        <?php echo $roadLabels; ?>,
                        <?php echo $roadData; ?>,
                        <?php echo $lineLabelsJson; ?>,
                        <?php echo $lineDataJson; ?>,
                        <?php echo $visLabels; ?>,
                        <?php echo $visData; ?>,
                        <?php echo $trafficLabels; ?>,
                        <?php echo $trafficData; ?>
                    );
                });
            </script>

            <?php elseif ($page === 'add_trip'): ?>

            <div class="card">
                <h2>New Driving Session</h2>

                <div class="tabs">
                    <button class="tab-btn active" onclick="showTab('manual')">Manual Entry</button>
                    <button class="tab-btn" onclick="showTab('live')">Live Tracking</button>
                </div>

                <div id="tab-manual" class="tab-content">
                    <form action="php/save_trip.php" method="POST" onsubmit="return validateManualForm()">
                        <input type="hidden" name="trip_type" value="manual">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">

                        <div class="form-row">
                            <div class="form-group col-half"><label>Start Time</label><input type="datetime-local" name="start_time" class="form-control" required></div>
                            <div class="form-group col-half"><label>End Time</label><input type="datetime-local" name="end_time" class="form-control" required></div>
                        </div>

                        <div class="form-group"><label>Distance (km)</label><input type="number" step="0.1" name="distance" class="form-control" required></div>

                        <div class="form-row">
                            <div class="form-group col-half">
                                <label>Weather</label>
                                <select name="weather" class="form-control" required>
                                    <?php foreach($weathers as $w) echo "<option value='{$w['weather_condition_id']}'>{$w['weather_condition']}</option>"; ?>
                                </select>
                            </div>
                            <div class="form-group col-half">
                                <label>Visibility</label>
                                <select name="visibility" class="form-control" required>
                                    <?php foreach($visibilities as $v) echo "<option value='{$v['visibility_id']}'>{$v['visibility']}</option>"; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-half">
                                <label>Road Type</label>
                                <div class="checkbox-group">
                                    <?php foreach($roadTypes as $r): ?>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="road_type[]" value="<?php echo $r['road_type_id']; ?>">
                                            <?php echo $r['road_type']; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="form-group col-half">
                                <label>Traffic</label>
                                <div class="checkbox-group">
                                    <?php foreach($trafficConditions as $t): ?>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="traffic[]" value="<?php echo $t['traffic_condition_id']; ?>">
                                            <?php echo $t['traffic_condition']; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div id="manual-error-msg" class="text-danger text-center fw-bold mb-3" style="display:none;"></div>
                        <button type="submit" class="btn btn-primary full-width">Save Manual Trip</button>
                    </form>
                </div>

                <div id="tab-live" class="tab-content" style="display:none;">
                    <div class="tracker-ui">
                        <div class="tracker-status">
                            <h1 id="distance-display">0.00 km</h1>
                            <p id="status-display">Ready to track</p>
                        </div>
                        <div class="tracker-controls">
                            <button id="start-btn" type="button" class="btn btn-primary">Start Tracking</button>
                            <button id="stop-btn" type="button" class="btn btn-danger" style="display:none;">Stop & Save</button>
                        </div>
                    </div>

                    <form action="php/save_trip.php" method="POST" id="live-form" style="margin-top:20px;">
                        <input type="hidden" name="trip_type" value="live">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        <input type="hidden" id="route-points" name="route_points">
                        <input type="hidden" id="distance-input" name="distance">
                        <input type="hidden" id="start-time" name="start_time">
                        <input type="hidden" id="end-time" name="end_time">

                        <div class="form-row">
                            <div class="form-group col-half">
                                <label>Weather</label>
                                <select name="weather" class="form-control" required>
                                    <?php foreach($weathers as $w) echo "<option value='{$w['weather_condition_id']}'>{$w['weather_condition']}</option>"; ?>
                                </select>
                            </div>
                            <div class="form-group col-half">
                                <label>Visibility</label>
                                <select name="visibility" class="form-control" required>
                                    <?php foreach($visibilities as $v) echo "<option value='{$v['visibility_id']}'>{$v['visibility']}</option>"; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-half">
                                <label>Road Type (Select all that apply)</label>
                                <div class="checkbox-group">
                                    <?php foreach($roadTypes as $r): ?>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="road_type[]" value="<?php echo $r['road_type_id']; ?>">
                                            <?php echo $r['road_type']; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="form-group col-half">
                                <label>Traffic (Select all that apply)</label>
                                <div class="checkbox-group">
                                    <?php foreach($trafficConditions as $t): ?>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="traffic[]" value="<?php echo $t['traffic_condition_id']; ?>">
                                            <?php echo $t['traffic_condition']; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary full-width">Save Live Trip</button>
                    </form>
                </div>

            </div>

            <script src="js/tracker.js"></script>

            <?php else: ?>
                <div class="card">
                    <h2>Unknown page</h2>
                    <p>Invalid page parameter.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="js/dashboard.js"></script>
</body>
</html>
