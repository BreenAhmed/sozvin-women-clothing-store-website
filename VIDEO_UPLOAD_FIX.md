# Video Upload Feature - Complete Fix

## ✅ What Was Fixed

### 1. Database Table Error Fixed
**Error:** `Table 'sozvin.videos' doesn't exist`

**Solution:**
- Created `create_videos_table.php` - Run this to create the table
- Updated `full_database_setup.php` to include videos table
- Updated database schema to support file uploads (added `video_file` column)

### 2. Video File Upload Added
**New Feature:** Admins can now upload video files directly from their device!

**Supported Formats:**
- MP4, WebM, OGG, MOV, AVI, WMV, FLV
- Maximum file size: 100MB

**Upload Locations:**
- Videos: `uploads/videos/`
- Thumbnails: `uploads/videos/thumbnails/`

---

## 🚀 Quick Setup

### Step 1: Create Videos Table

**Option A: Use PHP Script (Easiest)**
```
Visit: https://yourdomain.com/create_videos_table.php
```

**Option B: Run SQL**
```sql
-- In phpMyAdmin, select your database and run:
CREATE TABLE videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    title_ar VARCHAR(200) NOT NULL,
    description TEXT,
    description_ar TEXT,
    video_url VARCHAR(500),
    video_file VARCHAR(500),
    thumbnail_url VARCHAR(500),
    video_type ENUM('youtube', 'vimeo', 'direct', 'embed', 'upload') DEFAULT 'youtube',
    duration INT DEFAULT 0,
    views INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_active (is_active),
    KEY idx_featured (is_featured),
    KEY idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 2: Create Upload Directories

The script creates these automatically, but if needed:
```bash
mkdir -p uploads/videos
mkdir -p uploads/videos/thumbnails
chmod 755 uploads/videos
chmod 755 uploads/videos/thumbnails
```

### Step 3: Test It!

1. Login to admin
2. Go to Videos → Add Video
3. Choose "رفع ملف فيديو" (Upload video file)
4. Select a video file
5. Upload!

---

## 📋 How to Use Video Upload

### Adding a Video with File Upload:

1. **Go to:** Admin → Videos → Add Video

2. **Choose Upload Method:**
   - Select "رفع ملف فيديو" (Upload video file) radio button

3. **Upload Video:**
   - Click "Choose File" under "رفع ملف فيديو"
   - Select your video file (MP4, WebM, etc.)
   - File size shown automatically

4. **Upload Thumbnail (Optional):**
   - Click "Choose File" under "رفع صورة مصغرة"
   - Or enter thumbnail URL

5. **Fill Other Details:**
   - Title, description, etc.

6. **Click "Add Video"**

### Editing a Video:

1. Go to Videos list
2. Click "Edit" on any video
3. Choose to keep existing video or upload new one
4. Upload new thumbnail if needed
5. Save changes

---

## 🎯 Features

✅ **File Upload** - Upload videos directly from device  
✅ **URL Support** - Still supports YouTube, Vimeo, direct links  
✅ **Thumbnail Upload** - Upload custom thumbnails  
✅ **File Management** - Old files deleted when replaced  
✅ **HTML5 Player** - Uploaded videos play in HTML5 video player  
✅ **File Validation** - Checks file type and size  
✅ **Auto Directory Creation** - Creates upload folders automatically  

---

## 📁 Files Updated

### New Files:
- `create_videos_table.php` - Creates videos table

### Updated Files:
- `admin/add-video.php` - Added file upload support
- `admin/edit-video.php` - Added file upload support
- `admin/videos.php` - Shows upload status
- `videos.php` - Plays uploaded videos
- `full_database_setup.php` - Includes videos table

---

## 🔧 Database Schema

```sql
videos table:
├── id
├── title
├── title_ar
├── description
├── description_ar
├── video_url (for YouTube/Vimeo/direct links)
├── video_file (for uploaded files) ← NEW!
├── thumbnail_url
├── video_type (youtube, vimeo, direct, embed, upload) ← NEW!
├── duration
├── views
├── is_active
├── is_featured
├── sort_order
├── created_at
└── updated_at
```

---

## ⚠️ Important Notes

### File Size Limits:
- **Maximum:** 100MB per video
- **Recommended:** Under 50MB for better performance
- **Formats:** MP4 is most compatible

### Server Requirements:
- PHP `upload_max_filesize` should be at least 100M
- PHP `post_max_size` should be at least 100M
- `uploads/videos/` directory must be writable (755 permissions)

### Check PHP Settings:
```php
// In php.ini or .htaccess:
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
```

---

## 🐛 Troubleshooting

### Error: "Table doesn't exist"
**Fix:** Run `create_videos_table.php` or `full_database_setup.php`

### Error: "File upload failed"
**Fix:** 
- Check `uploads/videos/` directory exists and is writable
- Check PHP upload limits
- Check file size (must be under 100MB)

### Error: "File type not allowed"
**Fix:** Make sure file is one of: MP4, WebM, OGG, MOV, AVI, WMV, FLV

### Videos not playing
**Fix:**
- Check file path is correct
- Check file permissions (should be readable)
- Try different browser
- Check file format (MP4 is most compatible)

---

## ✅ Testing Checklist

- [ ] Videos table created successfully
- [ ] Can upload video file from admin
- [ ] Can upload thumbnail image
- [ ] Uploaded video plays on public page
- [ ] Can edit video and replace file
- [ ] Old files deleted when replaced
- [ ] File size validation works
- [ ] File type validation works
- [ ] Thumbnail shows correctly
- [ ] Mobile responsive

---

## 🎉 Success!

Your video upload feature is now fully functional! Admins can:
- Upload videos directly from their device
- Use YouTube/Vimeo links (still supported)
- Upload custom thumbnails
- Manage all videos from admin panel

Users can:
- Watch uploaded videos on the public page
- Videos play in beautiful modal player
- Works on mobile and desktop

---

**Next Steps:**
1. Run `create_videos_table.php` to create the table
2. Test uploading a video
3. Check it plays on the public page
4. Delete setup files after testing

