<?php 
require('_class/config.php'); 
require('session.php');   
?>
<!DOCTYPE html>
<html>
<head>
    <?php require('_inc/head.php'); ?>
    <title>AI Finans Asistanı</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>
    <style>
        .chat-container {
            height: 600px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            padding: 20px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .chat-message {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 15px;
            max-width: 85%;
            position: relative;
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .user-message {
            background: #2196F3;
            color: white;
            margin-left: auto;
            margin-right: 10px;
            border-bottom-right-radius: 5px;
        }
        .bot-message {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            margin-right: auto;
            margin-left: 10px;
            border-bottom-left-radius: 5px;
        }
        .chat-input {
            margin-top: 20px;
            position: relative;
        }
        .suggestions {
            margin: 20px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .suggestion-chip {
            display: inline-block;
            padding: 8px 15px;
            background: #e3f2fd;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #90caf9;
            color: #1976d2;
            font-size: 0.9em;
        }
        .suggestion-chip:hover {
            background: #bbdefb;
            transform: translateY(-2px);
        }
        .typing-indicator {
            padding: 10px;
            margin: 10px;
            color: #666;
            display: none;
        }
        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #666;
            border-radius: 50%;
            margin-right: 5px;
            animation: typing 1s infinite;
        }
        @keyframes typing {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .chart-container {
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #2196F3;
        }
        .voice-input-btn {
            position: absolute;
            right: 60px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #2196F3;
            cursor: pointer;
        }
        .message-time {
            font-size: 0.75em;
            opacity: 0.7;
            margin-top: 5px;
        }
        .data-table-container {
            overflow-x: auto;
            margin: 15px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            background: white;
        }
        .data-table th, .data-table td {
            padding: 12px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }
        .data-table th {
            background: #f5f5f5;
            font-weight: 600;
        }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-warning { color: #ffc107; }
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .chart-wrapper {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .ai-analysis {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #2196F3;
        }
        .command-category {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .safe-details, .pending-payments {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .card {
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card.success { border-left: 4px solid #28a745; }
        .card.warning { border-left: 4px solid #ffc107; }
        .card.danger { border-left: 4px solid #dc3545; }
        .budget-analysis {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .total-row {
            background: #f8f9fa;
            font-weight: bold;
        }
        .urgent { background: #fff3cd; }
        .warning { background: #fff8e1; }
        .help-menu {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .help-menu ul {
            list-style: none;
            padding-left: 0;
        }
        .help-menu li {
            margin: 10px 0;
            padding-left: 25px;
            position: relative;
        }
        .financial-analysis h3,
        .cash-flow-analysis h3,
        .budget-analysis h3 {
            color: #1976d2;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        .analysis-content {
            line-height: 1.6;
            color: #333;
        }
        .chart-title {
            font-size: 1.1em;
            color: #1976d2;
            margin-bottom: 15px;
            text-align: center;
        }
        .financial-analysis {
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.card {
    padding: 15px;
    border-radius: 8px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card.success { border-left: 4px solid #28a745; }
.card.warning { border-left: 4px solid #ffc107; }
.card.danger { border-left: 4px solid #dc3545; }

.data-table {
    width: 100%;
    margin: 15px 0;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

.data-table th {
    background: #f8f9fa;
}

.text-success { color: #28a745; }
.text-warning { color: #ffc107; }
.text-danger { color: #dc3545; }

.progress-bar {
    height: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
    margin: 10px 0;
}

.progress {
    height: 100%;
    transition: width 0.3s ease;
}

.progress.success { background: #28a745; }
.progress.warning { background: #ffc107; }
.progress.danger { background: #dc3545; }

.recommendations ul {
    list-style: none;
    padding: 0;
}

.recommendations li {
    margin: 10px 0;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.error-message {
    padding: 15px;
    background: #fff3cd;
    border: 1px solid #ffeeba;
    border-radius: 4px;
    color: #856404;
}
    </style>
</head>
<body>
    <?php require('_inc/header.php'); ?>

    <section class="content">
        <div class="title">
            <div class="left">
                <h1>AI Finans Asistanı</h1>
                <ul class="breadcrumb">
                    <li><a href="main.php">Anasayfa</a></li>
                    <li><a href="chatbot.php">AI Asistan</a></li>
                </ul>
            </div>
        </div>

        <div class="quick-stats">
            <?php
            // Günlük özet istatistikler
            $today = date('Y-m-d');
            $daily_income = $pia->get_row("SELECT SUM(price) as total FROM finances 
                WHERE u_id = $admin->id AND event_type = 1 AND DATE(adddate) = '$today'")->total ?? 0;
            $daily_expense = $pia->get_row("SELECT SUM(price) as total FROM finances 
                WHERE u_id = $admin->id AND event_type = 0 AND DATE(adddate) = '$today'")->total ?? 0;
            $pending_count = $pia->get_row("SELECT COUNT(*) as count FROM pending_payments 
                WHERE u_id = $admin->id AND status = 'pending'")->count ?? 0;
            $total_balance = $pia->get_row("SELECT SUM(balance) as total FROM safes 
                WHERE u_id = $admin->id AND status = 1")->total ?? 0;
            ?>
            <div class="stat-card">
                <i class="fas fa-arrow-up"></i>
                <h4>Günlük Gelir</h4>
                <p><?=number_format($daily_income, 2)?> TL</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-arrow-down"></i>
                <h4>Günlük Gider</h4>
                <p><?=number_format($daily_expense, 2)?> TL</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h4>Bekleyen Ödemeler</h4>
                <p><?=$pending_count?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-wallet"></i>
                <h4>Toplam Bakiye</h4>
                <p><?=number_format($total_balance, 2)?> TL</p>
            </div>
        </div>

        <div class="chat-container" id="chatContainer">
            <div class="chat-message bot-message">
                <div class="message-content">
                    Merhaba! Ben sizin AI Finans Asistanınızım. Size nasıl yardımcı olabilirim?<br><br>
                    💡 Aşağıdaki komutları kullanabilir veya doğal dille soru sorabilirsiniz:
                    <ul>
                        <li>📊 Genel analiz</li>
                        <li>💰 Nakit akışı</li>
                        <li>📈 Gelir analizi</li>
                        <li>📉 Gider analizi</li>
                        <li>💳 Borç analizi</li>
                        <li>⏰ Bekleyen ödemeler</li>
                        <li>📋 Bütçe analizi</li>
                    </ul>
                </div>
                <div class="message-time"><?=date('H:i')?></div>
            </div>
        </div>

        <div class="suggestions">
            <div class="suggestion-chip" onclick="useCommand('genel analiz')">📊 Genel Analiz</div>
            <div class="suggestion-chip" onclick="useCommand('nakit akışı')">💰 Nakit Akışı</div>
            <div class="suggestion-chip" onclick="useCommand('gelir analizi')">📈 Gelir Analizi</div>
            <div class="suggestion-chip" onclick="useCommand('gider analizi')">📉 Gider Analizi</div>
            <div class="suggestion-chip" onclick="useCommand('borç analizi')">💳 Borç Analizi</div>
            <div class="suggestion-chip" onclick="useCommand('ödemeler')">⏰ Ödemeler</div>
            <div class="suggestion-chip" onclick="useCommand('bütçe analizi')">📋 Bütçe</div>
            <div class="suggestion-chip" onclick="useCommand('yardım')">❓ Yardım</div>
        </div>

        <div class="chat-input">
            <div class="row">
                <div class="col-md-11">
                    <input type="text" id="userInput" class="form-control" 
                        placeholder="Mesajınızı yazın veya bir komut seçin...">
                    <button class="voice-input-btn" onclick="startVoiceInput()" title="Sesli Komut">
                        <i class="fas fa-microphone"></i>
                    </button>
                </div>
                <div class="col-md-1">
                    <button onclick="sendMessage()" class="btn-primary btn-block" title="Gönder">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <script>
        let recognition;
        if ('webkitSpeechRecognition' in window) {
            recognition = new webkitSpeechRecognition();
            recognition.continuous = false;
            recognition.lang = 'tr-TR';

            recognition.onresult = function(event) {
                const text = event.results[0][0].transcript;
                document.getElementById('userInput').value = text;
                sendMessage();
            };

            recognition.onerror = function(event) {
                console.error('Ses tanıma hatası:', event.error);
                alert('Ses tanıma sırasında bir hata oluştu.');
            };
        }

        function startVoiceInput() {
            if (recognition) {
                recognition.start();
            } else {
                alert('Ses tanıma özelliği tarayıcınızda desteklenmiyor.');
            }
        }

        function sendMessage() {
            var userInput = document.getElementById('userInput');
            var message = userInput.value.trim();
            
            if(message === '') return;

            addMessage(message, 'user');
            userInput.value = '';
            showTypingIndicator();

            $.ajax({
                url: '_ajax/_ajaxChatbot.php',
                type: 'POST',
                data: {message: message},
                success: function(response) {
                    hideTypingIndicator();
                    addMessage(response, 'bot');
                    
                    // Chart.js grafiklerini başlat
                    initializeCharts();
                },
                error: function(xhr, status, error) {
                    hideTypingIndicator();
                    addMessage('Üzgünüm, bir hata oluştu. Lütfen tekrar deneyin.', 'bot');
                    console.error('Ajax hatası:', error);
                }
            });
        }

        function useCommand(command) {
            document.getElementById('userInput').value = command;
            sendMessage();
        }

        function addMessage(message, type) {
            var chatContainer = document.getElementById('chatContainer');
            var messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message ' + (type === 'user' ? 'user-message' : 'bot-message');
            
            var contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = message;
            
            var timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
            timeDiv.textContent = new Date().toLocaleTimeString('tr-TR', {
                hour: '2-digit', 
                minute:'2-digit'
            });
            
            messageDiv.appendChild(contentDiv);
            messageDiv.appendChild(timeDiv);
            
            chatContainer.appendChild(messageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        function showTypingIndicator() {
            var indicator = document.createElement('div');
            indicator.className = 'typing-indicator bot-message';
            indicator.id = 'typingIndicator';
            indicator.innerHTML = 'AI düşünüyor<span></span><span></span><span></span>';
            document.getElementById('chatContainer').appendChild(indicator);
            document.getElementById('chatContainer').scrollTop = 
                document.getElementById('chatContainer').scrollHeight;
        }

        function hideTypingIndicator() {
            var indicator = document.getElementById('typingIndicator');
            if(indicator) indicator.remove();
        }

        function initializeCharts() {
            // Chart.js grafiklerini başlat
            document.querySelectorAll('[data-chart]').forEach(function(element) {
                if(element.chartInstance) {
                    element.chartInstance.destroy();
                }
                
                const chartData = JSON.parse(element.dataset.chart);
                const ctx = element.getContext('2d');
                
                element.chartInstance = new Chart(ctx, {
                    type: element.id.includes('trend') ? 'line' : 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: element.id.includes('trend') ? [
                            {
                                label: 'Gelir',
                                data: chartData.income,
                                borderColor: '#28a745',
                                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                tension: 0.4
                            },
                            {
                                label: 'Gider',
                                data: chartData.expense,
                                borderColor: '#dc3545',
                                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                tension: 0.4
                            }
                        ] : [{
                            data: chartData.data,
                            backgroundColor: [
                                '#4CAF50', '#2196F3', '#FFC107', '#E91E63',
                                '#9C27B0', '#00BCD4', '#FF5722', '#795548'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: element.id.includes('trend')
                            }
                        }
                    }
                });
            });
        }

        document.getElementById('userInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Sayfa yüklendiğinde chat container'ı en alta kaydır
        document.addEventListener('DOMContentLoaded', function() {
            var chatContainer = document.getElementById('chatContainer');
            chatContainer.scrollTop = chatContainer.scrollHeight;
        });
    </script>

    <?php require('_inc/footer.php'); ?>
</body>
</html>