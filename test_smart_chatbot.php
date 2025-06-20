<?php
require_once 'config/config.php';

echo "<h2>Smart Chatbot Test - Database Integration</h2>";
echo "<p><strong>اختبار النظام الذكي المربوط بقاعدة البيانات...</strong></p>";

// Test questions that should use database data
$smart_test_questions = [
    'ما هي المضيفين المتاحين؟',
    'أريد معرفة حجوزاتي',
    'ما هي الخدمات المتاحة؟',
    'كم تكلفة الخدمة؟',
    'ما هي المناطق المتاحة؟',
    'أريد معرفة معلومات حيواناتي',
    'معلومات حسابي',
    'أسعار الاستضافة'
];

echo "<h3>اختبار الأسئلة الذكية:</h3>";

foreach ($smart_test_questions as $question) {
    echo "<div style='border: 1px solid #ddd; margin: 15px 0; padding: 20px; border-radius: 8px; background: #f9f9f9;'>";
    echo "<h4 style='color: #491503; margin-bottom: 10px;'>📝 السؤال:</h4>";
    echo "<p style='font-size: 16px; margin-bottom: 15px;'>" . htmlspecialchars($question) . "</p>";
    
    // Call the smart chatbot API
    $url = 'http://localhost/pets_shop/chatbot_api.php';
    $data = json_encode([
        'question' => $question,
        'session_id' => 'smart_test_' . time() . '_' . rand(1000, 9999)
    ]);
    
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => $data,
            'timeout' => 15
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result !== FALSE) {
        $response = json_decode($result, true);
        if ($response && $response['success']) {
            echo "<h4 style='color: #28a745; margin-bottom: 10px;'>🤖 الإجابة:</h4>";
            echo "<div style='background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745; margin: 10px 0; line-height: 1.6;'>";
            echo nl2br(htmlspecialchars($response['response']));
            echo "</div>";
            
            // Show response type
            echo "<div style='margin-top: 10px;'>";
            if (isset($response['smart']) && $response['smart']) {
                echo "<span style='background: #28a745; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;'>🧠 إجابة ذكية من قاعدة البيانات</span>";
            } elseif (isset($response['fallback']) && $response['fallback']) {
                echo "<span style='background: #6c757d; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;'>💡 إجابة محلية</span>";
            } else {
                echo "<span style='background: #007bff; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;'>🤖 ذكاء اصطناعي</span>";
            }
            
            if (isset($response['saved_to_db']) && $response['saved_to_db']) {
                echo " <span style='background: #17a2b8; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; margin-left: 5px;'>💾 محفوظ</span>";
            }
            
            if (isset($response['user_logged_in']) && $response['user_logged_in']) {
                echo " <span style='background: #ffc107; color: black; padding: 4px 8px; border-radius: 12px; font-size: 12px; margin-left: 5px;'>👤 مسجل دخول</span>";
            }
            echo "</div>";
            
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;'>";
            echo "<strong>خطأ:</strong> " . ($response['error'] ?? 'خطأ غير معروف');
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;'>";
        echo "<strong>خطأ في الاتصال بالـ API</strong>";
        echo "</div>";
    }
    
    echo "</div>";
}

