# Pet Shop Chatbot System

نظام محادثة ذكي متخصص في الحيوانات الأليفة مع ربط قاعدة البيانات

## 📁 محتويات المجلد

### 🐍 Python Files
- **`main.py`** - الخادم الرئيسي للذكاء الاصطناعي (FastAPI + Gemini AI)
- **`requirements.txt`** - مكتبات Python المطلوبة

### 🐘 PHP Files
- **`api_handler.php`** - واجهة API للتواصل بين PHP و Python
- **`smart_chatbot.php`** - النظام الذكي المربوط بقاعدة البيانات
- **`simple_chatbot.php`** - النظام البسيط للإجابات الأساسية

### 🧪 Testing Files
- **`debug_test.php`** - تشخيص مشاكل النظام
- **`test_api.php`** - اختبار سريع للـ API
- **`final_test.php`** - اختبار شامل ونهائي

### 🚀 Setup & Management Files
- **`start_chatbot.bat`** - ملف تشغيل سريع للـ chatbot
- **`index.php`** - صفحة إدارة النظام
- **`README.md`** - هذا الملف

## 🔧 طريقة التشغيل

### 1. تثبيت المتطلبات
```bash
pip install -r requirements.txt
```

### 2. تشغيل الخادم
```bash
python main.py
```

أو استخدم الملف المساعد:
```bash
start_chatbot.bat
```

### 3. اختبار النظام
- **Health Check:** http://localhost:8000/health
- **API Documentation:** http://localhost:8000/docs
- **Chat Interface:** http://localhost/pets_shop/chat.php

## 🧠 مستويات الذكاء

### 1️⃣ Python + Gemini AI (الأفضل)
- ذكاء اصطناعي متقدم من Google
- فهم عميق للسياق
- إجابات طبيعية ومفصلة

### 2️⃣ Smart PHP Chatbot (ذكي)
- مربوط بقاعدة البيانات
- يستخرج معلومات حقيقية
- إجابات شخصية للمستخدمين

### 3️⃣ Simple PHP Chatbot (أساسي)
- إجابات مبرمجة مسبقاً
- يعمل دائماً كـ fallback
- يغطي الأسئلة الأساسية

## 📊 أنواع الأسئلة المدعومة

### 🏠 معلومات المضيفين
- "ما هي المضيفين المتاحين؟"
- أسماء، مواقع، أسعار، تقييمات

### 📅 حجوزات المستخدم  
- "أريد معرفة حجوزاتي"
- حجوزات سابقة وحالية

### 🐾 حيوانات المستخدم
- "أريد معرفة معلومات حيواناتي"
- معلومات الحيوانات المسجلة

### 💰 معلومات الأسعار
- "كم تكلفة الخدمة؟"
- إحصائيات الأسعار الحقيقية

### 📍 المواقع المتاحة
- "ما هي المناطق المتاحة؟"
- قائمة المناطق وعدد المضيفين

### 🛠️ الخدمات
- "ما هي الخدمات المتاحة؟"
- قائمة شاملة بالخدمات

### 👤 معلومات الحساب
- "معلومات حسابي"
- بيانات المستخدم الشخصية

## 🔗 API Endpoints

### Python FastAPI
- **POST** `/ask` - إرسال سؤال للـ chatbot
- **GET** `/health` - فحص حالة الخادم
- **GET** `/history/{session_id}` - تاريخ المحادثات
- **POST** `/reset/{session_id}` - إعادة تعيين الجلسة

### PHP API Handler
- **POST** `api_handler.php` - معالجة الأسئلة
- **GET** `api_handler.php?session_id=xxx` - تاريخ المحادثات

## 🗄️ قاعدة البيانات

### الجداول المطلوبة
- `chat_conversations` - المحادثات
- `chat_sessions` - جلسات المحادثة
- `hosts` - المضيفين
- `bookings` - الحجوزات  
- `pets` - الحيوانات الأليفة
- `users` - المستخدمين

## 🔧 الإعدادات

### تحديث إعدادات قاعدة البيانات
في ملف `main.py`:
```python
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root', 
    'password': 'your_password',  # ضع كلمة المرور هنا
    'database': 'pets_shop',
    'charset': 'utf8mb4'
}
```

### تحديث Google AI API Key
```python
GOOGLE_API_KEY = "your_api_key_here"
```

## 🧪 الاختبار

### ملفات الاختبار في مجلد chatbot/
- **`final_test.php`** - اختبار شامل ونهائي للنظام
- **`test_api.php`** - اختبار سريع للـ API
- **`debug_test.php`** - تشخيص مشاكل النظام

### ملفات الاختبار في المجلد الرئيسي
- **`test_smart_chatbot.php`** - اختبار النظام الذكي
- **`test_chat_quick.php`** - اختبار سريع
- **`test_chatbot_connection.php`** - اختبار شامل

### أسئلة للاختبار
```
ما هي المضيفين المتاحين؟
أريد معرفة حجوزاتي
كم تكلفة الخدمة؟
ما هي المناطق المتاحة؟
أريد معرفة معلومات حيواناتي
```

## 🚨 استكشاف الأخطاء

### إذا لم يعمل Python Chatbot
- تأكد من تثبيت المكتبات: `pip install -r requirements.txt`
- تحقق من المنفذ 8000: `netstat -an | findstr 8000`
- راجع إعدادات قاعدة البيانات

### إذا لم تعمل قاعدة البيانات
- تأكد من تشغيل MySQL/XAMPP
- تحقق من إعدادات الاتصال
- شغل `setup_chatbot_db.php` لإنشاء الجداول

### إذا لم تعمل الإجابات الذكية
- النظام سيستخدم Simple Chatbot كـ fallback
- تحقق من وجود البيانات في الجداول
- راجع ملفات الـ logs للأخطاء

## 📞 الدعم

للمساعدة أو الاستفسارات:
- تحقق من ملفات الاختبار
- راجع الـ logs في المتصفح
- تأكد من إعدادات قاعدة البيانات

---

**تم تطوير النظام بواسطة:** Augment Agent  
**التاريخ:** 2025-06-19
