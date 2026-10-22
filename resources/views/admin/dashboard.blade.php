@extends('layouts.app')

@section('title', 'Dashboard - Tech McRae')

@section('content')
<div class="dashboard-page">
    <h1 class="welcome-title">Welcome, {{ $adminName }}</h1>

    <div class="section-header">
        <h2 class="section-title">Today's Attendance</h2>
        <div class="period-toggle">
            <button class="period-btn active" data-period="today">Today</button>
            <button class="period-btn" data-period="week">Week</button>
            <button class="period-btn" data-period="month">Month</button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-darkblue"><span class="material-symbols-rounded">groups</span></div>
            <div class="stat-info">
                <div class="stat-number color-darkblue" id="stat-employees">{{ number_format($stats['employees']) }}</div>
                <div class="stat-label stat-label-period" data-base-label="Total Staff">Total Staff</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-blue"><span class="material-symbols-rounded">apartment</span></div>
            <div class="stat-info">
                <div class="stat-number color-blue" id="stat-wfo">{{ number_format($stats['wfo']) }}</div>
                <div class="stat-label stat-label-period" data-base-label="Work From Office">Work From Office</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-darkgreen"><span class="material-symbols-rounded">home</span></div>
            <div class="stat-info">
                <div class="stat-number color-darkgreen" id="stat-wfh">{{ number_format($stats['wfh']) }}</div>
                <div class="stat-label stat-label-period" data-base-label="Work From Home">Work From Home</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-red"><span class="material-symbols-rounded">person_off</span></div>
            <div class="stat-info">
                <div class="stat-number color-red" id="stat-absent">{{ number_format($stats['absent']) }}</div>
                <div class="stat-label stat-label-period" data-base-label="Absent">Absent</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-yellow"><span class="material-symbols-rounded">logout</span></div>
            <div class="stat-info">
                <div class="stat-number color-yellow" id="stat-on-leave">{{ number_format($stats['on_leave']) }}</div>
                <div class="stat-label stat-label-period" data-base-label="On Leave">On Leave</div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- ======================= Overall Attendance ======================= -->
        <div class="panel panel-attendance">
            <h3 class="panel-title">Overall Attendance</h3>
            <div class="chart-wrapper">
                <canvas id="attendanceLineChart"></canvas>
            </div>
            <div class="chart-stats">
                <div class="chart-stat">
                    <div class="chart-stat-label">AVG THIS YEAR</div>
                    <div class="chart-stat-value color-darkblue">{{ $chartData['avg'] }}%</div>
                </div>
                <div class="chart-stat">
                    <div class="chart-stat-label">PEAK MONTH</div>
                    <div class="chart-stat-value color-darkblue">{{ $chartData['peak_month'] }} · {{ $chartData['peak_value'] }}%</div>
                </div>
                <div class="chart-stat">
                    <div class="chart-stat-label">YOY CHANGE</div>
                    <div class="chart-stat-value color-darkgreen">+{{ $chartData['yoy_change'] }}%</div>
                </div>
            </div>
        </div>

        <!-- ======================= Attendance Pie Chart ======================= -->
        <div class="panel panel-pie">
            <h3 class="panel-title">Attendance Chart</h3>
            <div class="pie-wrapper">
                <canvas id="attendancePieChart"></canvas>
                <div class="pie-center">
                    <div class="pie-center-value">{{ number_format($stats['employees']) }}</div>
                    <div class="pie-center-label">TOTAL · TODAY</div>
                </div>
            </div>
            <ul class="pie-legend">
                @php
                    $total = max(array_sum($pieData['values']), 1);
                    $colors = ['blue','darkgreen','yellow','red'];
                @endphp
                @foreach($pieData['labels'] as $i => $label)
                <li>
                    <span class="legend-dot dot-{{ $colors[$i] }}"></span>
                    <span class="legend-name">{{ $label }}</span>
                    <span class="legend-value">{{ $pieData['values'][$i] }}</span>
                    <span class="legend-pct">{{ number_format(($pieData['values'][$i] / $total) * 100, 2) }}%</span>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- ======================= Notifications Panel ======================= -->
        <div class="panel panel-notifications">
            <div class="panel-header-row">
                <h3 class="panel-title">Notifications</h3>
                <a href="{{ url('/leave/requests') }}" class="view-all-pill">
                    View All Notifications
                </a>
            </div>
            
            <ul class="notification-list">
                @forelse($notifications as $note)
                <li class="notification-item">
                    {{-- Defaulting context icon style to yellow clock schedule view from mockup layout --}}
                    <div class="notification-icon">
                        <span class="material-symbols-rounded">schedule</span>
                    </div>
                    <p class="notification-text">{!! $note['html'] !!}</p>
                </li>
                @empty
                <li class="notification-item">
                    <div class="notification-icon">
                        <span class="material-symbols-rounded">info</span>
                    </div>
                    <p class="notification-text">No new notifications.</p>
                </li>
                @endforelse
            </ul>

            <a href="{{ url('/portal') }}" class="portal-footer-btn">
                Go to Employee Portal
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ======================= Line Chart =======================
    const lineEl = document.getElementById('attendanceLineChart');
    if (lineEl) {
        const lineCtx = lineEl.getContext('2d');
        const gradient = lineCtx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(191, 70, 70, 0.35)');
        gradient.addColorStop(1, 'rgba(191, 70, 70, 0)');

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [
                    {
                        label: 'This Year',
                        data: @json($chartData['this_year']),
                        borderColor: '#BF4646',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#BF4646',
                        pointBorderColor: '#FFF4EA',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Last Year',
                        data: @json($chartData['last_year']),
                        borderColor: '#151A2D',
                        borderDash: [5, 5],
                        borderWidth: 1.5,
                        fill: false,
                        tension: 0.35,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 100,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: (v) => v + '%',
                            color: '#151A2D',
                            font: { family: 'Poppins', size: 10 }
                        },
                        grid: { color: 'rgba(21, 26, 45, 0.08)' }
                    },
                    x: {
                        ticks: { color: '#151A2D', font: { family: 'Poppins', size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ======================= Pie / Donut Chart =======================
    const pieEl = document.getElementById('attendancePieChart');
    if (pieEl) {
        new Chart(pieEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json($pieData['labels']),
                datasets: [{
                    data: @json($pieData['values']),
                    backgroundColor: ['#7EACB5','#5B7E3C','#E8B33D','#BF4646'],
                    borderColor: '#FFF4EA',
                    borderWidth: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 100,
                cutout: '68%',
                plugins: { legend: { display: false } }
            }
        });
    }

    // ======================= Period toggle with AJAX =======================
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            // Remove active style state from other buttons and assign to clicked button
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const period = btn.dataset.period;

            // Update Header and Donut text visually
            const pageTitle = document.querySelector('.section-header .section-title');
            const donutCenterTitle = document.querySelector('.panel-pie .pie-center-label');
            
            if (pageTitle) {
                if (period === 'today') pageTitle.innerText = "Today's Attendance";
                else if (period === 'week') pageTitle.innerText = "This Week's Attendance";
                else if (period === 'month') pageTitle.innerText = "This Month's Attendance";
            }
            if (donutCenterTitle) {
                donutCenterTitle.innerText = `TOTAL · ${period.toUpperCase()}`;
            }

            // Fetch structural analytics data on target time limits from controller
            try {
                const response = await fetch(`${window.location.pathname}?period=${period}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Network response failure');
                const data = await response.json();

                // Update numerical values in DOM containers
                document.getElementById('stat-employees').innerText = Number(data.stats.employees).toLocaleString();
                document.getElementById('stat-wfo').innerText = Number(data.stats.wfo).toLocaleString();
                document.getElementById('stat-wfh').innerText = Number(data.stats.wfh).toLocaleString();
                document.getElementById('stat-absent').innerText = Number(data.stats.absent).toLocaleString();
                document.getElementById('stat-on-leave').innerText = Number(data.stats.on_leave).toLocaleString();

                // Change descriptive bottom label text strings
                const labelMapping = { today: 'Today', week: 'This Week', month: 'This Month' };
                document.querySelectorAll('.stat-label-period').forEach(label => {
                    const baseText = label.getAttribute('data-base-label');
                    label.innerText = `${baseText} (${labelMapping[period]})`;
                });

                // Hot-swap data array values inside ChartJS reference instances
                const pieChartInstance = Chart.getChart('attendancePieChart');
                if (pieChartInstance) {
                    pieChartInstance.data.datasets[0].data = data.pieData.values;
                    pieChartInstance.update();
                }

                // Render dynamic text modifications within the pie chart's list element legend
                const legendContainer = document.querySelector('.pie-legend');
                if (legendContainer && data.pieData.html_legend) {
                    legendContainer.innerHTML = data.pieData.html_legend;
                }

                // Update center total display counter
                const donutCenterValue = document.querySelector('.pie-center-value');
                if (donutCenterValue) {
                    donutCenterValue.innerText = Number(data.stats.employees).toLocaleString();
                }

            } catch (error) {
                console.error('Error handling dashboard toggle update:', error);
            }
        });
    });
    // ======================= Handle Dynamic Title and Donut Label based on Period =======================
    
    const pageTitle = document.querySelector('.section-header .section-title');
    const donutCenterTitle = document.querySelector('.panel-pie .pie-center-label');

    function updateLabels(period) {
        if (!pageTitle) return;

        // Normalize period and update main title
        if (period === 'today') {
            pageTitle.innerText = "Today's Attendance";
        } else if (period === 'week') {
            pageTitle.innerText = "This Week's Attendance";
        } else if (period === 'month') {
            pageTitle.innerText = "This Month's Attendance";
        }

        // Optional: Update the donut chart's central label to match
        if (donutCenterTitle) {
            donutCenterTitle.innerText = `TOTAL · ${period.toUpperCase()}`;
        }
    }

    // Initialize with default state
    const currentActivePeriod = document.querySelector('.period-btn.active')?.dataset.period;
    if (currentActivePeriod) {
        updateLabels(currentActivePeriod);
    }

    // Attach listener to update on button click
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const period = btn.dataset.period;
            updateLabels(period);
        });
    });
});
</script>
@endsection