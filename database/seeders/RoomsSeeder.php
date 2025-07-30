<?php
// database/seeders/RoomsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomsSeeder extends Seeder
{
    public function run()
    {
        $rooms = [
            [
                'order' => 1,
                'slug' => 'galerie-commercante',
                'name' => 'La galerie commerçante',
                'description' => 'Quelle clé est la bonne et quelle porte ouvre-t-elle ?',
                'qr_code' => 'QR_GALERIE_001',
                'type' => 'main',
                'mini_game_config' => json_encode([
                    'type' => 'key_match',
                    'doors' => 5,
                    'keys' => 5
                ]),
                'estimated_time' => 300
            ],
            [
                'order' => 2,
                'slug' => 'salon-coiffure',
                'name' => 'Le salon de coiffure',
                'description' => 'Un salon vidé de tout son mobilier',
                'qr_code' => 'QR_SALON_002',
                'type' => 'main',
                'digit_reward' => 1,
                'mini_game_config' => json_encode([
                    'type' => 'hair_styling',
                    'tools' => ['peigne', 'ciseaux', 'seche-cheveux']
                ]),
                'estimated_time' => 240
            ],
            [
                'order' => 3,
                'slug' => 'cave-salon',
                'name' => 'La cave du salon',
                'description' => 'Un espace vide avec une porte vers la cour intérieure',
                'qr_code' => 'QR_CAVE_003',
                'type' => 'main',
                'mini_game_config' => json_encode([
                    'type' => 'maze',
                    'difficulty' => 'easy'
                ]),
                'estimated_time' => 180
            ],
            [
                'order' => 4,
                'slug' => 'onze-caves',
                'name' => 'Les 11 caves',
                'description' => 'Un dédale de caves contenant des fragments de carte',
                'qr_code' => 'QR_CAVES_004',
                'type' => 'main',
                'digit_reward' => 2,
                'mini_game_config' => json_encode([
                    'type' => 'fragment_collection',
                    'total_fragments' => 11,
                    'fake_fragments' => [10, 11],
                    'mini_games' => ['memory', 'rotation', 'find_intruder']
                ]),
                'estimated_time' => 900
            ],
            [
                'order' => 5,
                'slug' => 'entree-1933',
                'name' => 'La vieille entrée de 1933',
                'description' => 'Une entrée historique avec une plaque mystérieuse',
                'qr_code' => 'QR_ENTREE_005',
                'type' => 'main',
                'mini_game_config' => json_encode([
                    'type' => 'date_decode',
                    'has_branch' => true
                ]),
                'estimated_time' => 300
            ],
            [
                'order' => 5.1,
                'slug' => 'chez-guy',
                'name' => 'Chez Guy',
                'description' => 'Bureau secret accessible aux explorateurs',
                'qr_code' => 'QR_GUY_005B',
                'type' => 'branch',
                'parent_room_id' => 5,
                'mini_game_config' => json_encode([
                    'type' => 'photo_1930',
                    'max_players' => 3
                ]),
                'estimated_time' => 180
            ],
            [
                'order' => 6,
                'slug' => 'vieux-bar',
                'name' => 'Le vieux bar',
                'description' => 'Un bar abandonné avec une cuisine mystérieuse',
                'qr_code' => 'QR_BAR_006',
                'type' => 'main',
                'mini_game_config' => json_encode([
                    'type' => 'cocktail_recipe',
                    'ingredients' => ['vodka', 'citron', 'menthe', 'gin', 'olive', 'cerise'],
                    'correct' => ['vodka', 'citron', 'menthe']
                ]),
                'estimated_time' => 420
            ],
            [
                'order' => 7,
                'slug' => 'centre-controle',
                'name' => 'Le centre de contrôle',
                'description' => 'Une salle avec une grande baie vitrée donnant sur la galerie',
                'qr_code' => 'QR_CONTROLE_007',
                'type' => 'main',
                'digit_reward' => 3,
                'mini_game_config' => json_encode([
                    'type' => 'light_pattern',
                    'sequence_length' => 5,
                    'max_attempts' => 3
                ]),
                'estimated_time' => 360
            ],
            [
                'order' => 8,
                'slug' => 'bureau-desaffecte',
                'name' => 'Le bureau désaffecté',
                'description' => 'Un dédale de 5 bureaux',
                'qr_code' => 'QR_BUREAU_008',
                'type' => 'main',
                'mini_game_config' => json_encode([
                    'type' => 'hot_cold_search',
                    'penalty_seconds' => 30
                ]),
                'estimated_time' => 300
            ],
            [
                'order' => 9,
                'slug' => 'bureaux-pluie',
                'name' => 'Bureaux sous la pluie',
                'description' => 'Des bureaux où le plafond est tombé par endroits',
                'qr_code' => 'QR_PLUIE_009',
                'type' => 'main',
                'digit_reward' => 4,
                'mini_game_config' => json_encode([
                    'type' => 'rain_pattern',
                    'stillness_duration' => 10,
                    'hint_delay' => 30
                ]),
                'estimated_time' => 360
            ],
            [
                'order' => 10,
                'slug' => 'aile-nord',
                'name' => 'Aile nord',
                'description' => 'L\'accès final avec la boîte à clé',
                'qr_code' => 'QR_NORD_010',
                'type' => 'main',
                'mini_game_config' => json_encode([
                    'type' => 'code_validation',
                    'physical_box' => true
                ]),
                'estimated_time' => 120
            ],
            [
                'order' => 11,
                'slug' => 'toit',
                'name' => 'Le toit',
                'description' => 'La finale spectaculaire avec l\'hélicoptère',
                'qr_code' => 'QR_TOIT_011',
                'type' => 'main',
                'mini_game_config' => json_encode([
                    'type' => 'finale',
                    'ar_helicopter' => true,
                    'photo_mode' => true
                ]),
                'estimated_time' => 180
            ]
        ];

        DB::table('rooms')->insert($rooms);
    }
}