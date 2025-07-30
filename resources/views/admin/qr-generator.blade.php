<!-- resources/views/admin/qr-generator.blade.php -->
@extends('layouts.app')

@section('title', 'Générateur de QR Codes')
@section('header', 'QR Codes des salles')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- En-tête avec bouton d'impression -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-100">Générateur de QR Codes</h2>
            <p class="text-gray-400 mt-1">{{ count($rooms) }} salles configurées</p>
        </div>
        <a href="{{ route('admin.qr.print') }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Imprimer tous les QR codes
        </a>
    </div>

    <!-- Grille des QR codes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($rooms as $room)
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl">
            <!-- En-tête de la carte -->
            <div class="bg-gray-700 p-4">
                <h3 class="font-bold text-lg text-white">{{ $room->name }}</h3>
                <p class="text-sm text-gray-300 mt-1">
                    @if($room->type === 'branch')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-900 text-yellow-200">
                            Embranchement
                        </span>
                    @else
                        Salle {{ $room->order }}
                    @endif
                </p>
            </div>
            
            <!-- QR Code -->
            <div class="p-6 bg-white flex justify-center">
                <div class="text-center">
                    <img src="{{ route('admin.qr.generate', $room->id) }}" alt="QR Code {{ $room->name }}" class="w-48 h-48">
                    <p class="mt-2 text-xs text-gray-600 font-mono">{{ $room->qr_code }}</p>
                </div>
            </div>
            
            <!-- Informations -->
            <div class="bg-gray-700 p-4 space-y-2">
                @if($room->description)
                <p class="text-sm text-gray-300">{{ $room->description }}</p>
                @endif
                
                @if($room->digit_reward)
                <div class="flex items-center text-sm">
                    <svg class="w-4 h-4 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <span class="text-gray-300">Chiffre {{ $room->digit_reward }} du code</span>
                </div>
                @endif
                
                <div class="flex items-center text-sm">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-gray-400">~{{ intval($room->estimated_time / 60) }} minutes</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Instructions d'utilisation -->
    <div class="mt-8 bg-gray-800 rounded-lg p-6">
        <h3 class="font-semibold text-indigo-400 mb-3">Instructions d'impression :</h3>
        <ul class="space-y-2 text-gray-300">
            <li class="flex items-start">
                <span class="text-indigo-400 mr-2">1.</span>
                <span>Cliquez sur "Imprimer tous les QR codes" pour ouvrir la page d'impression</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 mr-2">2.</span>
                <span>Imprimez sur du papier A4 en mode portrait</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 mr-2">3.</span>
                <span>Découpez chaque QR code avec son étiquette</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 mr-2">4.</span>
                <span>Plastifiez les QR codes pour une meilleure durabilité</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 mr-2">5.</span>
                <span>Fixez chaque QR code à l'entrée de la salle correspondante</span>
            </li>
        </ul>
    </div>
</div>
@endsection