Universal Image Format Converter
Author: Mohammed Saheel Shaikh
Version: 1.0
Description: A powerful WordPress plugin that automatically converts uploaded images to any desired format (WebP, JPEG, PNG, GIF) with easy configuration and admin interface.

📌 Features
Universal Format Support: Convert between WebP, JPEG, PNG, GIF, and BMP formats
Easy Configuration: Simple config array for quick format changes
Admin Settings Interface: Complete settings page in WordPress admin
Quality Control: Adjustable image quality settings (1-100)
Selective Conversion: Choose which source formats to convert
Server Compatibility Check: Built-in checks for GD Library and WebP support
Original File Management: Option to keep or delete original files after conversion
Error Handling: Robust error checking with fallback options

📥 Installation
Download or clone this repository.
Create a folder named universal-image-converter in your wp-content/plugins directory.
Place the universal-image-converter.php file inside this folder.
In your WordPress dashboard, go to Plugins → Installed Plugins.
Find Universal Image Format Converter and click Activate.

⚙️ Usage
Quick Configuration (Code-based)
Open the plugin file and locate the $config array.
Change the target_format value to your desired format:
webp - For WebP format
jpeg - For JPEG format
png - For PNG format
gif - For GIF format
Adjust quality (1-100) and other settings as needed.
Save the file.

Advanced Configuration (Admin Interface)
Go to Settings → Image Converter.
Configure your preferred settings:
Enable/Disable the converter
Target Format selection
Image Quality adjustment
Source Formats to convert
Original File handling options
Click Save Changes
