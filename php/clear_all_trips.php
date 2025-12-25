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
