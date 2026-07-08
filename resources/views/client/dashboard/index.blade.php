<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Client Portal - Dashboard</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            color: #1e293b;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient background glow effects (disabled on light theme for clean look) */
        .ambient-glow-1,
        .ambient-glow-2 {
            display: none;
        }

        /* Sidebar styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 280px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            z-index: 1050;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .sidebar-brand {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.5px;
            margin-bottom: 35px;
            padding-left: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand span {
            color: #FF5F00;
        }

        .sidebar-brand i {
            color: #FF5F00;
            background: rgba(255, 95, 0, 0.05);
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1px solid rgba(255, 95, 0, 0.15);
            font-size: 18px;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: #475569;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 15px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            background: transparent;
            text-align: left;
            width: 100%;
        }

        .sidebar-link i {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .sidebar-link:hover {
            color: #FF5F00;
            background: rgba(255, 95, 0, 0.03);
        }

        .sidebar-link.active {
            background: rgba(255, 95, 0, 0.08);
            border: 1px solid rgba(255, 95, 0, 0.2);
            color: #FF5F00;
        }

        .sidebar-link.logout-link {
            color: #dc3545;
            margin-top: auto;
            /* Push logout to bottom */
        }

        .sidebar-link.logout-link:hover {
            background: rgba(220, 53, 69, 0.05);
            border-color: rgba(220, 53, 69, 0.15);
            color: #ff4d5e;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-header-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 990;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .main-body-container {
            padding: 40px;
            flex-grow: 1;
            position: relative;
            z-index: 10;
        }

        /* Mobile Responsive Sidebar toggling */
        .mobile-header {
            display: none;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .logo-text span {
            color: #FF5F00;
        }

        .sidebar-toggle-btn {
            background: none;
            border: none;
            color: #1e293b;
            font-size: 22px;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 1040;
            display: none;
        }

        /* Profile & Cards styling */
        .profile-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 30px;
            height: 100%;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .profile-avatar {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            background: rgba(255, 95, 0, 0.05);
            border: 1px solid rgba(255, 95, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #FF5F00;
        }

        .client-name {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 24px;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            gap: 15px;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-size: 14px;
            white-space: nowrap;
        }

        .info-value {
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
            text-align: right;
            word-break: break-all;
        }

        .section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 20px;
            color: #1e293b;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #FF5F00;
            padding-left: 12px;
        }

        .custom-table {
            color: #1e293b;
            margin-bottom: 0;
        }

        .custom-table th {
            background: #f8fafc !important;
            color: #475569 !important;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 16px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            white-space: nowrap;
        }

        .custom-table td {
            background: transparent !important;
            color: #1e293b !important;
            font-size: 14px;
            padding: 14px 16px !important;
            border-bottom: 1px solid #f1f5f9 !important;
            vertical-align: middle;
            white-space: nowrap;
        }

        .custom-table tbody tr:hover td {
            background: #f8fafc !important;
        }

        .status-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
        }

        .status-active {
            background: rgba(40, 167, 69, 0.08);
            border: 1px solid rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .status-lapsed {
            background: rgba(220, 53, 69, 0.08);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .status-paid {
            background: rgba(0, 123, 255, 0.08);
            border: 1px solid rgba(0, 123, 255, 0.2);
            color: #007bff;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.08);
            border: 1px solid rgba(255, 193, 7, 0.2);
            color: #ff8c00;
        }

        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #64748b;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 15px;
            color: rgba(0, 0, 0, 0.08);
        }

        .alert-lapsed {
            background: rgba(220, 53, 69, 0.05);
            border: 1px solid rgba(220, 53, 69, 0.15);
            border-radius: 12px;
            color: #dc3545;
            padding: 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Improved Scrollable Table styles for Mobile */
        .table-responsive {
            -webkit-overflow-scrolling: touch;
            overflow-x: auto;
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-280px);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .main-header-bar {
                display: none;
            }

            .mobile-header {
                display: flex;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .main-body-container {
                padding: 20px 15px;
            }
        }

        @media (max-width: 576px) {

            .custom-table th,
            .custom-table td {
                padding: 12px 10px !important;
                font-size: 13px !important;
            }

            .profile-card,
            .section-card {
                padding: 20px 15px !important;
                border-radius: 14px !important;
            }

            .client-name {
                font-size: 20px !important;
            }

            .sidebar {
                width: 250px;
            }

            .section-header {
                font-size: 18px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Left Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-user-circle"></i>
            <span>ULC Portal</span>
            <!-- Close Button for Mobile Sidebar Drawer -->
            <button class="d-lg-none ms-auto text-white" id="close-sidebar"
                style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 5px;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="sidebar-menu">
            <a href="{{ route('client.dashboard.page') }}" class="sidebar-link active">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Loans</span>
            </a>

            <a href="{{ route('client.chat.page') }}" class="sidebar-link">
                <i class="fas fa-comments"></i>
                <span>Chat Support</span>
            </a>

            <!-- Logout Link inside Sidebar -->
            <form id="logout-form" action="{{ route('client.logout.request') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <button class="sidebar-link logout-link"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </div>
    </div>

    <!-- Mobile Header -->
    <div class="mobile-header">
        <button class="sidebar-toggle-btn" id="toggle-sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="logo-text">ULC <span>Portal</span></h1>
        <!-- Manila live clock on mobile header -->
        <div class="text small ms-auto me-1" id="manila-time-mobile"
            style="font-weight: 600; font-size: 11px; color: #FF5F00;"></div>
    </div>

    <!-- Main Content Panel -->
    <div class="main-content">
        <!-- Main Desktop Header Bar -->
        <div class="main-header-bar">
            <h1 class="h4 m-0 font-family-outfit fw-bold text-white"></h1>
            <div class="text small" id="manila-time" style="font-weight: 500; color: #FF5F00;"></div>
        </div>

        <!-- Main Body Wrapper -->
        <div class="main-body-container">

            <div class="row">
                <!-- Left Column: Client Profile -->
                <div class="col-lg-4 mb-4">
                    <div class="profile-card">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h2 class="client-name">{{ $client->fullname }}</h2>
                                <span class="badge bg-secondary">Client Account</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="info-item">
                                <span class="info-label">Primary Phone</span>
                                <span class="info-value">{{ $client->phone ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Secondary Phone</span>
                                <span class="info-value">{{ $client->phone_number_2 ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Area Location</span>
                                <span class="info-value">
                                    @if ($area)
                                        {{ $area->location_name }} - [{{ $area->areas_name }}]
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Gender</span>
                                <span class="info-value">{{ ucfirst($client->gender ?? 'N/A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Loans details & payments -->
                <div class="col-lg-8">
                    <!-- Lapsed Notification Alert -->
                    @php
                        $hasLapsed = $loans->where('status', 'lapsed')->count() > 0;
                    @endphp
                    @if ($hasLapsed)
                        <div class="alert-lapsed">
                            <i class="fas fa-exclamation-triangle fs-4"></i>
                            <div>
                                <strong>Important Notice:</strong> You have one or more accounts with lapsed status.
                                Please coordinate with your collector to settle any outstanding dues.
                            </div>
                        </div>
                    @endif

                    <!-- Section: Loans List -->
                    <div class="section-card">
                        <h3 class="section-header">
                            <i class="fas fa-file-invoice-dollar" style="color: #FF5F00;"></i>
                            <span>Your Loan Portfolio</span>
                        </h3>

                        <div class="table-responsive">
                            <table class="table custom-table">
                                <thead>
                                    <tr>
                                        <th>Loan Term</th>
                                        <th class="text-end">Outstanding Balance</th>
                                        <th class="text-end">Savings</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loans as $loan)
                                        <tr>
                                            <td>
                                                <div>{{ date('M d, Y', strtotime($loan->loan_from)) }}</div>
                                                <div class="text small">to
                                                    {{ date('M d, Y', strtotime($loan->loan_to)) }}</div>
                                            </td>

                                            <td class="text-end font-monospace fw-bold text-warning">
                                                P{{ number_format($loan->balance, 2) }}</td>
                                            <td class="text-end font-monospace fw-bold text-success">
                                                P{{ number_format($loan->savings_balance, 2) }}</td>
                                            <td>
                                                @if ($loan->status === 'unpaid' || $loan->status === 'active')
                                                    <span class="status-badge status-active">Active</span>
                                                @elseif($loan->status === 'lapsed')
                                                    <span class="status-badge status-lapsed">Lapsed</span>
                                                @elseif($loan->status === 'paid' || $loan->status === 'settled')
                                                    <span class="status-badge status-paid"
                                                        style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); color: #9ca3af;">Paid
                                                    </span>
                                                @else
                                                    <span
                                                        class="status-badge status-pending">{{ ucfirst($loan->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">
                                                <div class="empty-state">
                                                    <i class="fas fa-folder-open"></i>
                                                    <p>No loans registered under your account yet.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Section: Payments (Lazy Loading and Searching) -->
                    <div class="section-card">
                        @php
                            $isFC = $area && stripos($area->location_name ?? '', 'Financial Counselor') !== false;
                        @endphp
                        <div
                            class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                            <h3 class="section-header m-0 border-0 ps-0">
                                <i class="fas fa-history" style="color: #FF5F00;"></i>
                                <span>Payments</span>
                            </h3>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text small" style="white-space: nowrap;">Search Date:</span>
                                <input type="text" id="payment-search-input"
                                    class="form-control form-control-sm bg-white text-dark"
                                    placeholder="e.g. Jun 28, 2026"
                                    style="width: 180px; border-radius: 8px; font-size: 13px; border: 1.5px solid #cbd5e1;">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table custom-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Collected By</th>
                                        <th>Payment Type</th>
                                        <th class="text-end">Old Balance</th>
                                        <th class="text-end">Amount Paid</th>
                                        @if ($isFC)
                                            <th class="text-end">Savings</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="payment-table-body">
                                    <!-- Rows populated by JavaScript lazy loading -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Notifications Initialization
            const notyf = new Notyf({
                duration: 5000,
                position: {
                    x: 'right',
                    y: 'top'
                }
            });

            @if (session('success'))
                notyf.success("{{ session('success') }}");
            @endif

            @if (session('error'))
                notyf.error("{{ session('error') }}");
            @endif

            // Manila Live Clock
            function updateManilaTime() {
                const options = {
                    timeZone: 'Asia/Manila',
                    year: 'numeric',
                    month: 'long',
                    day: '2-digit',
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                };

                const now = new Date();
                const formatted = new Intl.DateTimeFormat('en-US', options).format(now);

                const clockEl = document.getElementById('manila-time');
                if (clockEl) {
                    clockEl.innerText = formatted;
                }

                const clockElMobile = document.getElementById('manila-time-mobile');
                if (clockElMobile) {
                    // Simpler clock layout for mobile header space constraints
                    const mobileOptions = {
                        timeZone: 'Asia/Manila',
                        hour: 'numeric',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    };
                    clockElMobile.innerText = new Intl.DateTimeFormat('en-US', mobileOptions).format(now);
                }
            }

            updateManilaTime();
            setInterval(updateManilaTime, 1000);

            // Mobile Navigation Toggle drawer
            const toggleSidebarBtn = document.getElementById('toggle-sidebar');
            const closeSidebarBtn = document.getElementById('close-sidebar');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (sidebar && overlay) {
                if (toggleSidebarBtn) {
                    toggleSidebarBtn.addEventListener('click', function() {
                        sidebar.classList.add('active');
                        overlay.classList.add('active');
                    });
                }

                if (closeSidebarBtn) {
                    closeSidebarBtn.addEventListener('click', function() {
                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');
                    });
                }

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }

            // Lazy Loading & Search for Payments
            const allPayments = @json($payments);
            const isFC = {{ $isFC ? 'true' : 'false' }};
            const tableBody = document.getElementById('payment-table-body');
            const searchInput = document.getElementById('payment-search-input');

            let chunkSize = 15;
            let loadedCount = 15;
            let filteredPayments = [...allPayments];

            function formatDate(dateStr) {
                if (!dateStr) return 'N/A';
                const d = new Date(dateStr.replace(/-/g, "/"));
                if (isNaN(d.getTime())) return dateStr;
                const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                const month = months[d.getMonth()];
                const day = String(d.getDate()).padStart(2, '0');
                const year = d.getFullYear();
                return `${month} ${day}, ${year}`;
            }

            function formatTime(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr.replace(/-/g, "/"));
                if (isNaN(d.getTime())) return '';
                let hours = d.getHours();
                const minutes = String(d.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12;
                return `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
            }

            function formatCurrency(amount) {
                const val = parseFloat(amount);
                return isNaN(val) ? 'P0.00' : 'P' + val.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function renderPayments(append = false) {
                if (!tableBody) return;

                if (!append) {
                    tableBody.innerHTML = '';
                }

                if (filteredPayments.length === 0) {
                    const cols = isFC ? 6 : 5;
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="${cols}">
                                <div class="empty-state">
                                    <i class="fas fa-receipt"></i>
                                    <p>No transactions found matching search criteria.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }

                const startIndex = append ? loadedCount - chunkSize : 0;
                const endIndex = Math.min(loadedCount, filteredPayments.length);

                let rowsHtml = '';
                for (let i = startIndex; i < endIndex; i++) {
                    const payment = filteredPayments[i];
                    const dateFormatted = formatDate(payment.due_date);
                    const timeFormatted = formatTime(payment.created_at);
                    const remainingBalance = payment.hasOwnProperty('computed_remaining_balance') 
                        ? payment.computed_remaining_balance 
                        : Math.max(0, (payment.old_balance || 0) - payment.collection);
                    const oldBalanceFormatted = formatCurrency(remainingBalance);
                    const collectionFormatted = formatCurrency(payment.collection);
                    const savingsFormatted = formatCurrency(payment.savings_amount || 0);
                    const paymentType = payment.type ? payment.type : 'Daily';

                    rowsHtml += `
                        <tr>
                            <td>
                                <div>${dateFormatted}</div>
                                <div class="text small">${timeFormatted}</div>
                            </td>
                            <td class="fw-bold text-white">
                                ${payment.collected_by_name || payment.collected_by || 'N/A'}
                            </td>
                            <td>
                                <span class="badge bg-dark border border-secondary text-capitalize">
                                    ${paymentType}
                                </span>
                            </td>
                            <td class="text-end font-monospace text-warning fw-bold">
                                ${oldBalanceFormatted}
                            </td>
                            <td class="text-end font-monospace text-success fw-bold">
                                ${collectionFormatted}
                            </td>
                            ${isFC ? `
                                                                            <td class="text-end font-monospace text-success fw-bold">
                                                                                ${savingsFormatted}
                                                                            </td>
                                                                        ` : ''}
                        </tr>
                    `;
                }

                if (append) {
                    tableBody.insertAdjacentHTML('beforeend', rowsHtml);
                } else {
                    tableBody.innerHTML = rowsHtml;
                }
            }

            // Perform initial render
            renderPayments();

            // Search input filter event
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim().toLowerCase();

                    if (query === '') {
                        filteredPayments = [...allPayments];
                    } else {
                        filteredPayments = allPayments.filter(payment => {
                            const dateStr = formatDate(payment.due_date).toLowerCase();
                            const timeStr = formatTime(payment.created_at).toLowerCase();
                            const collectorStr = (payment.collected_by_name || payment
                                .collected_by || '').toString().toLowerCase();
                            const typeStr = (payment.type || 'Daily').toLowerCase();
                            return dateStr.includes(query) || timeStr.includes(query) ||
                                collectorStr
                                .includes(query) || typeStr.includes(query);
                        });
                    }

                    loadedCount = chunkSize;
                    renderPayments(false);
                });
            }

            // Infinite Scroll listener
            window.addEventListener('scroll', function() {
                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 150) {
                    if (loadedCount < filteredPayments.length) {
                        loadedCount += chunkSize;
                        renderPayments(true);
                    }
                }
            });
        });
    </script>
</body>

</html>
