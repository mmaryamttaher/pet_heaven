<?php
// No need to require config here as it's already loaded by the calling file

class SmartChatbot {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function getSmartResponse($question, $user_id = null) {
        $question = strtolower(trim($question));
        
        // تحليل السؤال لمعرفة النوع
        $intent = $this->analyzeIntent($question);
        
        switch ($intent) {
            case 'hosts_info':
                return $this->getHostsInfo($question);
            case 'booking_info':
                return $this->getBookingInfo($question, $user_id);
            case 'user_pets':
                return $this->getUserPetsInfo($question, $user_id);
            case 'services':
                return $this->getServicesInfo($question);
            case 'prices':
                return $this->getPricesInfo($question);
            case 'locations':
                return $this->getLocationsInfo($question);
            case 'user_account':
                return $this->getUserAccountInfo($question, $user_id);
            default:
                return $this->getGeneralPetAdvice($question);
        }
    }
    
    private function analyzeIntent($question) {
        $intents = [
            'hosts_info' => ['مضيف', 'مضيفين', 'فندق', 'فنادق', 'مكان', 'أماكن', 'استضافة'],
            'booking_info' => ['حجز', 'حجوزات', 'موعد', 'مواعيد', 'حجزي', 'حجوزاتي'],
            'user_pets' => ['حيواني', 'حيواناتي', 'قطتي', 'كلبي', 'طيري'],
            'services' => ['خدمة', 'خدمات', 'نقدم', 'نوفر', 'متاح'],
            'prices' => ['سعر', 'أسعار', 'تكلفة', 'كلفة', 'فلوس', 'مال'],
            'locations' => ['مكان', 'أماكن', 'موقع', 'مواقع', 'منطقة', 'مناطق'],
            'user_account' => ['حسابي', 'بياناتي', 'معلوماتي', 'بروفايل']
        ];
        
        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($question, $keyword) !== false) {
                    return $intent;
                }
            }
        }
        
        return 'general';
    }
    
    private function getHostsInfo($question) {
        try {
            // البحث في جدول المضيفين/الفنادق
            $query = "SELECT * FROM hosts ORDER BY rating DESC LIMIT 5";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $hosts = $stmt->fetchAll();
            
            if (empty($hosts)) {
                // جرب جدول الفنادق
                $query = "SELECT * FROM hotels ORDER BY rating DESC LIMIT 5";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $hosts = $stmt->fetchAll();
            }
            
            if (empty($hosts)) {
                return "عذراً، لا توجد معلومات عن المضيفين حالياً في قاعدة البيانات. يمكنك التواصل معنا لمزيد من المعلومات.";
            }
            
            $response = "🏠 إليك أفضل المضيفين المتاحين:\n\n";
            foreach ($hosts as $host) {
                $response .= "📍 **" . $host['name'] . "**\n";
                $response .= "📍 الموقع: " . ($host['location'] ?? 'غير محدد') . "\n";
                $response .= "💰 السعر: " . ($host['price_per_day'] ?? 'غير محدد') . " جنيه/يوم\n";
                if (isset($host['rating']) && $host['rating'] > 0) {
                    $response .= "⭐ التقييم: " . $host['rating'] . "/5\n";
                }
                $response .= "\n";
            }
            
            $response .= "للحجز، يمكنك زيارة صفحة البحث أو التواصل معنا مباشرة!";
            return $response;
            
        } catch (Exception $e) {
            return "عذراً، حدث خطأ في استرجاع معلومات المضيفين. يرجى المحاولة مرة أخرى.";
        }
    }
    
    private function getBookingInfo($question, $user_id) {
        if (!$user_id) {
            return "لعرض معلومات حجوزاتك، يرجى تسجيل الدخول أولاً. يمكنك تسجيل الدخول من هنا: /login.php";
        }
        
        try {
            $query = "SELECT b.*, h.name as host_name, h.location 
                      FROM bookings b 
                      LEFT JOIN hosts h ON b.host_id = h.id 
                      WHERE b.user_id = ? 
                      ORDER BY b.created_at DESC 
                      LIMIT 5";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$user_id]);
            $bookings = $stmt->fetchAll();
            
            if (empty($bookings)) {
                return "📅 لا توجد حجوزات مسجلة باسمك حتى الآن.\n\nيمكنك إجراء حجز جديد من خلال:\n• صفحة البحث\n• تصفح المضيفين المتاحين\n• التواصل معنا مباشرة";
            }
            
            $response = "📋 **حجوزاتك:**\n\n";
            foreach ($bookings as $booking) {
                $response .= "🏠 " . ($booking['host_name'] ?? 'مضيف غير محدد') . "\n";
                $response .= "📅 من: " . ($booking['start_date'] ?? $booking['check_in_date'] ?? 'غير محدد');
                $response .= " إلى: " . ($booking['end_date'] ?? $booking['check_out_date'] ?? 'غير محدد') . "\n";
                $response .= "📊 الحالة: " . $this->translateStatus($booking['status']) . "\n";
                $response .= "💰 المبلغ: " . ($booking['total_price'] ?? $booking['total_amount'] ?? 0) . " جنيه\n\n";
            }
            
            $response .= "لمزيد من التفاصيل، يمكنك زيارة صفحة حجوزاتي.";
            return $response;
            
        } catch (Exception $e) {
            return "عذراً، حدث خطأ في استرجاع معلومات حجوزاتك. يرجى المحاولة مرة أخرى.";
        }
    }
    
    private function getUserPetsInfo($question, $user_id) {
        if (!$user_id) {
            return "لعرض معلومات حيواناتك الأليفة، يرجى تسجيل الدخول أولاً.";
        }
        
        try {
            $query = "SELECT * FROM pets WHERE user_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$user_id]);
            $pets = $stmt->fetchAll();
            
            if (empty($pets)) {
                return "🐾 لم تقم بإضافة معلومات حيواناتك الأليفة بعد.\n\nيمكنك إضافة معلومات حيوانك الأليف من خلال:\n• صفحة الملف الشخصي\n• قسم إدارة الحيوانات الأليفة\n\nهذا سيساعدنا في تقديم خدمة أفضل!";
            }
            
            $response = "🐾 **حيواناتك الأليفة:**\n\n";
            foreach ($pets as $pet) {
                $response .= "🐕 **" . $pet['name'] . "**\n";
                $response .= "📝 النوع: " . $this->translatePetType($pet['type']) . "\n";
                if (!empty($pet['breed'])) {
                    $response .= "🏷️ السلالة: " . $pet['breed'] . "\n";
                }
                if (!empty($pet['age'])) {
                    $response .= "🎂 العمر: " . $pet['age'] . " سنة\n";
                }
                if (!empty($pet['special_needs'])) {
                    $response .= "⚠️ احتياجات خاصة: " . $pet['special_needs'] . "\n";
                }
                $response .= "\n";
            }
            
            $response .= "💡 نصيحة: تأكد من تحديث معلومات حيواناتك بانتظام لضمان أفضل رعاية!";
            return $response;
            
        } catch (Exception $e) {
            return "عذراً، حدث خطأ في استرجاع معلومات حيواناتك. يرجى المحاولة مرة أخرى.";
        }
    }
    
    private function getServicesInfo($question) {
        $services = [
            "🏠 **استضافة الحيوانات الأليفة**" => "نوفر أماكن آمنة ومريحة لإقامة حيوانك الأليف",
            "🍽️ **التغذية والرعاية**" => "وجبات منتظمة ورعاية صحية متخصصة",
            "🎾 **اللعب والتمارين**" => "أنشطة يومية مناسبة لنوع وعمر حيوانك",
            "🏥 **الرعاية الطبية**" => "متابعة صحية وإسعافات أولية عند الحاجة",
            "📱 **تحديثات مستمرة**" => "صور وتقارير يومية عن حالة حيوانك",
            "🚗 **خدمة النقل**" => "إمكانية توصيل واستلام حيوانك (حسب المنطقة)"
        ];
        
        $response = "🌟 **خدماتنا المتاحة:**\n\n";
        foreach ($services as $service => $description) {
            $response .= $service . "\n" . $description . "\n\n";
        }
        
        $response .= "للحجز أو الاستفسار عن أي خدمة، تواصل معنا!";
        return $response;
    }
    
    private function getPricesInfo($question) {
        try {
            $query = "SELECT AVG(price_per_day) as avg_price, MIN(price_per_day) as min_price, MAX(price_per_day) as max_price FROM hosts WHERE price_per_day > 0";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $prices = $stmt->fetch();
            
            if (!$prices || !$prices['avg_price']) {
                // جرب جدول الفنادق
                $query = "SELECT AVG(price_per_day) as avg_price, MIN(price_per_day) as min_price, MAX(price_per_day) as max_price FROM hotels WHERE price_per_day > 0";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $prices = $stmt->fetch();
            }
            
            $response = "💰 **معلومات الأسعار:**\n\n";
            
            if ($prices && $prices['avg_price']) {
                $response .= "📊 متوسط السعر: " . round($prices['avg_price']) . " جنيه/يوم\n";
                $response .= "💵 أقل سعر: " . $prices['min_price'] . " جنيه/يوم\n";
                $response .= "💎 أعلى سعر: " . $prices['max_price'] . " جنيه/يوم\n\n";
            }
            
            $response .= "📝 **العوامل المؤثرة على السعر:**\n";
            $response .= "• نوع الحيوان الأليف\n";
            $response .= "• مدة الإقامة\n";
            $response .= "• الخدمات الإضافية المطلوبة\n";
            $response .= "• موقع المضيف\n";
            $response .= "• موسم الحجز\n\n";
            $response .= "للحصول على عرض سعر دقيق، يرجى التواصل معنا مع تفاصيل احتياجاتك!";
            
            return $response;
            
        } catch (Exception $e) {
            return "💰 أسعارنا تنافسية وتختلف حسب نوع الخدمة ومدة الإقامة. للحصول على عرض سعر مخصص، يرجى التواصل معنا!";
        }
    }
    
    private function getLocationsInfo($question) {
        try {
            $query = "SELECT DISTINCT location, COUNT(*) as count FROM hosts WHERE location IS NOT NULL AND location != '' GROUP BY location ORDER BY count DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $locations = $stmt->fetchAll();
            
            if (empty($locations)) {
                // جرب جدول الفنادق
                $query = "SELECT DISTINCT location, COUNT(*) as count FROM hotels WHERE location IS NOT NULL AND location != '' GROUP BY location ORDER BY count DESC";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $locations = $stmt->fetchAll();
            }
            
            $response = "📍 **المناطق المتاحة للخدمة:**\n\n";
            
            if (!empty($locations)) {
                foreach ($locations as $location) {
                    $response .= "🏙️ " . $location['location'] . " (" . $location['count'] . " مضيف)\n";
                }
                $response .= "\n";
            } else {
                $response .= "نخدم معظم مناطق القاهرة الكبرى والجيزة.\n\n";
            }
            
            $response .= "📞 للتأكد من توفر الخدمة في منطقتك، يرجى التواصل معنا مع ذكر المنطقة المطلوبة.";
            
            return $response;
            
        } catch (Exception $e) {
            return "📍 نخدم معظم مناطق القاهرة والجيزة. للتأكد من توفر الخدمة في منطقتك، يرجى التواصل معنا!";
        }
    }
    
    private function getUserAccountInfo($question, $user_id) {
        if (!$user_id) {
            return "لعرض معلومات حسابك، يرجى تسجيل الدخول أولاً.";
        }
        
        try {
            $query = "SELECT name, email, phone, created_at FROM users WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return "عذراً، لم أتمكن من العثور على معلومات حسابك.";
            }
            
            $response = "👤 **معلومات حسابك:**\n\n";
            $response .= "📝 الاسم: " . $user['name'] . "\n";
            $response .= "📧 البريد الإلكتروني: " . $user['email'] . "\n";
            if (!empty($user['phone'])) {
                $response .= "📱 الهاتف: " . $user['phone'] . "\n";
            }
            $response .= "📅 تاريخ التسجيل: " . date('Y-m-d', strtotime($user['created_at'])) . "\n\n";
            
            $response .= "لتعديل معلومات حسابك، يمكنك زيارة صفحة الملف الشخصي.";
            
            return $response;
            
        } catch (Exception $e) {
            return "عذراً، حدث خطأ في استرجاع معلومات حسابك. يرجى المحاولة مرة أخرى.";
        }
    }
    
    private function getGeneralPetAdvice($question) {
        // نفس الإجابات العامة من simple_chatbot.php
        return getEnhancedChatbotResponse($question);
    }
    
    private function translateStatus($status) {
        $statuses = [
            'pending' => 'في الانتظار',
            'confirmed' => 'مؤكد',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي'
        ];
        return $statuses[$status] ?? $status;
    }
    
    private function translatePetType($type) {
        $types = [
            'dog' => 'كلب',
            'cat' => 'قطة',
            'bird' => 'طائر',
            'rabbit' => 'أرنب',
            'other' => 'آخر'
        ];
        return $types[$type] ?? $type;
    }
}
?>
