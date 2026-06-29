# Gold Certificate Verifier - WordPress Plugin

A professional WordPress plugin for managing and displaying gold bar product certificates with verification capabilities.

## Features

- **Professional Certificate Display**: Beautiful, responsive certificate pages matching industry standards
- **Custom URL Routing**: Access certificates via clean URLs like `/cert/U0302258`
- **Admin Dashboard**: Manage certificates with an intuitive admin interface
- **Bulk Import**: Import certificates from CSV files
- **Product Gallery**: Display multiple product images with zoom functionality
- **QR Code Generation**: Automatic QR codes linking to certificate verification
- **Print & PDF**: Print or download certificates as PDF
- **Responsive Design**: Fully responsive on desktop, tablet, and mobile devices
- **Security**: Built-in validation and sanitization
- **Contact Information**: Display company contact details on certificates

## Installation

### 1. Upload Plugin Files

1. Download the `gold-certificate-verifier` folder
2. Upload it to `/wp-content/plugins/` directory via FTP or file manager
3. Alternatively, use WordPress plugin uploader:
   - Go to **Plugins > Add New > Upload Plugin**
   - Select the plugin ZIP file
   - Click "Install Now"

### 2. Activate Plugin

1. Go to **Plugins** in WordPress admin
2. Find "Gold Certificate Verifier"
3. Click "Activate"

### 3. Configure Settings

1. Go to **Gold Certificates > Settings** in the admin menu
2. Enter your company information:
   - Phone number
   - Email address
   - Website URL
3. Click "Save Settings"

## Usage

### Adding Certificates

#### Method 1: Manual Entry

1. Go to **Gold Certificates > Add Certificate**
2. Fill in the certificate details:
   - **Certificate No.** - Unique identifier (e.g., U0302258)
   - **Item Code** - Internal product code (e.g., GCB460)
   - **Product Name** - Product title (e.g., 999.9 - Gold Cast Bar)
   - **Metal** - Type of metal (default: Gold)
   - **Weight (Gram)** - Product weight in grams
   - **Fineness** - Purity level (e.g., 999.9)
   - **Certification Date** - Date of certification
   - **Origin** - Country of origin (default: Malaysia)
   - **Certified Assayer** - Company name and registration
   - **Product Image** - Upload primary product image
3. Click "Add Certificate"

#### Method 2: CSV Bulk Import

1. Prepare a CSV file with the following columns:
   ```
   certificate_no,item_code,product_name,metal,weight,fineness,cert_date,origin,assayer,image_url
   U0302258,GCB460,999.9 - Gold Cast Bar,Gold,100,999.9,2026-03-26,Malaysia,Refine Au Sdn Bhd (1246791-K),https://example.com/image.jpg
   ```

2. Go to **Gold Certificates > All Certificates**
3. Use the import function (if available) or manually add certificates

### Viewing Certificates

Customers can view certificates using:

1. **Pretty URL**: `https://yourdomain.com/cert/U0302258`
2. **Query Parameter**: `https://yourdomain.com/verify/?q=U0302258`

Both URLs will display the professional certificate page with:
- Product image and gallery
- Complete certificate details
- QR code for verification
- Print and PDF download options
- Company contact information

### Managing Certificates

1. Go to **Gold Certificates > All Certificates**
2. View all certificates in a table format
3. Click "View" to see the public certificate page
4. Click "Delete" to remove a certificate

## Database Schema

The plugin creates a custom table `wp_gold_certificates` with the following fields:

| Field | Type | Description |
|-------|------|-------------|
| id | BIGINT | Internal unique ID |
| certificate_no | VARCHAR(50) | Unique certificate number |
| item_code | VARCHAR(50) | Internal product code |
| product_name | VARCHAR(255) | Product title |
| metal | VARCHAR(50) | Type of metal |
| weight | DECIMAL(10,3) | Weight in grams |
| fineness | VARCHAR(20) | Purity level |
| cert_date | DATE | Certification date |
| origin | VARCHAR(100) | Country of origin |
| assayer | VARCHAR(255) | Certified assayer name |
| image_url | TEXT | Primary product image URL |
| gallery_urls | LONGTEXT | JSON array of additional images |
| status | VARCHAR(20) | Certificate status (active/suspended) |
| created_at | TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | Record update time |

## URL Routing

The plugin implements custom WordPress rewrite rules:

### Rewrite Rule 1: `/cert/{code}`
- Pattern: `^cert/([a-zA-Z0-9_-]+)/?$`
- Maps to: `index.php?gcv_cert=$matches[1]`

### Rewrite Rule 2: `/verify/?q={code}`
- Pattern: `^verify/?$`
- Maps to: `index.php?gcv_verify=1`

**Note**: After activating the plugin, go to **Settings > Permalinks** and click "Save Changes" to flush rewrite rules.

## Customization

### Styling

The plugin uses CSS custom properties for easy theming:

```css
:root {
	--gold-primary: #d4af37;
	--gold-dark: #b8941f;
	--charcoal: #2c2c2c;
	--dark-bg: #1a1a1a;
	--light-bg: #f5f5f5;
	--border-color: #ddd;
	--text-color: #333;
}
```

Edit `/assets/css/public.css` to customize colors and styling.

### Adding Custom Fields

To add custom fields to certificates:

1. Modify the database table in `includes/class-db.php`
2. Update the form in `includes/class-admin.php`
3. Update the display template in `includes/class-renderer.php`

## Security Features

- **Input Sanitization**: All user inputs are sanitized using WordPress functions
- **SQL Injection Prevention**: Uses prepared statements with `$wpdb->prepare()`
- **XSS Protection**: All outputs are escaped using `esc_html()`, `esc_url()`, etc.
- **Nonce Verification**: Admin actions are protected with WordPress nonces
- **Capability Checks**: Only administrators can access certificate management

## Troubleshooting

### Certificates Not Showing

1. **Check Permalink Settings**:
   - Go to **Settings > Permalinks**
   - Ensure "Post name" or custom structure is selected
   - Click "Save Changes" to flush rewrite rules

2. **Verify Database Table**:
   - Ensure the plugin was properly activated
   - Check that `wp_gold_certificates` table exists in the database

3. **Check Certificate Status**:
   - Ensure certificate status is set to "active"

### 404 Error on Certificate Page

1. Verify the certificate number exists in the database
2. Check that the URL format is correct: `/cert/CERTIFICATE_NO`
3. Flush rewrite rules: **Settings > Permalinks > Save Changes**

### Images Not Displaying

1. Verify image URLs are correct and accessible
2. Check server file permissions
3. Ensure the image URL is properly formatted (starts with `http://` or `https://`)

## Support & Development

For support, feature requests, or bug reports, please contact the plugin developer.

## License

This plugin is licensed under the GPL-2.0+ License. See LICENSE file for details.

## Changelog

### Version 1.0.0
- Initial release
- Core certificate management functionality
- Professional certificate display
- Admin dashboard
- CSV import support
- QR code generation
- Print and PDF support
