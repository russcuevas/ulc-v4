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
            background: #0b0b0f;
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient background glow effects */
        .ambient-glow-1 {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 95, 0, 0.06) 0%, rgba(255, 95, 0, 0) 70%);
            top: -10%;
            left: -10%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 700px;
            height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 123, 255, 0.05) 0%, rgba(0, 123, 255, 0) 70%);
            bottom: -10%;
            right: -10%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
        }

        /* Sidebar styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 280px;
            background: rgba(19, 19, 26, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            z-index: 1050;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-brand {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
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
            background: rgba(255, 95, 0, 0.1);
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1px solid rgba(255, 95, 0, 0.2);
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
            color: #9ca3af;
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
            color: #ffffff;
            background: rgba(255, 255, 255, 0.03);
        }

        .sidebar-link.active {
            background: rgba(255, 95, 0, 0.1);
            border: 1px solid rgba(255, 95, 0, 0.25);
            color: #FF5F00;
        }

        .sidebar-link.logout-link {
            color: #dc3545;
            margin-top: auto;
        }

        .sidebar-link.logout-link:hover {
            background: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.2);
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
            background: rgba(23, 23, 33, 0.5);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 990;
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
            background: rgba(19, 19, 26, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            align-items: center;
            justify-content: space-between;
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .logo-text span {
            color: #FF5F00;
        }

        .sidebar-toggle-btn {
            background: none;
            border: none;
            color: #ffffff;
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
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1040;
            display: none;
        }

        /* Chat Layout Container */
        .chat-layout {
            display: flex;
            height: calc(100vh - 180px);
            background: rgba(23, 23, 33, 0.75);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            overflow: hidden;
        }

        .chat-sidebar {
            width: 300px;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            background: rgba(19, 19, 26, 0.5);
        }

        .chat-sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 16px;
            color: #ffffff;
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
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid transparent;
        }

        .chat-agent-item:hover,
        .chat-agent-item.active {
            background: rgba(255, 95, 0, 0.1);
            border-color: rgba(255, 95, 0, 0.2);
        }

        .agent-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 95, 0, 0.1);
            border: 1px solid rgba(255, 95, 0, 0.25);
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
            color: #ffffff;
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
            background: transparent;
        }

        .chat-main-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
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
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .message-outgoing {
            background: #FF5F00;
            color: #ffffff;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 5px;
            text-align: right;
        }

        .chat-input-area {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(19, 19, 26, 0.3);
        }

        .chat-input-form {
            display: flex;
            gap: 10px;
        }

        .chat-input {
            flex-grow: 1;
            background: rgba(11, 11, 15, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 14px;
        }

        .chat-input:focus {
            border-color: #FF5F00 !important;
            box-shadow: 0 0 0 2px rgba(255, 95, 0, 0.2) !important;
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
                display: none;
            }

            .chat-layout {
                height: calc(100vh - 140px);
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
                    <div class="chat-agent-list">
                        <div class="chat-agent-item active">
                            <div class="agent-avatar">FC</div>
                            <div class="agent-info">
                                <div class="agent-name">Financial Counselor Support</div>
                                <div class="agent-status">Online</div>
                            </div>
                        </div>
                        <div class="chat-agent-item">
                            <div class="agent-avatar">OS</div>
                            <div class="agent-info">
                                <div class="agent-name">Office Secretary</div>
                                <div class="agent-status">Online</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Main Pane -->
                <div class="chat-main">
                    <div class="chat-main-header">
                        <div class="agent-avatar">FC</div>
                        <div>
                            <div class="agent-name" style="font-size: 16px;">Financial Counselor Support</div>
                            <div class="agent-status">Active Support Agent</div>
                        </div>
                    </div>

                    <!-- Chat Messages List -->
                    <div class="chat-messages" id="chat-messages-container">
                        <div class="message-bubble message-incoming">
                            Hello {{ $client->fullname }}! Welcome to ULC Chat Support. How can we help you with your
                            account today?
                            <div class="message-time" id="greeting-time"></div>
                        </div>
                    </div>

                    <!-- Chat Input form -->
                    <div class="chat-input-area">
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

            // Set greeting time to current time
            const now = new Date();
            const formatTimeShort = now.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('greeting-time').innerText = formatTimeShort;

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

            // Real-time Chat Simulation logic
            const chatForm = document.getElementById('chat-form');
            const chatInputField = document.getElementById('chat-input-field');
            const chatContainer = document.getElementById('chat-messages-container');

            if (chatForm && chatInputField && chatContainer) {
                chatForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const text = chatInputField.value.trim();
                    if (!text) return;

                    // User Message
                    const msgTime = new Date().toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const userBubble = `
                        <div class="message-bubble message-outgoing">
                            ${escapeHtml(text)}
                            <div class="message-time" style="color: rgba(255,255,255,0.7);">${msgTime}</div>
                        </div>
                    `;
                    chatContainer.insertAdjacentHTML('beforeend', userBubble);
                    chatInputField.value = '';
                    chatContainer.scrollTop = chatContainer.scrollHeight;

                    // Automated Counselor Response simulation after a tiny delay
                    setTimeout(function() {
                        let responseText =
                            "Thank you for reaching out! A representative will connect with you shortly to assist you with your loans/savings questions. You may also contact your designated area collector directly.";

                        const lowerText = text.toLowerCase();
                        if (lowerText.includes('balance') || lowerText.includes('due') || lowerText
                            .includes('pay')) {
                            responseText =
                                "To view your active balances, payment dues, or payment log, please navigate back to the 'Loans' portal section in your left sidebar.";
                        } else if (lowerText.includes('hi') || lowerText.includes('hello')) {
                            responseText =
                                "Hello! Hope you are having a great day. How can we support you today?";
                        } else if (lowerText.includes('savings')) {
                            responseText =
                                "For your savings account questions, you can coordinate directly with your collector or visit our branch office.";
                        }

                        const agentBubble = `
                            <div class="message-bubble message-incoming">
                                ${responseText}
                                <div class="message-time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                            </div>
                        `;
                        chatContainer.insertAdjacentHTML('beforeend', agentBubble);
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }, 1000);
                });
            }

            function escapeHtml(text) {
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        });
    </script>
</body>

</html>
