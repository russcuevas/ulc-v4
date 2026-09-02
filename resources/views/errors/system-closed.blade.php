<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Offline | ULC Operating Hours</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #FF5F00;
            --primary-glow: rgba(255, 95, 0, 0.35);
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.85);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 50% 20%, #1e293b 0%, #0b0f19 100%);
            color: var(--text-main);
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow */
        .ambient-glow {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--primary-glow) 0%, rgba(255, 95, 0, 0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
            opacity: 0.6;
        }

        .closed-card {
            position: relative;
            z-index: 1;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 48px 36px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 40px rgba(255, 95, 0, 0.08);
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(255, 95, 0, 0.12);
            border: 1px solid rgba(255, 95, 0, 0.3);
            border-radius: 999px;
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
            letter-spacing: 0.5px;
        }

        .brand-badge .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #ef4444;
            box-shadow: 0 0 10px #ef4444;
            animation: pulse-dot 1.8s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.5; }
        }

        .icon-container {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, rgba(255, 95, 0, 0.15), rgba(239, 68, 68, 0.1));
            border: 1px solid rgba(255, 95, 0, 0.25);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 38px;
            box-shadow: 0 10px 25px rgba(255, 95, 0, 0.15);
        }

        h1 {
            font-size: 26px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        p.description {
            color: var(--text-muted);
            font-size: 14.5px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .schedule-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-around;
            text-align: left;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .schedule-item i {
            font-size: 20px;
            color: var(--primary);
        }

        .schedule-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .schedule-value {
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
        }

        .countdown-box {
            background: linear-gradient(180deg, rgba(255, 95, 0, 0.08) 0%, rgba(255, 95, 0, 0.02) 100%);
            border: 1px solid rgba(255, 95, 0, 0.2);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .countdown-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fdba74;
            margin-bottom: 12px;
        }

        .timer-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .timer-segment {
            background: rgba(15, 23, 42, 0.7);
            border-radius: 12px;
            padding: 10px 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .timer-number {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.1;
        }

        .timer-label {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .server-time {
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .server-time i {
            color: var(--primary);
        }

        @media (max-width: 480px) {
            .closed-card {
                padding: 32px 20px;
            }
            .timer-number {
                font-size: 22px;
            }
            .schedule-box {
                flex-direction: column;
                gap: 14px;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="ambient-glow"></div>

    <div class="closed-card">
        <div class="brand-badge">
            <span class="dot"></span>
            <span>ULC System Offline</span>
        </div>

        <div class="icon-container">
            <i class="fas fa-lock"></i>
        </div>

        <h1>System is Currently Closed</h1>
        <p class="description">
            Ang system ay offline sa labas ng itinakdang oras ng opisina. 
            Maaari lamang ma-access ang mga transaksyon at serbisyo mula <strong>8:00 AM hanggang 5:00 PM</strong>.
        </p>

        <div class="schedule-box">
            <div class="schedule-item">
                <i class="fas fa-business-time"></i>
                <div>
                    <div class="schedule-label">Operating Hours</div>
                    <div class="schedule-value">8:00 AM – 5:00 PM</div>
                </div>
            </div>
            <div class="schedule-item">
                <i class="fas fa-power-off"></i>
                <div>
                    <div class="schedule-label">Off Hours</div>
                    <div class="schedule-value">5:01 PM – 7:59 AM</div>
                </div>
            </div>
        </div>

        <div class="countdown-box">
            <div class="countdown-title">
                <i class="fas fa-hourglass-half me-1"></i> System Opens In
            </div>
            <div class="timer-grid">
                <div class="timer-segment">
                    <div class="timer-number" id="countdown-hours">00</div>
                    <div class="timer-label">Hours</div>
                </div>
                <div class="timer-segment">
                    <div class="timer-number" id="countdown-minutes">00</div>
                    <div class="timer-label">Minutes</div>
                </div>
                <div class="timer-segment">
                    <div class="timer-number" id="countdown-seconds">00</div>
                    <div class="timer-label">Seconds</div>
                </div>
            </div>
        </div>

        <div class="server-time">
            <i class="fas fa-clock"></i>
            <span>Current Realtime: <strong id="live-time">{{ $currentTime ?? date('h:i:s A') }}</strong></span>
        </div>
    </div>

    <script>
        function updateCountdown() {
            const now = new Date();
            
            // Format current live time
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            document.getElementById('live-time').textContent = now.toLocaleTimeString('en-US', timeOptions);

            // Calculate next 8:00 AM opening time
            let openTime = new Date();
            openTime.setHours(8, 0, 0, 0);

            // Kung lagpas na ng 8:00 AM ngayon (ibig sabihin 5:01 PM to 11:59 PM),
            // ang next opening ay 8:00 AM kinabukasan (+1 day)
            if (now >= openTime) {
                openTime.setDate(openTime.getDate() + 1);
            }

            const diff = openTime - now;

            // Kung sumapit na ang 8:00:00 AM, kusa mag-reload ang page para makapasok na
            if (diff <= 0) {
                window.location.reload();
                return;
            }

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('countdown-hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('countdown-minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('countdown-seconds').textContent = String(seconds).padStart(2, '0');
        }

        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
</body>

</html>
