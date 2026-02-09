<?php
// Biteship API Configuration
// URL Testing: https://api.biteship.com/v1 (Usually same for production, key determines mode)
// Official Docs: https://biteship.com/docs

// Determine Environment: 'sandbox' or 'production'
// You can set this via server environment variable or manually here.
define('BITESHIP_ENV', getenv('BITESHIP_ENV') ?: 'production'); 

// API Keys - Replace with your actual keys
// It is recommended to use environment variables for keys in production
define('BITESHIP_API_KEY_SANDBOX', 'biteship_test.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoibGFwYWstaW50ZWdyYXNpIiwidXNlcklkIjoiNjk3MzNmNGQzODI5ZDI2YmNiMzNlM2Q4IiwiaWF0IjoxNzY5NDg3OTUxfQ.7O5smgSFozNbLObYHe6qXA4kvNhK-7lI4vC2Tn4hxSA'); // Current Test Key
define('BITESHIP_API_KEY_PRODUCTION', getenv('BITESHIP_API_KEY_PRODUCTION') ?: 'biteship_live.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoibGFwYWstYmFuZ3Nhd2FuLWFwaSIsInVzZXJJZCI6IjY5NzMzZjRkMzgyOWQyNmJjYjMzZTNkOCIsImlhdCI6MTc2OTQ3NTk1NH0.mP2ctkclA-skyp91R4AcP25uyII7Fu3yn0Evt3Fujxk'); 

// Select Key based on Environment
if (BITESHIP_ENV === 'production') {
    define('BITESHIP_API_KEY', BITESHIP_API_KEY_PRODUCTION);
    define('BITESHIP_BASE_URL', 'https://api.biteship.com/v1'); // Production URL
} else {
    define('BITESHIP_API_KEY', BITESHIP_API_KEY_SANDBOX);
    define('BITESHIP_BASE_URL', 'https://api.biteship.com/v1'); // Sandbox URL (Same, key differs)
}

// Origin Configuration (Lapak Bangsawan Location)
// You can also add default origin area ID if you have it
define('BITESHIP_ORIGIN_AREA_ID', 'IDNP9IDNC105IDND171IDZ45171'); // Example ID for a specific area
define('BITESHIP_ORIGIN_LAT', -6.744815286779071); // Origin Latitude for Instant Courier
define('BITESHIP_ORIGIN_LNG', 108.53552144352373); // Origin Longitude for Instant Courier

// Contact Information
define('BITESHIP_ORIGIN_CONTACT_NAME', 'Lapak Bangsawan');
define('BITESHIP_ORIGIN_CONTACT_PHONE', '08123456789'); // Replace with actual phone