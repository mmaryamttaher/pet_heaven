<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Shop Chatbot System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        .hero-section { background: linear-gradient(135deg, #491503, #6d2408); color: white; padding: 60px 0; text-align: center; }
        .feature-card { background: white; border-radius: 15px; padding: 30px; margin: 20px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .feature-card:hover { transform: translateY(-5px); }
        .icon-large { font-size: 3rem; color: #491503; margin-bottom: 20px; }
        .btn-custom { background: #491503; color: white; border: none; padding: 12px 30px; border-radius: 25px; font-weight: bold; transition: all 0.3s; }
        .btn-custom:hover { background: #6d2408; color: white; transform: translateY(-2px); }
        .status-indicator { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-left: 8px; }
        .status-online { background: #28a745; }
        .status-offline { background: #dc3545; }
        .code-block { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 5px; padding: 15px; margin: 10px 0; font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="container">
            <h1><i class="fas fa-robot"></i> Pet Shop Chatbot System</h1>
            <p class="lead">نظام محادثة ذكي متخصص في الحيوانات الأليفة مع ربط قاعدة البيانات</p>
            <div class="mt-4">
                <span id="pythonStatus" class="badge bg-secondary">
                    <i class="fas fa-circle status-indicator status-offline"></i>
                    Python Service: Checking...
                </span>
                <span id="databaseStatus" class="badge bg-secondary ms-2">
                    <i class="fas fa-circle status-indicator status-offline"></i>
                    Database: Checking...
                </span>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-brain icon-large"></i>
                    <h4>ذكاء اصطناعي متقدم</h4>
                    <p>يستخدم Google Gemini AI لفهم الأسئلة والإجابة بذكاء عن كل ما يتعلق بالحيوانات الأليفة</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-database icon-large"></i>
                    <h4>مربوط بقاعدة البيانات</h4>
                    <p>يستخرج معلومات حقيقية عن المضيفين والحجوزات والأسعار من قاعدة البيانات</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-layers icon-large"></i>
                    <h4>نظام متدرج</h4>
                    <p>ثلاث مستويات ذكاء: AI متقدم، نظام ذكي، ونظام أساسي كـ fallback</p>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <h3><i class="fas fa-cogs"></i> ملفات النظام</h3>
                <div class="list-group">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fab fa-python text-primary"></i>
                            <strong>main.py</strong>
                            <small class="text-muted d-block">خادم الذكاء الاصطناعي الرئيسي</small>
                        </div>
                        <span class="badge bg-primary">Python</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fab fa-php text-info"></i>
                            <strong>smart_chatbot.php</strong>
                            <small class="text-muted d-block">النظام الذكي المربوط بقاعدة البيانات</small>
                        </div>
                        <span class="badge bg-info">PHP</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fab fa-php text-success"></i>
                            <strong>simple_chatbot.php</strong>
                            <small class="text-muted d-block">النظام البسيط للإجابات الأساسية</small>
                        </div>
                        <span class="badge bg-success">PHP</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-exchange-alt text-warning"></i>
                            <strong>api_handler.php</strong>
                            <small class="text-muted d-block">واجهة API للتواصل بين PHP و Python</small>
                        </div>
                        <span class="badge bg-warning">API</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <h3><i class="fas fa-play-circle"></i> التشغيل السريع</h3>
                
                <h5>1. تثبيت المتطلبات:</h5>
                <div class="code-block">
                    pip install -r requirements.txt
                </div>
                
                <h5>2. تشغيل الخادم:</h5>
                <div class="code-block">
                    python main.py
                </div>
                
                <h5>أو استخدم الملف المساعد:</h5>
                <div class="code-block">
                    start_chatbot.bat
                </div>
                
                <div class="mt-3">
                    <button class="btn btn-custom" onclick="checkServices()">
                        <i class="fas fa-sync-alt"></i> فحص الخدمات
                    </button>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <h3><i class="fas fa-link"></i> روابط مفيدة</h3>
                <div class="row">
                    <div class="col-md-3">
                        <a href="../chat.php" class="btn btn-custom w-100 mb-2">
                            <i class="fas fa-comments"></i> واجهة المحادثة
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="http://localhost:8000/docs" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-book"></i> API Documentation
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="../test_smart_chatbot.php" class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-vial"></i> اختبار النظام الذكي
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="../setup_chatbot_db.php" class="btn btn-outline-warning w-100 mb-2">
                            <i class="fas fa-database"></i> إعداد قاعدة البيانات
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <div class="feature-card">
                    <h3><i class="fas fa-question-circle"></i> أسئلة للاختبار</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>أسئلة ذكية (تستخدم قاعدة البيانات):</h5>
                            <ul>
                                <li>"ما هي المضيفين المتاحين؟"</li>
                                <li>"أريد معرفة حجوزاتي"</li>
                                <li>"كم تكلفة الخدمة؟"</li>
                                <li>"ما هي المناطق المتاحة؟"</li>
                                <li>"أريد معرفة معلومات حيواناتي"</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>أسئلة عامة عن الحيوانات:</h5>
                            <ul>
                                <li>"كيف أرعى قطتي؟"</li>
                                <li>"ما هو أفضل طعام للكلاب؟"</li>
                                <li>"كيف أدرب كلبي؟"</li>
                                <li>"قطتي مريضة ماذا أفعل؟"</li>
                                <li>"كيف أعتني بالطيور؟"</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function checkServices() {
            // Check Python service
            try {
                const response = await fetch('http://localhost:8000/health');
                const data = await response.json();
                
                if (data.status === 'healthy') {
                    document.getElementById('pythonStatus').innerHTML = 
                        '<i class="fas fa-circle status-indicator status-online"></i>Python Service: Online';
                    document.getElementById('pythonStatus').className = 'badge bg-success';
                } else {
                    throw new Error('Service unhealthy');
                }
            } catch (error) {
                document.getElementById('pythonStatus').innerHTML = 
                    '<i class="fas fa-circle status-indicator status-offline"></i>Python Service: Offline';
                document.getElementById('pythonStatus').className = 'badge bg-danger';
            }
            
            // Check database through PHP
            try {
                const response = await fetch('../test_chat_quick.php');
                if (response.ok) {
                    document.getElementById('databaseStatus').innerHTML = 
                        '<i class="fas fa-circle status-indicator status-online"></i>Database: Connected';
                    document.getElementById('databaseStatus').className = 'badge bg-success';
                } else {
                    throw new Error('Database connection failed');
                }
            } catch (error) {
                document.getElementById('databaseStatus').innerHTML = 
                    '<i class="fas fa-circle status-indicator status-offline"></i>Database: Error';
                document.getElementById('databaseStatus').className = 'badge bg-danger';
            }
        }
        
        // Check services on page load
        document.addEventListener('DOMContentLoaded', checkServices);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
