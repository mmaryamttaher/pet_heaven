<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Chatbot API</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-box { border: 1px solid #ddd; padding: 20px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        button { padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🧪 اختبار Chatbot API</h1>
    
    <div class="test-box info">
        <h3>اختبار سريع للـ API</h3>
        <button onclick="testAPI('مرحبا')">اختبار: مرحبا</button>
        <button onclick="testAPI('ما هي المضيفين المتاحين؟')">اختبار: المضيفين</button>
        <button onclick="testAPI('كيف أرعى قطتي؟')">اختبار: رعاية القطط</button>
        <button onclick="testAPI('كم تكلفة الخدمة؟')">اختبار: الأسعار</button>
    </div>
    
    <div id="results"></div>

    <script>
        async function testAPI(question) {
            const resultsDiv = document.getElementById('results');
            
            // Add loading message
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'test-box info';
            loadingDiv.innerHTML = `<h4>🔄 اختبار: "${question}"</h4><p>جاري الاختبار...</p>`;
            resultsDiv.appendChild(loadingDiv);
            
            try {
                const response = await fetch('../chatbot_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        question: question,
                        session_id: 'test_' + Date.now()
                    })
                });
                
                const data = await response.json();
                
                // Remove loading message
                resultsDiv.removeChild(loadingDiv);
                
                // Add result
                const resultDiv = document.createElement('div');
                resultDiv.className = data.success ? 'test-box success' : 'test-box error';
                
                let html = `<h4>${data.success ? '✅' : '❌'} السؤال: "${question}"</h4>`;
                
                if (data.success) {
                    html += `<p><strong>الإجابة:</strong></p>`;
                    html += `<div style="background: white; padding: 10px; border-radius: 3px; margin: 10px 0;">${data.response.replace(/\n/g, '<br>')}</div>`;
                    
                    // Show response type
                    if (data.smart) {
                        html += `<span style="background: #28a745; color: white; padding: 3px 8px; border-radius: 10px; font-size: 12px;">🧠 ذكي</span> `;
                    }
                    if (data.fallback) {
                        html += `<span style="background: #6c757d; color: white; padding: 3px 8px; border-radius: 10px; font-size: 12px;">💡 محلي</span> `;
                    }
                    if (data.simple) {
                        html += `<span style="background: #17a2b8; color: white; padding: 3px 8px; border-radius: 10px; font-size: 12px;">📝 بسيط</span> `;
                    }
                    if (!data.fallback && !data.smart && !data.simple) {
                        html += `<span style="background: #007bff; color: white; padding: 3px 8px; border-radius: 10px; font-size: 12px;">🤖 AI</span> `;
                    }
                    
                    html += `<br><br><small>Session ID: ${data.session_id}</small>`;
                    html += `<br><small>Saved to DB: ${data.saved_to_db ? 'Yes' : 'No'}</small>`;
                } else {
                    html += `<p><strong>خطأ:</strong> ${data.error}</p>`;
                }
                
                html += `<br><br><details><summary>البيانات الكاملة</summary><pre>${JSON.stringify(data, null, 2)}</pre></details>`;
                
                resultDiv.innerHTML = html;
                resultsDiv.appendChild(resultDiv);
                
            } catch (error) {
                // Remove loading message
                resultsDiv.removeChild(loadingDiv);
                
                // Add error result
                const errorDiv = document.createElement('div');
                errorDiv.className = 'test-box error';
                errorDiv.innerHTML = `<h4>❌ خطأ في الشبكة</h4><p>السؤال: "${question}"</p><p>الخطأ: ${error.message}</p>`;
                resultsDiv.appendChild(errorDiv);
            }
        }
        
        // Test on page load
        document.addEventListener('DOMContentLoaded', function() {
            testAPI('مرحبا');
        });
    </script>
</body>
</html>