// Test database connectivity
echo "<hr>";
echo "<h3>اختبار الاتصال بقاعدة البيانات:</h3>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ الاتصال بقاعدة البيانات ناجح</p>";
        
        // Check important tables
        $tables_to_check = [
            'hosts' => 'المضيفين',
            'hotels' => 'الفنادق', 
            'bookings' => 'الحجوزات',
            'pets' => 'الحيوانات الأليفة',
            'users' => 'المستخدمين'
        ];
        
        echo "<h4>حالة الجداول:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>الجدول</th><th>الحالة</th><th>عدد السجلات</th><th>عينة من البيانات</th>";
        echo "</tr>";
        
        foreach ($tables_to_check as $table => $arabic_name) {
            echo "<tr>";
            echo "<td>$arabic_name ($table)</td>";
            
            // Check if table exists
            $check_query = "SHOW TABLES LIKE '$table'";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute();
            $exists = $check_stmt->fetch();
            
            if ($exists) {
                echo "<td style='color: green;'>موجود</td>";
                
                // Count records
                $count_query = "SELECT COUNT(*) as count FROM $table";
                $count_stmt = $db->prepare($count_query);
                $count_stmt->execute();
                $count = $count_stmt->fetch()['count'];
                echo "<td>$count</td>";
                
                // Show sample data
                if ($count > 0) {
                    $sample_query = "SELECT * FROM $table LIMIT 1";
                    $sample_stmt = $db->prepare($sample_query);
                    $sample_stmt->execute();
                    $sample = $sample_stmt->fetch();
                    
                    $sample_text = "";
                    if ($table == 'hosts' || $table == 'hotels') {
                        $sample_text = ($sample['name'] ?? 'N/A') . " - " . ($sample['location'] ?? 'N/A');
                    } elseif ($table == 'users') {
                        $sample_text = ($sample['name'] ?? 'N/A') . " - " . ($sample['email'] ?? 'N/A');
                    } elseif ($table == 'bookings') {
                        $sample_text = "حجز #" . ($sample['id'] ?? 'N/A') . " - " . ($sample['status'] ?? 'N/A');
                    } elseif ($table == 'pets') {
                        $sample_text = ($sample['name'] ?? 'N/A') . " - " . ($sample['type'] ?? 'N/A');
                    }
                    echo "<td>$sample_text</td>";
                } else {
                    echo "<td style='color: orange;'>لا توجد بيانات</td>";
                }
            } else {
                echo "<td style='color: red;'>غير موجود</td>";
                echo "<td>-</td>";
                echo "<td>-</td>";
            }
            
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>✗ فشل الاتصال بقاعدة البيانات</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ خطأ في قاعدة البيانات: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>مميزات النظام الذكي الجديد:</h3>";
echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4 style='color: #0066cc;'>🧠 الذكاء المتقدم:</h4>";
echo "<ul>";
echo "<li><strong>تحليل النوايا:</strong> يفهم نوع السؤال (حجوزات، مضيفين، أسعار، إلخ)</li>";
echo "<li><strong>بيانات حقيقية:</strong> يستخرج المعلومات من قاعدة البيانات الفعلية</li>";
echo "<li><strong>إجابات شخصية:</strong> يعرض معلومات المستخدم الخاصة (حجوزات، حيوانات)</li>";
echo "<li><strong>إحصائيات حية:</strong> أسعار ومواقع محدثة من قاعدة البيانات</li>";
echo "<li><strong>ذكاء تدريجي:</strong> Python AI → Smart PHP → Simple PHP</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f0f8f0; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4 style='color: #28a745;'>📊 أنواع الأسئلة المدعومة:</h4>";
echo "<ul>";
echo "<li><strong>معلومات المضيفين:</strong> أسماء، مواقع، أسعار، تقييمات</li>";
echo "<li><strong>حجوزات المستخدم:</strong> حجوزات سابقة وحالية</li>";
echo "<li><strong>حيوانات المستخدم:</strong> معلومات الحيوانات المسجلة</li>";
echo "<li><strong>الخدمات:</strong> قائمة الخدمات المتاحة</li>";
echo "<li><strong>الأسعار:</strong> إحصائيات الأسعار الحقيقية</li>";
echo "<li><strong>المواقع:</strong> المناطق المتاحة للخدمة</li>";
echo "<li><strong>معلومات الحساب:</strong> بيانات المستخدم الشخصية</li>";
echo "</ul>";
echo "</div>";

echo "<br>";
echo "<p><strong>روابط مفيدة:</strong></p>";
echo "<a href='chat.php' style='margin-right: 10px; padding: 8px 16px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🗨️ جرب المحادثة الذكية</a>";
echo "<a href='test_chat_quick.php' style='margin-right: 10px; padding: 8px 16px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;'>⚡ اختبار سريع</a>";
echo "<a href='setup_chatbot_db.php' style='margin-right: 10px; padding: 8px 16px; background-color: #ffc107; color: black; text-decoration: none; border-radius: 5px;'>🔧 إعداد قاعدة البيانات</a>";
echo "<a href='index.php' style='padding: 8px 16px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>🏠 الصفحة الرئيسية</a>";
?>
