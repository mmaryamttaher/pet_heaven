@echo off
echo ========================================
echo    Pet Shop Chatbot Startup Script
echo ========================================
echo.
echo Installing Python dependencies...
pip install -r requirements.txt
echo.
echo ========================================
echo Starting Pet Shop Chatbot Server...
echo ========================================
echo.
echo Server will run on: http://localhost:8000
echo API Documentation: http://localhost:8000/docs
echo.
python main.py
pause
