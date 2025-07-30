<?php
// app/Http/Controllers/Admin/QRGeneratorController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRGeneratorController extends Controller
{
    /**
     * Afficher la page de génération des QR codes
     */
    public function index()
    {
        $rooms = Room::orderBy('order')->get();
        return view('admin.qr-generator', compact('rooms'));
    }

    /**
     * Générer un QR code pour une salle
     */
    public function generate($roomId)
    {
        $room = Room::findOrFail($roomId);
        
        // Générer le QR code en SVG
        $qrCode = QrCode::size(300)
            ->margin(2)
            ->generate($room->qr_code);
            
        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Page d'impression de tous les QR codes
     */
    public function printAll()
    {
        $rooms = Room::orderBy('order')->get();
        
        $qrCodes = [];
        foreach ($rooms as $room) {
            $qrCodes[$room->id] = base64_encode(
                QrCode::size(250)
                    ->margin(1)
                    ->generate($room->qr_code)
            );
        }
        
        return view('admin.qr-print', compact('rooms', 'qrCodes'));
    }
}

// N'oubliez pas d'installer le package QR Code :
// composer require simplesoftwareio/simple-qrcode