<?php
require_once 'config/config.php';

echo "<h2>Quick Chat Test</h2>";

// Test the chatbot API directly
$test_questions = [
    'مرحبا',
    'كيف أرعى قطتي؟',
    'ما هو أفضل طعام للكلاب؟',
    'كيف أدرب كلبي؟',
    'قطتي مريضة ماذا أفعل؟'
];

echo "<h3>Testing Chatbot API:</h3>";

foreach ($test_questions as $question) {
    echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px;'>";
    echo "<strong>السؤال:</strong> " . htmlspecialchars($question) . "<br><br>";
    
    // Call the API
    $url = 'http://localhost/pets_shop/chatbot_api.php';
    $data = json_encode([
        'question' => $question,
        'session_id' => 'test_session_' . time()
    ]);
    
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => $data,
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result !== FALSE) {
        $response = json_decode($result, true);
        if ($response && $response['success']) {
            echo "<strong>الإجابة:</strong><br>";
            echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 3px; margin: 5px 0;'>";
            echo nl2br(htmlspecialchars($response['response']));
            echo "</div>";
            
            if (isset($response['fallback']) && $response['fallback']) {
                echo "<small style='color: #666;'>📝 إجابة من النظام المحلي (PHP)</small><br>";
            } else {
                echo "<small style='color: #28a745;'>🤖 إجابة من الذكاء الاصطناعي (Python)</small><br>";
            }
            
            if (isset($response['saved_to_db']) && $response['saved_to_db']) {
                echo "<small style='color: #007bff;'>💾 تم حفظ المحادثة في قاعدة البيانات</small><br>";
            }
        } else {
            echo "<strong style='color: red;'>خطأ:</strong> " . ($response['error'] ?? 'خطأ غير معروف');
        }
    } else {
        echo "<strong style='color: red;'>خطأ في الاتصال بالـ API</strong>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h3>نصائح لحل المشاكل:</h3>";
echo "<ul>";
echo "<li><strong>إذا كانت الإجابات من النظام المحلي:</strong> هذا يعني أن خدمة Python غير متاحة، لكن النظام يعمل بشكل أساسي</li>";
echo "<li><strong>لتشغيل الذكاء الاصطناعي:</strong> افتح terminal وشغل: <code>python chatbot_main.py</code></li>";
echo "<li><strong>تأكد من تثبيت المكتبات:</strong> <code>pip install -r requirements.txt</code></li>";
echo "<li><strong>تحقق من المنفذ:</strong> تأكد أن المنفذ 8000 متاح</li>";
echo "</ul>";

echo "<br>";
echo "<p><strong>روابط مفيدة:</strong></p>";
echo "<a href='chat.php' style='margin-right: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 3px;'>جرب المحادثة</a>";
echo "<a href='test_chatbot_connection.php' style='margin-right: 10px; padding: 5px 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 3px;'>اختبار شامل</a>";
echo "<a href='setup_chatbot_db.php' style='margin-right: 10px; padding: 5px 10px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 3px;'>إعداد قاعدة البيانات</a>";
?>
