<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-07
// Part of: SPED LMS — Availability Calendar View

$pageTitle = 'My Availability - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

$monthName = date('F', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
$prevMonth = $currentMonth - 1;
$prevYear = $currentYear;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

$nextMonth = $currentMonth + 1;
$nextYear = $currentYear;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-calendar-check text-primary"></i> 
        My Availability Calendar
    </h1>

    <p class="text-muted mb-4">
        Set your availability so the system can suggest meeting dates when all participants are available.
    </p>

    <!-- Weekly Schedule Panel -->
    <div class="card mb-4">
        <div class="card-header" style="background-color: #1e4072; color: white;">
            <h5 class="mb-0">
                <i class="bi bi-calendar-week"></i> Set Weekly Schedule
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Select the days of the week you are regularly available for IEP meetings.
            </p>
            
            <form id="weeklyScheduleForm">
                <div class="row">
                    <?php
                    $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    foreach ($daysOfWeek as $index => $dayName):
                        $isChecked = isset($recurringAvailability[$index]) && $recurringAvailability[$index];
                    ?>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="days[]" value="<?php echo $index; ?>" 
                                       id="day<?php echo $index; ?>"
                                       <?php echo $isChecked ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="day<?php echo $index; ?>">
                                    <strong><?php echo $dayName; ?></strong>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="btn btn-primary mt-2">
                    <i class="bi bi-save"></i> Save Weekly Schedule
                </button>
            </form>
        </div>
    </div>

    <!-- Monthly Calendar -->
    <div class="card">
        <div class="card-header" style="background-color: #1e4072; color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-calendar3"></i> 
                    <?php echo $monthName . ' ' . $currentYear; ?>
                </h5>
                <div>
                    <a href="?year=<?php echo $prevYear; ?>&month=<?php echo $prevMonth; ?>" 
                       class="btn btn-sm btn-light">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                    <a href="?year=<?php echo date('Y'); ?>&month=<?php echo date('m'); ?>" 
                       class="btn btn-sm btn-light">
                        Today
                    </a>
                    <a href="?year=<?php echo $nextYear; ?>&month=<?php echo $nextMonth; ?>" 
                       class="btn btn-sm btn-light">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Legend -->
            <div class="p-3 border-bottom">
                <div class="row text-center small">
                    <div class="col">
                        <span class="badge" style="background-color: #1e4072;">Available</span>
                    </div>
                    <div class="col">
                        <span class="badge bg-secondary">Unavailable</span>
                    </div>
                    <div class="col">
                        <span class="badge" style="background-color: #a01422;">
                            <i class="bi bi-circle-fill" style="font-size: 8px;"></i> Exception
                        </span>
                    </div>
                    <div class="col">
                        <span class="badge" style="background-color: #ffc107; color: #000;">Today</span>
                    </div>
                </div>
            </div>

            <!-- Calendar Grid -->
            <table class="table table-bordered mb-0 calendar-table">
                <thead>
                    <tr>
                        <th class="text-center">Sun</th>
                        <th class="text-center">Mon</th>
                        <th class="text-center">Tue</th>
                        <th class="text-center">Wed</th>
                        <th class="text-center">Thu</th>
                        <th class="text-center">Fri</th>
                        <th class="text-center">Sat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($calendarData as $week): ?>
                        <tr>
                            <?php foreach ($week as $dayData): ?>
                                <?php if ($dayData === null): ?>
                                    <td class="calendar-day empty"></td>
                                <?php else: ?>
                                    <td class="calendar-day <?php echo $dayData['is_available'] ? 'available' : 'unavailable'; ?> 
                                               <?php echo $dayData['is_today'] ? 'today' : ''; ?>"
                                        data-date="<?php echo $dayData['date']; ?>"
                                        onclick="toggleDate('<?php echo $dayData['date']; ?>', <?php echo $dayData['is_available'] ? 'false' : 'true'; ?>)">
                                        <div class="day-number"><?php echo $dayData['day']; ?></div>
                                        <?php if ($dayData['is_exception']): ?>
                                            <div class="exception-indicator">
                                                <i class="bi bi-circle-fill"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle"></i> 
        <strong>How it works:</strong>
        <ul class="mb-0 mt-2">
            <li>Set your regular weekly schedule above (e.g., available every Monday, Wednesday, Friday)</li>
            <li>Click any date in the calendar to create an exception (override your regular schedule for that specific date)</li>
            <li>Exception dates are marked with a crimson dot</li>
            <li>The system will suggest meeting dates when SPED Teacher, Guidance, and Principal are all available</li>
        </ul>
    </div>
</div>

<style>
.calendar-table {
    table-layout: fixed;
}
.calendar-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    padding: 12px;
}
.calendar-day {
    height: 80px;
    vertical-align: top;
    padding: 8px;
    cursor: pointer;
    position: relative;
    transition: all 0.2s;
}
.calendar-day.empty {
    background-color: #f8f9fa;
    cursor: default;
}
.calendar-day.available {
    background-color: #e7f1ff;
    border-left: 3px solid #1e4072;
}
.calendar-day.unavailable {
    background-color: #f8f9fa;
}
.calendar-day.today {
    border: 2px solid #ffc107;
}
.calendar-day:not(.empty):hover {
    background-color: #d4e9ff;
    transform: scale(1.02);
}
.day-number {
    font-weight: 600;
    font-size: 16px;
}
.exception-indicator {
    position: absolute;
    top: 5px;
    right: 5px;
    color: #a01422;
    font-size: 10px;
}
.form-check-input:checked {
    background-color: #1e4072;
    border-color: #1e4072;
}
</style>

<script>
// Save weekly schedule
document.getElementById('weeklyScheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?php echo $basePath; ?>/iep/availability/save-recurring', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Weekly schedule saved successfully! Refreshing calendar...');
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving weekly schedule');
    });
});

// Toggle exception date
function toggleDate(date, makeAvailable) {
    if (!confirm('Toggle availability for ' + date + '?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('date', date);
    formData.append('is_available', makeAvailable ? '1' : '0');
    
    fetch('<?php echo $basePath; ?>/iep/availability/toggle-exception', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error toggling date');
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
