<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

// Generate session ID for chat
$chat_session_id = 'chat_' . (isLoggedIn() ? $_SESSION['user_id'] . '_' : 'guest_') . date('Ymd');

// Get conversation history if exists
$conversation_history = [];
if (isset($_GET['session_id'])) {
    $chat_session_id = sanitizeInput($_GET['session_id']);
}

try {
    $history_query = "SELECT user_message, bot_response, created_at
                      FROM chat_conversations
                      WHERE session_id = ?
                      ORDER BY created_at ASC
                      LIMIT 50";
    $history_stmt = $db->prepare($history_query);
    $history_stmt->execute([$chat_session_id]);
    $conversation_history = $history_stmt->fetchAll();
} catch (Exception $e) {
    // Table might not exist yet
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat bot</title>
    <link rel="stylesheet" href="schat.css">
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* Additional styles for chat functionality */
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 10px;
            max-width: 80%;
            word-wrap: break-word;
        }
        .message.user {
            background-color: #491503;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .message.bot {
            background-color: #f0ebe9;
            color: #491503;
            margin-right: auto;
        }
        .typing-indicator {
            display: none;
            padding: 10px;
            font-style: italic;
            color: #666;
        }
        #conbox {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            margin: 10px;
        }
        .quick-questions {
            margin: 10px 20px;
            text-align: center;
        }
        .quick-question-btn {
            background: #f0ebe9;
            border: 1px solid #491503;
            border-radius: 15px;
            padding: 5px 10px;
            margin: 3px;
            font-size: 12px;
            cursor: pointer;
            color: #491503;
        }
        .quick-question-btn:hover {
            background: #491503;
            color: white;
        }
        #buttons {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        #buttons input {
            flex: 1;
        }
    </style>
