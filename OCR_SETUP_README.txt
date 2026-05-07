================================================================================
TESSERACT OCR AUTO-SETUP - SPED LMS
================================================================================

QUICK START (3 Steps):

1. RIGHT-CLICK "setup_tesseract.bat"
   → Select "Run as administrator"
   → Wait for completion

2. RESTART APACHE
   → Open XAMPP Control Panel
   → Stop Apache
   → Start Apache

3. TEST OCR
   → Open browser
   → Go to: http://localhost/Signedd/public/test_ocr.php
   → Check if all green ✅

DONE! OCR Auto-Fill is ready.

================================================================================

WHAT THIS DOES:

✅ Finds Tesseract installation automatically
✅ Adds Tesseract to system PATH
✅ Updates SPED LMS configuration
✅ Creates backup of config file
✅ Tests if everything works

================================================================================

IF YOU SEE ERRORS:

"Tesseract NOT found"
→ Install Tesseract first: https://github.com/UB-Mannheim/tesseract/wiki
→ Run setup script again

"Not running as Administrator"
→ Right-click setup_tesseract.bat
→ Select "Run as administrator"

"Config file not found"
→ Make sure you're in project root folder
→ Should see: config/, app/, public/ folders

================================================================================

TEST PAGE:

After setup, test at:
http://localhost/Signedd/public/test_ocr.php

Should show:
✅ Tesseract is accessible via PATH
✅ Found: C:\Program Files\Tesseract-OCR\tesseract.exe
✅ This path WORKS!
✅ OCR Auto-Fill is READY!

================================================================================

MANUAL SETUP (if script fails):

1. Find Tesseract installation:
   - Usually at: C:\Program Files\Tesseract-OCR\tesseract.exe

2. Edit config/tesseract.php, line 13:
   $manualPath = 'C:\Program Files\Tesseract-OCR\tesseract.exe';

3. Restart Apache

4. Test at: http://localhost/Signedd/public/test_ocr.php

================================================================================

NEED HELP?

Read: SETUP_INSTRUCTIONS.md (detailed guide)
Read: TESSERACT_OCR_SETUP.md (complete documentation)
Test: http://localhost/Signedd/public/test_ocr.php (diagnostics)

================================================================================

SUMMARY:

✅ 100% FREE - No API costs
✅ Privacy-friendly - Data stays local
✅ Easy setup - One-time, 1 minute
✅ Always works - Manual fill as fallback

Enjoy free OCR auto-fill! 🎉

================================================================================
