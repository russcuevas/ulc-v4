<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Client Portal - Chat Support</title>

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

        /* Ambient background glow effects (disabled on light theme) */
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

        /* Chat Layout Container */
        .chat-layout {
            display: flex;
            height: calc(100vh - 180px);
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .chat-sidebar {
            width: 300px;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
        }

        .chat-sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 16px;
            color: #1e293b;
        }

        .chat-agent-list {
            flex-grow: 1;
            overflow-y: auto;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .chat-agent-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        .chat-agent-item:hover,
        .chat-agent-item.active {
            background: rgba(255, 95, 0, 0.06);
            border-color: rgba(255, 95, 0, 0.15);
        }

        .agent-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 95, 0, 0.05);
            border: 1px solid rgba(255, 95, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FF5F00;
            font-size: 18px;
            font-weight: 700;
        }

        .agent-info {
            flex-grow: 1;
            min-width: 0;
        }

        .agent-name {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 14px;
            color: #1e293b;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .agent-status {
            font-size: 11px;
            color: #28a745;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .agent-status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #28a745;
            display: inline-block;
        }

        .chat-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }

        .chat-main-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            background: #f8fafc;
        }

        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
        }

        .message-incoming {
            background: #ffffff;
            color: #1e293b;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .message-outgoing {
            background: #FF5F00;
            color: #ffffff;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
            box-shadow: 0 1px 3px rgba(255, 95, 0, 0.1);
        }

        .message-time {
            font-size: 10px;
            color: #64748b;
            margin-top: 5px;
            text-align: right;
        }

        .message-outgoing .message-time {
            color: rgba(255, 255, 255, 0.7);
        }

        .chat-input-area {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .chat-input-form {
            display: flex;
            gap: 10px;
        }

        .chat-input {
            flex-grow: 1;
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #1e293b !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 14px;
        }

        .chat-input::placeholder {
            color: #94a3b8;
        }

        .chat-input:focus {
            border-color: #FF5F00 !important;
            box-shadow: 0 0 0 2px rgba(255, 95, 0, 0.1) !important;
            outline: none;
        }

        .chat-send-btn {
            background: #FF5F00;
            border: none;
            color: #ffffff;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .chat-send-btn:hover {
            background: #e05300;
            transform: scale(1.02);
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

        @media (max-width: 768px) {
            .chat-sidebar {
                position: absolute;
                top: 0;
                left: -100%;
                bottom: 0;
                width: 260px;
                background: #ffffff;
                z-index: 999;
                transition: left 0.25s ease;
                display: flex !important;
                border-right: 1px solid #e2e8f0;
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.05);
            }

            .chat-sidebar.active {
                left: 0;
            }

            .chat-layout {
                height: calc(100vh - 140px);
                position: relative;
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
            <a href="{{ route('client.dashboard.page') }}" class="sidebar-link">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Loans</span>
            </a>

            <a href="{{ route('client.chat.page') }}" class="sidebar-link active">
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
            <div class="chat-layout">
                <!-- Chat Agent List Sidebar -->
                <div class="chat-sidebar">
                    <div class="chat-sidebar-header">Support Agents</div>
                    <div class="chat-agent-list" id="agents-list">
                        <!-- AI Support Bot (Ultra Support) -->
                        <div class="chat-agent-item active" data-staff-type="bot" data-staff-id="0"
                            data-staff-name="Ultra Support (AI Bot)">
                            <div class="agent-info">
                                <div class="agent-name" style="font-weight: 700;">Ultra Support (AI Bot)</div>
                                <div class="agent-status" style="color: #FF5F00;">24/7 Virtual Assistant</div>
                            </div>
                        </div>

                        @if ($collector)
                            <div class="chat-agent-item" data-staff-type="collector"
                                data-staff-id="{{ $collector->id }}"
                                data-staff-name="Collector: {{ $collector->fullname }}">
                                <div class="agent-info">
                                    <div class="agent-name">Collector: {{ $collector->fullname }}</div>
                                    <div class="agent-status">Area Support</div>
                                </div>
                            </div>
                        @endif
                        @if ($secretary)
                            <div class="chat-agent-item" data-staff-type="secretary"
                                data-staff-id="{{ $secretary->id }}"
                                data-staff-name="Secretary: {{ $secretary->fullname }}">
                                <div class="agent-info">
                                    <div class="agent-name">Secretary: {{ $secretary->fullname }}</div>
                                    <div class="agent-status">Office Support</div>
                                </div>
                            </div>
                        @endif
                        @if ($admin)
                            <div class="chat-agent-item" data-staff-type="admin" data-staff-id="{{ $admin->id }}"
                                data-staff-name="System Admin">
                                <div class="agent-info">
                                    <div class="agent-name">System Support (Admin)</div>
                                    <div class="agent-status">Helpdesk Support</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Chat Main Pane -->
                <div class="chat-main">
                    <div class="chat-main-header d-flex align-items-center justify-content-between">
                        <div>
                            <div class="agent-name" id="active-agent-name" style="font-size: 16px;">Ultra Support (AI
                                Bot)</div>
                            <div class="agent-status" id="active-agent-status">24/7 Virtual Assistant</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <!-- Clear Chat Button -->
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clear-chat-history"
                                style="border-radius: 8px; font-size: 12px; font-weight: 600; padding: 6px 12px; display: none;">
                                <i class="fas fa-trash-alt me-1"></i> Clear Chat
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning d-md-none" id="toggle-chat-agents"
                                style="border-radius: 8px; font-size: 12px; font-weight: 600; border-color: #FF5F00 !important; color: #FF5F00 !important; padding: 6px 12px;">
                                <i class="fas fa-comments me-1"></i> Chats
                            </button>
                        </div>
                    </div>

                    <!-- Chat Messages List -->
                    <div class="chat-messages" id="chat-messages-container">
                        <!-- Loaded dynamically -->
                    </div>

                    <!-- Chat Input form -->
                    <div class="chat-input-area">
                        <!-- AI Quick Questions Section -->
                        <div id="ai-quick-questions" style="display: none; padding-bottom: 12px;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted small" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Mga Pwedeng Itanong sa AI:</span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-warning text-decoration-none" id="toggle-quick-questions" style="font-size: 11px; font-weight: 600;">
                                    <i class="fas fa-chevron-up me-1"></i><span>Itago</span>
                                </button>
                            </div>
                            <div id="ai-questions-wrapper" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <button type="button" class="btn btn-sm btn-outline-warning bot-question-btn"
                                    data-question="Natanggap ba ang pinadala kong bayad kahapon?"
                                    style="border-radius: 20px; font-size: 11px; font-weight: 600; padding: 6px 12px;">Natanggap
                                    ba ang bayad ko kahapon? 📅</button>
                                <button type="button" class="btn btn-sm btn-outline-warning bot-question-btn"
                                    data-question="Natanggap ba ang bayad ko ngayon?"
                                    style="border-radius: 20px; font-size: 11px; font-weight: 600; padding: 6px 12px;">Natanggap
                                    ba ang bayad ko ngayon? 💵</button>
                                <button type="button" class="btn btn-sm btn-outline-warning bot-question-btn"
                                    data-question="Magkano nalang ang balanse ko?"
                                    style="border-radius: 20px; font-size: 11px; font-weight: 600; padding: 6px 12px;">Magkano
                                    nalang ang balanse ko? 📊</button>
                                
                                @php
                                    $isFC = $area && stripos($area->location_name ?? '', 'Financial Counselor') !== false;
                                @endphp
                                @if($isFC)
                                    <button type="button" class="btn btn-sm btn-outline-warning bot-question-btn"
                                        data-question="Magkano na ang aking naipon (Savings)?"
                                        style="border-radius: 20px; font-size: 11px; font-weight: 600; padding: 6px 12px;">Magkano
                                        ang naipon ko (Savings)? 🏦</button>
                                @endif

                                <button type="button" class="btn btn-sm btn-outline-warning bot-question-btn"
                                    data-question="Sino ang aking collector at secretary?"
                                    style="border-radius: 20px; font-size: 11px; font-weight: 600; padding: 6px 12px;">Sino
                                    ang collector ko? 👤</button>
                            </div>
                        </div>

                        <form class="chat-input-form" id="chat-form">
                            <input type="text" id="chat-input-field" class="form-control chat-input"
                                placeholder="Type your message here..." autocomplete="off">
                            <button type="submit" class="chat-send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
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

            // AJAX Chat logic
            const notyf = new Notyf({
                duration: 5000,
                position: {
                    x: 'right',
                    y: 'top'
                }
            });

            let conversations = [];
            let activeConversationId = null;
            let pollingInterval = null;

            function getSelectedAgent() {
                const activeItem = $('.chat-agent-item.active');
                if (!activeItem.length) return null;
                return {
                    type: activeItem.data('staff-type'),
                    id: activeItem.data('staff-id'),
                    name: activeItem.data('staff-name')
                };
            }

            function loadConversations(callback) {
                $.ajax({
                    url: "{{ route('api.chat.conversations') }}",
                    type: "GET",
                    success: function(response) {
                        conversations = response;
                        if (callback) callback();
                    }
                });
            }

            function formatBotMessage(text) {
                let formatted = escapeHtml(text)
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/^\*\s(.*)$/gm, '• $1')
                    .replace(/\n/g, '<br>');
                return formatted;
            }

            function selectAgent() {
                const agent = getSelectedAgent();
                if (!agent) return;

                // Update active agent header details
                $('#active-agent-name').text(agent.name);

                if (agent.type === 'bot') {
                    $('#active-agent-status').text('Virtual Assistant');
                    $('#ai-quick-questions').show();
                } else {
                    $('#active-agent-status').text('Messaging Session');
                    $('#ai-quick-questions').hide();
                }

                // Find matching conversation
                const match = conversations.find(c => c.staff_type === agent.type && c.staff_id === agent.id);
                if (match) {
                    activeConversationId = match.id;
                    $('#clear-chat-history').show();
                    loadMessages(match.id, true);
                    resetPoller(match.id);
                } else {
                    activeConversationId = null;
                    $('#clear-chat-history').hide();
                    let placeholderText = `Send a message to start a secure chat with ${agent.name}.`;
                    if (agent.type === 'bot') {
                        placeholderText =
                            `Click a question below or send a message to start a session with Ultra Support AI.`;
                    }
                    $('#chat-messages-container').html(`
                        <div class="text-center text-muted small py-5">
                            <i class="fas fa-comment-dots fs-1 mb-2 text-secondary"></i>
                            <p>${placeholderText}</p>
                        </div>
                    `);
                    if (pollingInterval) clearInterval(pollingInterval);
                }
            }

            function loadMessages(convoId, autoScroll = false) {
                if (!convoId) return;

                $.ajax({
                    url: `/api/chat/messages/${convoId}`,
                    type: "GET",
                    success: function(messages) {
                        // Check if we are still viewing the same conversation
                        if (activeConversationId !== convoId) return;

                        const container = $('#chat-messages-container');

                        // Keep track of scroll position to prevent jumping unless autoscrolling
                        const isAtBottom = container[0].scrollHeight - container.scrollTop() <=
                            container.outerHeight() + 50;

                        container.empty();

                        if (messages.length === 0) {
                            container.html(
                                '<div class="text-center text-muted small py-4">No messages yet.</div>'
                            );
                            return;
                        }

                        messages.forEach(msg => {
                            const isOutgoing = msg.sender_type === 'client';
                            const bubbleClass = isOutgoing ? 'message-outgoing' :
                                'message-incoming';
                            const timeStr = new Date(msg.created_at).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            let parsedText = '';
                            if (msg.sender_type === 'bot') {
                                parsedText = formatBotMessage(msg.message);
                            } else {
                                parsedText = escapeHtml(msg.message);
                            }

                            const bubbleHtml = `
                                <div class="message-bubble ${bubbleClass}">
                                    <div>${parsedText}</div>
                                    <div class="message-time">${timeStr}</div>
                                </div>
                            `;
                            container.append(bubbleHtml);
                        });

                        if (autoScroll || isAtBottom) {
                            container.scrollTop(container[0].scrollHeight);
                        }
                    }
                });
            }

            function resetPoller(convoId) {
                if (pollingInterval) clearInterval(pollingInterval);
                pollingInterval = setInterval(function() {
                    loadMessages(convoId, false);
                }, 3000);
            }

            // Click quick question button handler
            $(document).on('click', '.bot-question-btn', function() {
                const questionText = $(this).data('question');
                $('#chat-input-field').val(questionText);
                $('#chat-form').submit();
            });

            // Toggle Chat Agents Sidebar on Mobile
            $(document).on('click', '#toggle-chat-agents', function(e) {
                e.stopPropagation();
                $('.chat-sidebar').toggleClass('active');
            });

            // Click agent item handler
            $(document).on('click', '.chat-agent-item', function() {
                $('.chat-agent-item').removeClass('active');
                $(this).addClass('active');
                $('.chat-sidebar').removeClass('active'); // Close mobile sidebar drawer
                selectAgent();
            });

            // Close sidebar when clicking outside on mobile
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.chat-sidebar').length && !$(e.target).closest('#toggle-chat-agents').length) {
                    $('.chat-sidebar').removeClass('active');
                }
            });

            // Submit form handler
            $('#chat-form').on('submit', function(e) {
                e.preventDefault();
                const text = $('#chat-input-field').val().trim();
                if (!text) return;

                const agent = getSelectedAgent();
                if (!agent) return;

                const postData = {
                    _token: "{{ csrf_token() }}",
                    message: text
                };

                if (activeConversationId) {
                    postData.conversation_id = activeConversationId;
                } else {
                    postData.receiver_type = agent.type;
                    postData.receiver_id = agent.id;
                }

                $.ajax({
                    url: "{{ route('api.chat.send') }}",
                    type: "POST",
                    data: postData,
                    success: function(response) {
                        $('#chat-input-field').val('');

                        // Reload conversations to register the new conversation ID if it was created
                        loadConversations(function() {
                            if (response.conversation_id) {
                                activeConversationId = response.conversation_id;
                                $('#clear-chat-history').show();
                                resetPoller(activeConversationId);
                            }
                            loadMessages(activeConversationId, true);
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message :
                            "Failed to send message.";
                        notyf.error(errorMsg);
                    }
                });
            });

            // Toggle quick questions visibility
            $(document).on('click', '#toggle-quick-questions', function() {
                const wrapper = $('#ai-questions-wrapper');
                const icon = $(this).find('i');
                const label = $(this).find('span');
                
                wrapper.slideToggle(200, function() {
                    if (wrapper.is(':visible')) {
                        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        label.text('Itago');
                    } else {
                        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        label.text('Ipakita');
                    }
                });
            });

            // Clear Chat History Handler
            $(document).on('click', '#clear-chat-history', function() {
                if (!activeConversationId) return;

                if (confirm('Sigurado ka ba na nais mong i-clear ang chat history na ito? Hindi na ito maibabalik.')) {
                    $.ajax({
                        url: "{{ route('api.chat.clear') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            conversation_id: activeConversationId
                        },
                        success: function(response) {
                            notyf.success(response.message || 'Chat history cleared.');
                            loadMessages(activeConversationId, true); // Refresh pane
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : "Failed to clear chat.";
                            notyf.error(errorMsg);
                        }
                    });
                }
            });

            // Helper to escape HTML
            function escapeHtml(text) {
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // Initial load sequence
            loadConversations(function() {
                selectAgent();
            });
        });
    </script>
</body>

</html>
