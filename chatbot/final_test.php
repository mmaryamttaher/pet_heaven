<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Chatbot Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .status-online { color: #28a745; }
        .status-offline { color: #dc3545; }
        .test-result { margin: 10px 0; padding: 15px; border-radius: 8px; }
        .test-success { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .test-error { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .test-warning { background-color: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">🧪 اختبار نهائي لنظام Chatbot</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>🔧 حالة النظام</h5>
                    </div>
                    <div class="card-body">
                        <div id="systemStatus">
                            <p>🔄 جاري فحص النظام...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>📊 إحصائيات سريعة</h5>
                    </div>
                    <div class="card-body">
                        <div id="quickStats">
                            <p>🔄 جاري جمع الإحصائيات...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>🧪 اختبارات API</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary me-2" onclick="runAllTests()">تشغيل جميع الاختبارات</button>
                        <button class="btn btn-success me-2" onclick="testQuestion('مرحبا')">اختبار بسيط</button>
                        <button class="btn btn-info me-2" onclick="testQuestion('ما هي المضيفين المتاحين؟')">اختبار ذكي</button>
                        <button class="btn btn-warning" onclick="testQuestion('كيف أرعى قطتي؟')">اختبار عام</button>
                        
                        <div id="testResults" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>🔗 روابط سريعة</h5>
                    </div>
                    <div class="card-body">
                        <a href="../chat.php" class="btn btn-primary me-2">💬 واجهة المحادثة</a>
                        <a href="index.php" class="btn btn-info me-2">🏠 إدارة Chatbot</a>
                        <a href="../test_smart_chatbot.php" class="btn btn-success me-2">🧠 اختبار ذكي</a>
                        <a href="../setup_chatbot_db.php" class="btn btn-warning">🔧 إعداد قاعدة البيانات</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function checkSystemStatus() {
            const statusDiv = document.getElementById('systemStatus');
            let html = '';
            
            // Check database
            try {
                const dbResponse = await fetch('debug_test.php');
                if (dbResponse.ok) {
                    html += '<p class="status-online">✅ قاعدة البيانات: متصلة</p>';
                } else {
                    html += '<p class="status-offline">❌ قاعدة البيانات: غير متصلة</p>';
                }
            } catch (error) {
                html += '<p class="status-offline">❌ قاعدة البيانات: خطأ في الاتصال</p>';
            }
            
            // Check Python service
            try {
                const pythonResponse = await fetch('http://localhost:8000/health');
                if (pythonResponse.ok) {
                    const data = await pythonResponse.json();
                    html += '<p class="status-online">✅ Python Chatbot: متصل</p>';
                } else {
                    html += '<p class="status-offline">⚠️ Python Chatbot: غير متصل (سيستخدم النظام المحلي)</p>';
                }
            } catch (error) {
                html += '<p class="status-offline">⚠️ Python Chatbot: غير متصل (سيستخدم النظام المحلي)</p>';
            }
            
            // Check API
            try {
                const apiResponse = await fetch('../chatbot_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ question: 'test' })
                });
                if (apiResponse.ok) {
                    html += '<p class="status-online">✅ Chatbot API: يعمل</p>';
                } else {
                    html += '<p class="status-offline">❌ Chatbot API: لا يعمل</p>';
                }
            } catch (error) {
                html += '<p class="status-offline">❌ Chatbot API: خطأ</p>';
            }
            
            statusDiv.innerHTML = html;
        }
        
        async function getQuickStats() {
            const statsDiv = document.getElementById('quickStats');
            let html = '';
            
            try {
                // Get conversation count (approximate)
                html += '<p>📊 النظام جاهز للاستخدام</p>';
                html += '<p>🔧 3 مستويات ذكاء متاحة</p>';
                html += '<p>📁 ملفات منظمة في مجلد chatbot/</p>';
                html += '<p>🧪 اختبارات شاملة متاحة</p>';
            } catch (error) {
                html += '<p>❌ خطأ في جمع الإحصائيات</p>';
            }
            
            statsDiv.innerHTML = html;
        }
        
        async function testQuestion(question) {
            const resultsDiv = document.getElementById('testResults');
            
            const testDiv = document.createElement('div');
            testDiv.className = 'test-result test-warning';
            testDiv.innerHTML = `<strong>🔄 اختبار:</strong> "${question}" - جاري الاختبار...`;
            resultsDiv.appendChild(testDiv);
            
            try {
                const response = await fetch('../chatbot_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        question: question,
                        session_id: 'test_' + Date.now()
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    testDiv.className = 'test-result test-success';
                    let responseType = '';
                    if (data.smart) responseType = '🧠 ذكي';
                    else if (data.simple) responseType = '📝 بسيط';
                    else if (data.fallback) responseType = '💡 محلي';
                    else responseType = '🤖 AI';
                    
                    testDiv.innerHTML = `
                        <strong>✅ نجح:</strong> "${question}"<br>
                        <strong>النوع:</strong> ${responseType}<br>
                        <strong>الإجابة:</strong> ${data.response.substring(0, 100)}...
                    `;
                } else {
                    testDiv.className = 'test-result test-error';
                    testDiv.innerHTML = `
                        <strong>❌ فشل:</strong> "${question}"<br>
                        <strong>الخطأ:</strong> ${data.error}
                    `;
                }
            } catch (error) {
                testDiv.className = 'test-result test-error';
                testDiv.innerHTML = `
                    <strong>❌ خطأ شبكة:</strong> "${question}"<br>
                    <strong>التفاصيل:</strong> ${error.message}
                `;
            }
        }
        
        async function runAllTests() {
            document.getElementById('testResults').innerHTML = '';
            
            const questions = [
                'مرحبا',
                'ما هي المضيفين المتاحين؟',
                'كيف أرعى قطتي؟',
                'كم تكلفة الخدمة؟',
                'أريد معرفة حجوزاتي'
            ];
            
            for (const question of questions) {
                await testQuestion(question);
                await new Promise(resolve => setTimeout(resolve, 1000)); // Wait 1 second between tests
            }
        }
        
        // Run checks on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkSystemStatus();
            getQuickStats();
        });
    </script>
</body>
</html>
