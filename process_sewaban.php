<?php
// backend/process_sewaban.php

// Enable CORS
header('Access-Control-Allow-Origin: ');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo jsonResponse(false, 'Only POST method allowed');
    exit();
}

// Load dependencies
require_once 'config.php';
require_once 'database.php';
require_once 'uploader.php';

try {
    // Get form data
    $data = getFormData();
    
    // Validate required fields
    validateRequiredFields($data);
    
    // Handle file uploads
    $filePaths = handleFileUploads();
    
    // Prepare data for database
    $dbData = prepareDatabaseData($data, $filePaths);
    
    // Save to database
    $db = getDB();
    $id = $db->insert('sewa_ban', $dbData);
    
    // Success response
    echo jsonResponse(true, 'Data penyewaan berhasil disimpan!', ['id' => $id]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo jsonResponse(false, $e->getMessage());
}

/**
 * Helper Functions
 */

function getFormData() {
    return [
        'nama_lengkap' => cleanInput($_POST['Nama_lengkap'] ?? ''),
        'truck_brand' => cleanInput($_POST['truck_brand'] ?? ''),
        'jenis_truck' => cleanInput($_POST['Jenis_truck'] ?? ''),
        'tgl_pengecekan' => cleanInput($_POST['tglpengecekan'] ?? ''),
        'customer' => cleanInput($_POST['customer'] ?? ''),
        'nopol' => cleanInput($_POST['nopol'] ?? ''),
        'km_odo' => cleanInput($_POST['KM_ODO'] ?? ''),
        'posisi_ban' => cleanInput($_POST['posisi_ban'] ?? ''),
        'ukuran_ban' => cleanInput($_POST['ukuran_ban'] ?? ''),
        'ukuran_ban_lain' => cleanInput($_POST['ukuran_ban_lain'] ?? ''),
        'merk_ban' => cleanInput($_POST['merk_ban'] ?? ''),
        'merk_ban_lain' => cleanInput($_POST['merk_ban_lain'] ?? ''),
        'pattern_ban' => cleanInput($_POST['pattern_ban'] ?? ''),
        'depth_ban' => cleanInput($_POST['depth_ban'] ?? ''),
        'pressure' => cleanInput($_POST['pressure'] ?? ''),
        'tread' => cleanInput($_POST['tread'] ?? ''),
        'side_wall' => cleanInput($_POST['side_wall'] ?? ''),
        'kondisi_ban' => cleanInput($_POST['Kondisiban'] ?? '')
    ];
}

function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateRequiredFields($data) {
    $required = [
        'nama_lengkap', 'truck_brand', 'jenis_truck', 'tgl_pengecekan',
        'customer', 'nopol', 'km_odo', 'posisi_ban', 'ukuran_ban', 
        'merk_ban', 'pattern_ban', 'depth_ban', 'pressure', 'tread',
        'side_wall', 'kondisi_ban'
    ];
    
    $missing = [];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        throw new Exception('Field wajib diisi: ' . implode(', ', $missing));
    }
}

function handleFileUploads() {
    $filePaths = [];
    $fileMap = [
        'foto_KMODO' => 'km_odo',
        'foto_merkban' => 'merk_ban',
        'foto_tread' => 'tread',
        'foto_sidewall' => 'sidewall',
        'foto_kondisiban' => 'kondisi_ban'
    ];
    
    foreach ($fileMap as $field => $prefix) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            try {
                $filePaths[$field] = FileUploader::upload($_FILES[$field], $prefix);
            } catch (Exception $e) {
                throw new Exception("File $field: " . $e->getMessage());
            }
        } else {
            throw new Exception("File $field wajib diupload");
        }
    }
    
    return $filePaths;
}

function prepareDatabaseData($data, $filePaths) {
    // Handle custom inputs
    if ($data['ukuran_ban'] === 'lain') {
        $data['ukuran_ban'] = !empty($data['ukuran_ban_lain']) ? $data['ukuran_ban_lain'] : $data['ukuran_ban'];
    }
    
    if ($data['merk_ban'] === 'lain') {
        $data['merk_ban'] = !empty($data['merk_ban_lain']) ? $data['merk_ban_lain'] : $data['merk_ban'];
    }
    
    return [
        'nama_lengkap' => $data['nama_lengkap'],
        'truck_brand' => $data['truck_brand'],
        'jenis_truck' => $data['jenis_truck'],
        'tgl_pengecekan' => $data['tgl_pengecekan'],
        'customer' => $data['customer'],
        'nopol' => $data['nopol'],
        'km_odo' => $data['km_odo'],
        'posisi_ban' => $data['posisi_ban'],
        'ukuran_ban' => $data['ukuran_ban'],
        'merk_ban' => $data['merk_ban'],
        'pattern_ban' => $data['pattern_ban'],
        'depth_ban' => $data['depth_ban'],
        'pressure' => $data['pressure'],
        'tread' => $data['tread'],
        'side_wall' => $data['side_wall'],
        'kondisi_ban' => $data['kondisi_ban'],
        'foto_km_odo' => $filePaths['foto_KMODO'] ?? null,
        'foto_merk_ban' => $filePaths['foto_merkban'] ?? null,
        'foto_tread' => $filePaths['foto_tread'] ?? null,
        'foto_sidewall' => $filePaths['foto_sidewall'] ?? null,
        'foto_kondisi_ban' => $filePaths['foto_kondisiban'] ?? null,
        'created_at' => date('Y-m-d H:i:s')
    ];
}

function jsonResponse($success, $message, $data = []) {
    $response = ['success' => $success, 'message' => $message];
    if (!empty($data)) {
        $response['data'] = $data;
    }
    return json_encode($response);
}
?>