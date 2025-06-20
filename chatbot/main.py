# -*- coding: utf-8 -*-
"""
Pet Shop Chatbot with Database Integration
"""

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import google.generativeai as genai
import mysql.connector
from datetime import datetime
import os
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Google AI Configuration
GOOGLE_API_KEY = "AIzaSyCSyfLO0mmzWiBr6OGbG4QTQiw1eQV3GKE"
genai.configure(api_key=GOOGLE_API_KEY)

# Database Configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',  # Update with your MySQL password
    'database': 'pets_shop',
    'charset': 'utf8mb4'
}

# Initialize Gemini Model
flash = genai.GenerativeModel('gemini-1.5-flash')

# Create chat with specialized prompt
chat = flash.start_chat(history=[
    {
        "role": "user",
        "parts": [
            """أنت مساعد ذكي متخصص فقط في جميع مجالات الحيوانات الأليفة والبرية والبحرية. 
            
            مهامك:
            1. الإجابة على أسئلة حول رعاية الحيوانات الأليفة
            2. تقديم نصائح حول التغذية والصحة
            3. مساعدة في اختيار الحيوان المناسب
            4. شرح سلوك الحيوانات وطرق التدريب
            5. معلومات عن الحيوانات البرية والبحرية
            
            قواعد مهمة:
            - تحدث فقط عن الحيوانات
            - إذا سُئلت عن شيء خارج مجال الحيوانات، أخبر المستخدم أنك متخصص في الحيوانات فقط
            - قدم إجابات مفيدة وعملية
            - استخدم اللغة العربية بشكل أساسي
            - كن ودوداً ومساعداً
            
            ابدأ كل محادثة بترحيب مناسب."""
        ]
    }
])

# FastAPI App
app = FastAPI(title="Pet Shop Chatbot", version="1.0.0")

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Pydantic Models
class Question(BaseModel):
    question: str
    session_id: str = None
    user_id: int = None

class ChatResponse(BaseModel):
    response: str
    session_id: str
    success: bool = True

# Database Functions
def get_db_connection():
    """Get database connection"""
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        return connection
    except mysql.connector.Error as err:
        logger.error(f"Database connection error: {err}")
        return None

def save_conversation_to_db(user_id, session_id, user_message, bot_response):
    """Save conversation to database"""
    try:
        connection = get_db_connection()
        if not connection:
            return False
            
        cursor = connection.cursor()
        
        # Insert conversation
        insert_query = """
        INSERT INTO chat_conversations (user_id, session_id, user_message, bot_response) 
        VALUES (%s, %s, %s, %s)
        """
        cursor.execute(insert_query, (user_id, session_id, user_message, bot_response))
        
        # Update or insert session
        session_query = """
        INSERT INTO chat_sessions (session_id, user_id, total_messages) 
        VALUES (%s, %s, 1) 
        ON DUPLICATE KEY UPDATE 
        total_messages = total_messages + 1, 
        last_activity = NOW()
        """
        cursor.execute(session_query, (session_id, user_id))
        
        connection.commit()
        cursor.close()
        connection.close()
        
        logger.info(f"Conversation saved for session: {session_id}")
        return True
        
    except mysql.connector.Error as err:
        logger.error(f"Database error: {err}")
        return False

def get_conversation_history(session_id, limit=5):
    """Get recent conversation history"""
    try:
        connection = get_db_connection()
        if not connection:
            return []
            
        cursor = connection.cursor(dictionary=True)
        
        query = """
        SELECT user_message, bot_response, created_at 
        FROM chat_conversations 
        WHERE session_id = %s 
        ORDER BY created_at DESC 
        LIMIT %s
        """
        cursor.execute(query, (session_id, limit))
        history = cursor.fetchall()
        
        cursor.close()
        connection.close()
        
        return list(reversed(history))  # Return in chronological order
        
    except mysql.connector.Error as err:
        logger.error(f"Database error: {err}")
        return []

def enhance_prompt_with_context(question, session_id):
    """Enhance the question with conversation context"""
    history = get_conversation_history(session_id, 3)
    
    if not history:
        return question
    
    context = "السياق من المحادثة السابقة:\n"
    for conv in history:
        context += f"المستخدم: {conv['user_message']}\n"
        context += f"المساعد: {conv['bot_response'][:100]}...\n\n"
    
    enhanced_question = f"{context}\nالسؤال الحالي: {question}"
    return enhanced_question

# API Endpoints
@app.get("/")
async def root():
    return {"message": "Pet Shop Chatbot API is running!", "status": "active"}

@app.get("/health")
async def health_check():
    """Health check endpoint"""
    db_status = "connected" if get_db_connection() else "disconnected"
    return {
        "status": "healthy",
        "database": db_status,
        "timestamp": datetime.now().isoformat()
    }

@app.post("/ask", response_model=ChatResponse)
async def ask_animal_bot(q: Question):
    """Main chatbot endpoint"""
    try:
        # Validate input
        if not q.question or not q.question.strip():
            raise HTTPException(status_code=400, detail="Question cannot be empty")
        
        question = q.question.strip()
        session_id = q.session_id or f"session_{datetime.now().strftime('%Y%m%d_%H%M%S')}"
        
        # Enhance question with context if session exists
        enhanced_question = enhance_prompt_with_context(question, session_id)
        
        # Get response from Gemini
        response = chat.send_message(enhanced_question)
        bot_response = response.text
        
        # Save to database
        save_success = save_conversation_to_db(q.user_id, session_id, question, bot_response)
        
        logger.info(f"Question processed for session: {session_id}, DB saved: {save_success}")
        
        return ChatResponse(
            response=bot_response,
            session_id=session_id,
            success=True
        )
        
    except Exception as e:
        logger.error(f"Error processing question: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Error processing your question: {str(e)}")

@app.get("/history/{session_id}")
async def get_chat_history(session_id: str, limit: int = 10):
    """Get conversation history for a session"""
    try:
        history = get_conversation_history(session_id, limit)
        return {
            "success": True,
            "session_id": session_id,
            "history": history,
            "count": len(history)
        }
    except Exception as e:
        logger.error(f"Error getting history: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/reset/{session_id}")
async def reset_chat_session(session_id: str):
    """Reset chat session (clear context)"""
    try:
        # You can implement session reset logic here
        # For now, we'll just return success
        return {
            "success": True,
            "message": "Chat session reset successfully",
            "session_id": session_id
        }
    except Exception as e:
        logger.error(f"Error resetting session: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# Run the application
if __name__ == "__main__":
    import uvicorn
    
    print("🐾 Starting Pet Shop Chatbot...")
    print("📊 Database:", DB_CONFIG['database'])
    print("🌐 Server will run on: http://localhost:8000")
    print("📖 API docs available at: http://localhost:8000/docs")
    
    uvicorn.run(
        app, 
        host="0.0.0.0", 
        port=8000,
        reload=True,
        log_level="info"
    )
