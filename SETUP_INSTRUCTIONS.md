# Tesseract OCR Setup - Automatic

## Paspas nga Setup (1 minute)

### Step 1: Run Setup Script

1. **Right-click** sa `setup_tesseract.bat`
2. **Select** "Run as administrator"
3. **Wait** for script to finish
4. **Press any key** to close

✅ **Done!** Automatic na ang setup.

---

### Step 2: Restart Apache

1. Open **XAMPP Control Panel**
2. Click **Stop** sa Apache
3. Click **Start** sa Apache

✅ **Done!** Apache restarted.

---

### Step 3: Test OCR

**Option A: Test Page**
1. Open browser
2. Go to: `http://localhost/Signedd/public/test_ocr.php`
3. Check kung all green ✅

**Option B: Test sa System**
1. Login as SPED Teacher
2. Go to IEP Meeting → PDSP Form
3. Upload signed document
4. Click "OCR Auto-Fill"
5. Check kung mu-work

---

## Kung May Error Pa

### Error: "Tesseract NOT found"

**Meaning:** Tesseract wala pa na-install

**Solution:**
1. Download: https://github.com/UB-Mannheim/tesseract/wiki
2. Install: Run the .exe file
3. Run `setup_tesseract.bat` again

---

### Error: "Not running as Administrator"

**Meaning:** Script needs admin rights

**Solution:**
1. Right-click `setup_tesseract.bat`
2. Select "Run as administrator"
3. Click "Yes" sa UAC prompt

---

### Error: "Config file not found"

**Meaning:** Wrong directory

**Solution:**
1. Make sure you're in project root folder
2. Should see: `config/`, `app/`, `public/` folders
3. Run script from there

---

## Manual Setup (Kung dili mu-work ang script)

### Step 1: Find Tesseract Path

Open File Explorer, check kung naa sa:
- `C:\Program Files\Tesseract-OCR\tesseract.exe`
- `C:\Program Files (x86)\Tesseract-OCR\tesseract.exe`

### Step 2: Update Config

Edit `config/tesseract.php`, line 13:

```php
$manualPath = 'C:\Program Files\Tesseract-OCR\tesseract.exe';
```

Change to your actual path.

### Step 3: Test

Go to: `http://localhost/Signedd/public/test_ocr.php`

---

## Files Created

- ✅ `setup_tesseract.bat` - Auto-setup script
- ✅ `config/tesseract.php` - OCR configuration
- ✅ `public/test_ocr.php` - Test page
- ✅ `SETUP_INSTRUCTIONS.md` - This file

---

## Summary

1. **Run** `setup_tesseract.bat` as administrator
2. **Restart** Apache
3. **Test** at `test_ocr.php`
4. **Use** OCR Auto-Fill sa PDSP form

**That's it!** 🎉

---

## Need Help?

Kung naa pa'y problema:
1. Check `test_ocr.php` - shows detailed diagnostics
2. Check error logs sa `/logs/` folder
3. Try manual setup (see above)
4. Use manual fill (always works)

**Sulti lang!** 😊
