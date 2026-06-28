<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC System - Chat Support</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .chat-container {
            display: flex;
            height: calc(100vh - 120px);
            background: #ffffff;
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .chat-sidebar {
            width: 320px;
            border-right: 1px solid #e3e6f0;
            display: flex;
            flex-direction: column;
            background: #f8f9fc;
        }

        .chat-sidebar-header {
            padding: 15px;
            background: #ffffff;
            border-bottom: 1px solid #e3e6f0;
        }

        .convo-list {
            flex-grow: 1;
            overflow-y: auto;
        }

        .convo-item {
            padding: 15px;
            border-bottom: 1px solid #e3e6f0;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
        }

        .convo-item:hover {
            background: #f1f3f9;
        }

        .convo-item.active {
            background: rgba(255, 95, 0, 0.08);
            border-left: 4px solid #FF5F00;
        }

        .convo-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #FF5F00;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .convo-details {
            flex-grow: 1;
            min-width: 0;
        }

        .convo-name {
            font-weight: 600;
            color: #2e384d;
            font-size: 14px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .convo-preview {
            font-size: 12px;
            color: #7e8299;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }

        .chat-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
        }

        .chat-header-title {
            font-weight: 600;
            color: #2e384d;
            font-size: 16px;
            margin: 0;
        }

        .chat-header-subtitle {
            font-size: 12px;
            color: #7e8299;
        }

        .chat-messages-container {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f4f6f9;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message-bubble {
            max-width: 65%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
            word-wrap: break-word;
        }

        .message-incoming {
            background: #ffffff;
            color: #2e384d;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            border: 1px solid #e3e6f0;
        }

        .message-outgoing {
            background: #FF5F00;
            color: #ffffff;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
            box-shadow: 0 2px 4px rgba(255, 95, 0, 0.15);
        }

        .message-time {
            font-size: 10px;
            color: rgba(0, 0, 0, 0.35);
            margin-top: 5px;
            text-align: right;
        }

        .message-outgoing .message-time {
            color: rgba(255, 255, 255, 0.7);
        }

        .chat-input-area {
            padding: 15px 20px;
            border-top: 1px solid #e3e6f0;
            background: #ffffff;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .chat-input-area textarea {
            flex-grow: 1;
            border: 1px solid #e3e6f0;
            border-radius: 20px;
            padding: 10px 18px;
            resize: none;
            height: 42px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .chat-input-area textarea:focus {
            border-color: #FF5F00;
        }

        .send-btn {
            background: #FF5F00;
            color: #ffffff;
            border: none;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .send-btn:hover {
            background: #e05300;
        }

        .chat-placeholder {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #a0a5ba;
            background: #f8f9fc;
        }

        .chat-placeholder i {
            font-size: 64px;
            color: #e3e6f0;
            margin-bottom: 15px;
        }

        .unread-badge {
            background: #28a745;
            color: white;
            border-radius: 10px;
            padding: 2px 7px;
            font-size: 10px;
            font-weight: 700;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        {{-- TOP BAR --}}
        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <h1 class="animation__shake">ULC</h1>
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand" style="background-color:#FF5F00;">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>
        </nav>


        {{-- LEFT SIDEBAR --}}
        <aside class="main-sidebar sidebar-light-primary elevation-4" style="background-color:#f8f9fa;">
            <a href="index3.html" class="brand-link" style="background-color: #FF5F00; color: white;">
                <i class="fas fa-database img-circle elevation-3 ml-3"></i>
                <span class="brand-text font-weight-light">ULC System</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">

                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        @php $user = Session::get('user'); @endphp

                        <a href="#" class="d-block" style="color: #FF5F00;">
                            Welcome! <br>
                            Collector <br>
                            {{ $user->fullname ?? 'User' }}
                        </a>
                    </div>
                </div>

                <nav class="mt-2 d-flex flex-column">

                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">

                        <li class="nav-item">
                            <a href="{{ route('collector.dashboard.page') }}"
                                class="nav-link {{ request()->routeIs('collector.dashboard.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('collector.collections.page') }}"
                                class="nav-link {{ request()->routeIs('collector.collections.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-hand-holding-usd"></i>
                                <p>Collections</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('collector.chat.page') }}"
                                class="nav-link {{ request()->routeIs('collector.chat.page') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-comments"></i>
                                <p>Chat Support</p>
                            </a>
                        </li>

                    </ul>


                </nav>

            </div>
            <!-- /.sidebar -->
        </aside>


        {{-- MAIN --}}
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <h1 class="m-0 text-dark">Chat Support Workspace</h1>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">

                    <div class="chat-container">
                        <!-- Sidebar: Conversations List -->
                        <div class="chat-sidebar">
                            <div class="chat-sidebar-header">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="chat-search" class="form-control"
                                        placeholder="Search client name...">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="convo-list" id="conversations-list">
                                <!-- Populated dynamically by Ajax -->
                            </div>
                        </div>

                        <!-- Chat Area -->
                        <div class="chat-main" id="chat-pane-active" style="display: none;">
                            <div class="chat-header">
                                <div>
                                    <h3 class="chat-header-title" id="active-client-name">Loading...</h3>
                                    <span class="chat-header-subtitle" id="active-client-phone">Loading...</span>
                                </div>
                                <span class="badge bg-secondary text-capitalize" id="staff-identity-badge"
                                    style="font-size: 11px;">
                                    Scope: {{ $role }}
                                </span>
                            </div>

                            <div class="chat-messages-container" id="chat-messages-box">
                                <!-- Populated dynamically by Ajax -->
                            </div>

                            <div class="chat-input-area">
                                <textarea id="chat-textarea-message" placeholder="Type a secure message..." rows="1"></textarea>
                                <button class="send-btn" id="chat-send-btn">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Default Chat Placeholder -->
                        <div class="chat-placeholder" id="chat-pane-placeholder">
                            <i class="fas fa-comments"></i>
                            <h5>Confidential Support Chat</h5>
                            <p class="text-muted small">Select an active client session on the left to begin messaging
                                securely.</p>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <script>
        $(document).ready(function() {
            const notyf = new Notyf({
                duration: 5000,
                position: {
                    x: 'right',
                    y: 'top'
                }
            });

            let currentConversationId = null;
            let pollingInterval = null;
            let conversationsData = [];

            // Load Conversations
            function loadConversations(forceSelectId = null) {
                $.ajax({
                    url: "{{ route('api.chat.conversations') }}",
                    type: "GET",
                    success: function(response) {
                        conversationsData = response;
                        renderConversationsList(forceSelectId);
                    },
                    error: function() {
                        console.error("Failed to load conversations list.");
                    }
                });
            }

            function renderConversationsList(forceSelectId = null) {
                const searchVal = $('#chat-search').val().trim().toLowerCase();
                const container = $('#conversations-list');
                container.empty();

                const filtered = conversationsData.filter(c => {
                    const name = (c.client_name || '').toLowerCase();
                    return name.includes(searchVal);
                });

                if (filtered.length === 0) {
                    container.html(
                        '<div class="text-center text-muted p-4 small">No active chat sessions found.</div>');
                    return;
                }

                filtered.forEach(convo => {
                    const initials = (convo.client_name || 'C').substring(0, 2).toUpperCase();
                    const activeClass = convo.id === currentConversationId ? 'active' : '';
                    const unreadBadge = convo.unread_count > 0 ?
                        `<span class="unread-badge ml-auto">${convo.unread_count}</span>` :
                        '';

                    const itemHtml = `
                        <div class="convo-item ${activeClass}" data-convo-id="${convo.id}" data-client-name="${convo.client_name}" data-client-phone="${convo.client_phone}" data-areas-name="${convo.areas_name || 'N/A'}" data-location-name="${convo.location_name || 'N/A'}">
                            <div class="convo-avatar">${initials}</div>
                            <div class="convo-details">
                                <div class="convo-name">${convo.client_name}</div>
                                <div style="font-size: 11px; color: #ff5f00; font-weight: 600; margin-bottom: 2px;">
                                    ${convo.location_name || 'N/A'} - [${convo.areas_name || 'N/A'}]
                                </div>
                                <div class="convo-preview">${convo.latest_message || 'No messages yet.'}</div>
                            </div>
                            ${unreadBadge}
                        </div>
                    `;
                    container.append(itemHtml);
                });

                if (forceSelectId) {
                    $(`.convo-item[data-convo-id="${forceSelectId}"]`).addClass('active');
                }
            }

            // Load Messages
            function loadMessages(convoId, autoScroll = false) {
                if (!convoId) return;

                $.ajax({
                    url: `/api/chat/messages/${convoId}`,
                    type: "GET",
                    success: function(messages) {
                        const box = $('#chat-messages-box');
                        box.empty();

                        if (messages.length === 0) {
                            box.html(
                                '<div class="text-center text-muted small py-4">No messages in this chat session yet.</div>'
                            );
                            return;
                        }

                        messages.forEach(msg => {
                            const isOutgoing = msg.sender_type !== 'client';
                            const bubbleClass = isOutgoing ? 'message-outgoing' :
                                'message-incoming';
                            const dateStr = new Date(msg.created_at).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            const bubbleHtml = `
                                <div class="message-bubble ${bubbleClass}">
                                    <div>${escapeHtml(msg.message)}</div>
                                    <div class="message-time">${dateStr}</div>
                                </div>
                            `;
                            box.append(bubbleHtml);
                        });

                        if (autoScroll) {
                            box.scrollTop(box[0].scrollHeight);
                        }
                    },
                    error: function() {
                        console.error("Failed to load messages.");
                    }
                });
            }

            // Selection Handler
            $(document).on('click', '.convo-item', function() {
                const convoId = $(this).data('convo-id');
                const clientName = $(this).data('client-name');
                const clientPhone = $(this).data('client-phone');
                const areasName = $(this).data('areas-name');
                const locationName = $(this).data('location-name');

                $('.convo-item').removeClass('active');
                $(this).addClass('active');

                currentConversationId = convoId;

                $('#chat-pane-placeholder').hide();
                $('#chat-pane-active').show();

                $('#active-client-name').text(clientName);
                $('#active-client-phone').text(`Phone: ${clientPhone} | Location: ${locationName} - [${areasName}]`);

                loadMessages(convoId, true);

                // Clear prior interval and reset poller
                if (pollingInterval) clearInterval(pollingInterval);
                pollingInterval = setInterval(function() {
                    loadMessages(currentConversationId, false);
                    loadConversations(currentConversationId);
                }, 3000);
            });

            // Send Message Handler
            function performSend() {
                const text = $('#chat-textarea-message').val().trim();
                if (!text || !currentConversationId) return;

                $.ajax({
                    url: "{{ route('api.chat.send') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        conversation_id: currentConversationId,
                        message: text
                    },
                    success: function() {
                        $('#chat-textarea-message').val('');
                        loadMessages(currentConversationId, true);
                        loadConversations(currentConversationId);
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message :
                            "Failed to send message.";
                        notyf.error(errorMsg);
                    }
                });
            }

            $('#chat-send-btn').click(performSend);
            $('#chat-textarea-message').on('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    performSend();
                }
            });

            // Search Filter input trigger
            $('#chat-search').on('input', function() {
                renderConversationsList();
            });

            // Helper to escape HTML tags
            function escapeHtml(text) {
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // Initial load
            loadConversations();
            setInterval(function() {
                if (!currentConversationId) {
                    loadConversations();
                }
            }, 5000);
        });
    </script>
</body>

</html>
