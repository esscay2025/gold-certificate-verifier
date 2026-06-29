# Gold Certificate Verifier - Installation Guide

## System Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- WordPress with custom permalinks enabled

## Step-by-Step Installation

### Step 1: Download the Plugin

1. Download the `gold-certificate-verifier` plugin folder
2. Compress it as a ZIP file (if not already compressed)

### Step 2: Upload to WordPress

#### Option A: Using WordPress Admin Dashboard

1. Log in to your WordPress admin panel
2. Navigate to **Plugins > Add New**
3. Click the "Upload Plugin" button
4. Click "Choose File" and select the plugin ZIP file
5. Click "Install Now"
6. After installation, click "Activate Plugin"

#### Option B: Using FTP/File Manager

1. Extract the plugin ZIP file on your computer
2. Connect to your server via FTP or use your hosting file manager
3. Navigate to `/wp-content/plugins/` directory
4. Upload the `gold-certificate-verifier` folder
5. Go to WordPress admin: **Plugins**
6. Find "Gold Certificate Verifier" and click "Activate"

### Step 3: Verify Installation

1. In WordPress admin, you should see a new menu item: **Gold Certificates**
2. Click on it to access the plugin dashboard
3. You should see the following submenus:
   - All Certificates
   - Add Certificate
   - Settings

### Step 4: Configure Permalinks

**Important**: This step is required for the certificate URLs to work properly.

1. Go to **Settings > Permalinks**
2. Select "Post name" or any custom structure (NOT "Plain")
3. Click "Save Changes"

This flushes the WordPress rewrite rules and activates the custom certificate URLs.

### Step 5: Configure Plugin Settings

1. Go to **Gold Certificates > Settings**
2. Fill in your company information:
   - **Company Phone**: Your business phone number
   - **Company Email**: Your business email address
   - **Company Website**: Your website URL
3. Click "Save Settings"

### Step 6: Add Your First Certificate

1. Go to **Gold Certificates > Add Certificate**
2. Fill in the certificate details:
   - Certificate No. (e.g., U0302258)
   - Item Code (e.g., GCB460)
   - Product Name (e.g., 999.9 - Gold Cast Bar)
   - Metal (default: Gold)
   - Weight in grams
   - Fineness (e.g., 999.9)
   - Certification Date
   - Origin (default: Malaysia)
   - Certified Assayer
   - Product Image (upload via media uploader)
3. Click "Add Certificate"

### Step 7: Test Certificate Display

1. Go to **Gold Certificates > All Certificates**
2. Click "View" on your newly added certificate
3. You should see the professional certificate page at a URL like:
   `https://yourdomain.com/cert/U0302258`

## Bulk Import from CSV

### CSV File Format

Create a CSV file with the following header row:

```
certificate_no,item_code,product_name,metal,weight,fineness,cert_date,origin,assayer,image_url
```

Example data row:

```
U0302258,GCB460,999.9 - Gold Cast Bar,Gold,100,999.9,2026-03-26,Malaysia,Refine Au Sdn Bhd (1246791-K),https://example.com/gold-bar.jpg
```

### Import Steps

1. Prepare your CSV file with all certificate data
2. Go to **Gold Certificates > All Certificates**
3. Use the import function (if available in your version)
4. Or manually add certificates one by one

**Note**: A sample CSV file is included as `sample-certificates.csv`

## Uninstallation

### To Deactivate and Remove the Plugin

1. Go to **Plugins** in WordPress admin
2. Find "Gold Certificate Verifier"
3. Click "Deactivate"
4. Click "Delete"
5. Confirm deletion

**Note**: This will remove the plugin files but NOT the database table. To completely remove the database table, you would need to manually delete it via phpMyAdmin or similar tool.

## Troubleshooting

### Issue: Plugin doesn't appear in admin menu

**Solution**:
1. Ensure the plugin is activated
2. Clear your browser cache
3. Log out and log back in
4. Check that you have administrator privileges

### Issue: Certificate URLs return 404 error

**Solution**:
1. Go to **Settings > Permalinks**
2. Select "Post name" structure
3. Click "Save Changes"
4. Try accessing the certificate URL again

### Issue: Images not uploading

**Solution**:
1. Check server upload directory permissions (should be 755)
2. Verify file size is within WordPress limits
3. Ensure image format is supported (JPG, PNG, GIF, WebP)
4. Check WordPress media settings

### Issue: Database table not created

**Solution**:
1. Deactivate the plugin
2. Delete the plugin folder
3. Re-upload and activate the plugin
4. This will trigger the table creation process

## Database Access

The plugin creates a table named `wp_gold_certificates` in your WordPress database.

### Accessing via phpMyAdmin

1. Log in to your hosting control panel
2. Open phpMyAdmin
3. Select your WordPress database
4. Look for the `wp_gold_certificates` table
5. You can view, edit, or export certificate data

## Security Considerations

1. **Backup Your Database**: Before installing, create a database backup
2. **Keep WordPress Updated**: Ensure WordPress and plugins are up to date
3. **Use Strong Passwords**: Use strong admin passwords
4. **Limit Admin Access**: Restrict admin access by IP if possible
5. **Regular Backups**: Maintain regular backups of your website

## Performance Optimization

For sites with many certificates:

1. **Enable Caching**: Use a caching plugin like WP Super Cache
2. **Database Optimization**: Regularly optimize your database
3. **Image Optimization**: Optimize product images before uploading
4. **CDN**: Use a CDN for serving images

## Getting Help

If you encounter issues:

1. Check the README.md file for additional documentation
2. Review the troubleshooting section above
3. Check your server error logs
4. Contact your hosting provider for server-related issues

## Next Steps

After installation:

1. Add all your gold bar certificates
2. Test the certificate display pages
3. Share certificate URLs with customers
4. Monitor certificate views and access
5. Update certificates as needed

Enjoy your Gold Certificate Verifier plugin!