</head>
<body>
    <!--start header-->
    <header class="navbar navbar-expand-lg" style="position: fixed;">
        <div class="container">
            <img src="imgs/Preview-removebg-preview.png" alt="logo" height="100" width="150">
            <a href="#menue" class="navbar-toggler collapsed" data-bs-toggle="collapse" data-bs-target="#menue" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </a>
            <nav class="collapse navbar-collapse justify-content-end" id="menue">
                <ul>
                    <li class="navbar-item">
                        <a href="index.php" class="nav-link">HOME</a>
                    </li>
                    <li class="navbar-item">
                        <a href="search.php" class="nav-link">Search</a>
                    </li>
                    <li class="navbar-item">
                        <a href="booking.php" class="nav-link">Booking</a>
                    </li>
                    <li class="navbar-item">
                        <a href="#contact" class="nav-link">Contact Us</a>
                    </li>
                    <li class="navbar-item">
                        <a href="user.php" class="nav-link"><i class="fa-regular fa-user"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <!--end header-->

    <!--start chat-->
    <section id="chat">
        <div id="conversation">
            <div id="conbox">
                <?php if (empty($conversation_history)): ?>
                <div class="message bot">
                    <strong>🐾 Welcome!</strong><br>
                    I'm your AI assistant specialized in pets. I can help you with:<br>
                    • Pet care and health<br>
                    • Nutrition and feeding<br>
                    • Behavior and training<br>
                    • Choosing the right pet<br><br>
                    Ask me anything about pets!
                </div>
                <?php else: ?>
                    <?php foreach ($conversation_history as $conv): ?>
                    <div class="message user">
                        <?php echo nl2br(htmlspecialchars($conv['user_message'])); ?>
                    </div>
                    <div class="message bot">
                        <?php echo nl2br(htmlspecialchars($conv['bot_response'])); ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="typing-indicator" id="typingIndicator">
                    Bot is typing...
                </div>
            </div>

            <div class="quick-questions">
                <small class="text-muted">Quick questions:</small><br>
                <button class="quick-question-btn" onclick="sendQuickQuestion('What are the best hosts available?')">Available Hosts</button>
                <button class="quick-question-btn" onclick="sendQuickQuestion('I want to know my bookings')">My Bookings</button>
                <button class="quick-question-btn" onclick="sendQuickQuestion('What services are available?')">Services</button>
                <button class="quick-question-btn" onclick="sendQuickQuestion('How much does the service cost?')">Prices</button>
                <button class="quick-question-btn" onclick="sendQuickQuestion('How do I care for my cat?')">Cat Care</button>
                <button class="quick-question-btn" onclick="sendQuickQuestion('What is the best food for dogs?')">Dog Food</button>
            </div>

            <div id="buttons">
                <input type="text" id="messageInput" placeholder="Write your message" onkeypress="handleKeyPress(event)">
                <button class="btn btn-light" onclick="toggleMicrophone()"><i class="fa-solid fa-microphone"></i></button>
                <button class="btn btn-light" onclick="sendMessage()"><i class="fa-regular fa-paper-plane"></i></button>
            </div>
        </div>
    </section>
    <!--end chat-->

    <!--start footer-->
    <footer id="contact">
        <img src="imgs/Preview-removebg-preview.png" alt="logo" height="100" width="150">
        <div class="icons">
            <a href=""><i class="fa-brands fa-facebook" style="color: #2958a8;"></i></a>
            <a href=""><i class="fa-brands fa-instagram" style="color: #e713a4;"></i></a>
            <a href=""><i class="fa-brands fa-whatsapp" style="color: #398f00;"></i></a>
            <a href="chat.php"><i class="fa-regular fa-comments"></i></a>
        </div>
    </footer>
    <!--end footer-->

    <script>
        let sessionId = '<?php echo $chat_session_id; ?>';

        function scrollToBottom() {
            const conbox = document.getElementById('conbox');
            conbox.scrollTop = conbox.scrollHeight;
        }

        function showTypingIndicator() {
            document.getElementById('typingIndicator').style.display = 'block';
            scrollToBottom();
        }

        function hideTypingIndicator() {
            document.getElementById('typingIndicator').style.display = 'none';
        }

        function addMessage(message, isUser = false) {
            const conbox = document.getElementById('conbox');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;
            messageDiv.innerHTML = message.replace(/\n/g, '<br>');

            conbox.appendChild(messageDiv);
            scrollToBottom();
        }

        function toggleMicrophone() {
            // Placeholder for microphone functionality
            alert('Microphone feature coming soon!');
        }

        async function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();

            if (!message) return;

            // Disable input
            messageInput.disabled = true;

            // Add user message to chat
            addMessage(message, true);
            messageInput.value = '';

            // Show typing indicator
            showTypingIndicator();

            try {
                const response = await fetch('chatbot_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        question: message,
                        session_id: sessionId
                    })
                });

                const data = await response.json();

                hideTypingIndicator();

                if (data.success) {
                    let response = data.response;
                    if (data.smart) {
                        response += '\n\n<small style="color: #28a745;">🧠 Smart response from database</small>';
                    } else if (data.simple) {
                        response += '\n\n<small style="color: #17a2b8;">📝 Simple response</small>';
                    } else if (data.fallback) {
                        response += '\n\n<small style="color: #666;">💡 Local system response</small>';
                    } else {
                        response += '\n\n<small style="color: #007bff;">🤖 AI response</small>';
                    }
                    addMessage(response);
                    sessionId = data.session_id;
                } else {
                    let errorMsg = 'Sorry, there was an error: ' + (data.error || 'Unknown error');
                    errorMsg += '\n\nPlease try again or contact support.';
                    addMessage(errorMsg);
                }
            } catch (error) {
                hideTypingIndicator();
                let errorMsg = 'Sorry, connection error.\n\n';
                errorMsg += 'Possible causes:\n';
                errorMsg += '• Internet connection issue\n';
                errorMsg += '• Server error\n';
                errorMsg += '• System configuration problem\n\n';
                errorMsg += 'Please try again or contact support.';
                addMessage(errorMsg);
                console.error('Error:', error);
            }

            // Re-enable input
            messageInput.disabled = false;
            messageInput.focus();
        }

        function sendQuickQuestion(question) {
            document.getElementById('messageInput').value = question;
            sendMessage();
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }

        // Auto-scroll to bottom on page load
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
            document.getElementById('messageInput').focus();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
