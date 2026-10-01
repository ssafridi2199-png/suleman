<?php
// Script to generate high quality SVG/GD fallback images for products & categories

$productsDir = __DIR__ . '/../uploads/products/';
$rxDir = __DIR__ . '/../uploads/prescriptions/';
$catDir = __DIR__ . '/../uploads/categories/';

if (!file_exists($productsDir)) mkdir($productsDir, 0777, true);
if (!file_exists($rxDir)) mkdir($rxDir, 0777, true);
if (!file_exists($catDir)) mkdir($catDir, 0777, true);

function makeProductSVG($filename, $title, $category, $bgColor = '#0d6efd', $badge = '') {
    $filepath = __DIR__ . '/../uploads/products/' . $filename;
    
    // Generate clean SVG image
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">
      <defs>
        <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="#f8fafc" />
          <stop offset="100%" stop-color="#e2e8f0" />
        </linearGradient>
        <linearGradient id="header" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" stop-color="' . $bgColor . '" />
          <stop offset="100%" stop-color="#059669" />
        </linearGradient>
        <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
          <feDropShadow dx="0" dy="10" stdDeviation="15" flood-color="#0f172a" flood-opacity="0.12"/>
        </filter>
      </defs>

      <rect width="600" height="600" fill="url(#bg)" />

      <!-- Product Box Container -->
      <g filter="url(#shadow)">
        <rect x="100" y="80" width="400" height="440" rx="24" fill="#ffffff" stroke="#cbd5e1" stroke-width="2"/>
        
        <!-- Header Banner -->
        <path d="M 100 104 Q 100 80 124 80 L 476 80 Q 500 80 500 104 L 500 160 L 100 160 Z" fill="url(#header)"/>
        <text x="300" y="130" font-family="system-ui, sans-serif" font-weight="bold" font-size="26" fill="#ffffff" text-anchor="middle">AFRIDI PHARMACY</text>
        
        <!-- Cross Icon -->
        <circle cx="300" cy="270" r="55" fill="#f1f5f9"/>
        <path d="M285 270 H315 M300 255 V285" stroke="' . $bgColor . '" stroke-width="12" stroke-linecap="round"/>
        
        <!-- Product Label -->
        <rect x="140" y="360" width="320" height="120" rx="12" fill="#f8fafc" stroke="#e2e8f0"/>
        <text x="300" y="400" font-family="system-ui, sans-serif" font-weight="bold" font-size="20" fill="#0f172a" text-anchor="middle">' . htmlspecialchars(substr($title, 0, 28)) . '</text>
        <text x="300" y="430" font-family="system-ui, sans-serif" font-size="16" fill="#64748b" text-anchor="middle">' . htmlspecialchars($category) . '</text>';

    if ($badge) {
        $svg .= '<rect x="380" y="100" width="100" height="30" rx="15" fill="#dc2626" />
        <text x="430" y="121" font-family="system-ui, sans-serif" font-weight="bold" font-size="13" fill="#ffffff" text-anchor="middle">' . htmlspecialchars($badge) . '</text>';
    }

    $svg .= '</g>
    </svg>';

    file_put_contents($filepath, $svg);
    // Also save as jpg extension if filename ends with .jpg (SVG format compatible with modern browsers img tag if served with header or embedded)
}

$items = [
    ['panadol-extra.jpg', 'Panadol Extra 500mg', 'Medicines', '#0d6efd', 'OTC Relief'],
    ['amoxicillin.jpg', 'Amoxicillin 500mg', 'Antibiotic', '#0f766e', 'Rx Required'],
    ['lipitor.jpg', 'Lipitor 20mg Tablets', 'Cardiovascular', '#2563eb', 'Rx Required'],
    ['centrum-adults.jpg', 'Centrum Adults Daily', 'Vitamins', '#d97706', 'Daily Health'],
    ['vitaminc-effervescent.jpg', 'Vitamin C 1000mg', 'Supplements', '#ea580c', 'Immunity'],
    ['pampers-wipes.jpg', 'Pampers Sensitive Wipes', 'Baby Care', '#0284c7', 'Baby Gentle'],
    ['johnson-bath.jpg', 'Johnson Gentle Bath', 'Baby Skincare', '#3b82f6', 'No Tears'],
    ['dettol-antiseptic.jpg', 'Dettol Antiseptic Liquid', 'First Aid', '#16a34a', 'Antiseptic'],
    ['onetouch-glucometer.jpg', 'OneTouch Select Plus', 'Diabetes Care', '#9333ea', 'Blood Glucose'],
    ['omron-bp-monitor.jpg', 'Omron M2 Digital BP', 'Medical Device', '#4f46e5', 'Clinically Proven'],
    ['sensodyne-toothpaste.jpg', 'Sensodyne Rapid Relief', 'Dental Care', '#2563eb', 'Rapid Relief'],
    ['insulin-syringes.jpg', 'Insulin Syringes U-100', 'Diabetes Care', '#e11d48', 'Rx Required'],
    ['bandages.jpg', 'Waterproof Bandages', 'First Aid', '#059669', 'Wound Care']
];

foreach ($items as $item) {
    makeProductSVG($item[0], $item[1], $item[2], $item[3], $item[4]);
}

// Sample prescription dummy file
$rxSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="1000" viewBox="0 0 800 1000">
  <rect width="800" height="1000" fill="#f8fafc"/>
  <rect x="50" y="50" width="700" height="900" rx="16" fill="#ffffff" stroke="#cbd5e1" stroke-width="3"/>
  <rect x="50" y="50" width="700" height="140" fill="#0f766e"/>
  <text x="400" y="110" font-family="sans-serif" font-weight="bold" font-size="32" fill="#ffffff" text-anchor="middle">DR. AHMED KHAN, M.D.</text>
  <text x="400" y="150" font-family="sans-serif" font-size="18" fill="#e2e8f0" text-anchor="middle">Senior Consultant Physician • PMDC # 78452</text>
  
  <text x="100" y="240" font-family="sans-serif" font-weight="bold" font-size="20" fill="#0f172a">Patient Name: John Doe</text>
  <text x="550" y="240" font-family="sans-serif" font-weight="bold" font-size="20" fill="#0f172a">Date: 2026-10-01</text>
  <line x1="100" y1="270" x2="700" y2="270" stroke="#94a3b8" stroke-width="2"/>
  
  <text x="100" y="340" font-family="sans-serif" font-weight="bold" font-size="40" fill="#0f766e">Rx</text>
  <text x="130" y="410" font-family="monospace" font-size="22" fill="#1e293b">1. Tab. Amoxicillin 500mg ----- 1 cap TID (7 Days)</text>
  <text x="130" y="470" font-family="monospace" font-size="22" fill="#1e293b">2. Tab. Panadol Extra 500mg --- 1 tab BD SOS</text>
  <text x="130" y="530" font-family="monospace" font-size="22" fill="#1e293b">3. Cap. Centrum Multivitamin -- 1 cap OD</text>
  
  <rect x="450" y="780" width="230" height="100" rx="8" fill="none" stroke="#0f766e" stroke-width="3" stroke-dasharray="6,6"/>
  <text x="565" y="835" font-family="sans-serif" font-weight="bold" font-size="18" fill="#0f766e" text-anchor="middle">VERIFIED STAMP</text>
  <text x="565" y="860" font-family="sans-serif" font-size="14" fill="#0f766e" text-anchor="middle">Dr. Ahmed Khan M.D.</text>
</svg>';

file_put_contents(__DIR__ . '/../uploads/prescriptions/sample-rx-john.jpg', $rxSvg);

echo "Placeholder SVG images created successfully!\n";
