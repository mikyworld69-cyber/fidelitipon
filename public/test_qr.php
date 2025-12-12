<?php
require_once __DIR__ . '/../lib/phpqrcode/qrlib.php';

QRcode::png("PRUEBA QR", false, QR_ECLEVEL_L, 8);
