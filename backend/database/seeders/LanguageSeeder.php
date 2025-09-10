<?php

namespace Database\Seeders;

use App\Domain\Localization\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'flag_emoji' => '🇺🇸',
                'direction' => 'ltr',
                'date_format' => 'MM/DD/YYYY',
                'time_format' => '12h',
                'currency_code' => 'USD',
                'currency_position' => 'before',
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'decimal_places' => 2,
                'phone_format' => [
                    'format' => '+1 (XXX) XXX-XXXX',
                    'example' => '+1 (555) 123-4567'
                ],
                'address_format' => [
                    'format' => 'street\ncity, state zip\ncountry',
                    'fields' => ['street', 'city', 'state', 'zip', 'country']
                ],
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
                'native_name' => 'Español',
                'flag_emoji' => '🇪🇸',
                'direction' => 'ltr',
                'date_format' => 'DD/MM/YYYY',
                'time_format' => '24h',
                'currency_code' => 'EUR',
                'currency_position' => 'after',
                'thousand_separator' => '.',
                'decimal_separator' => ',',
                'decimal_places' => 2,
                'phone_format' => [
                    'format' => '+34 XXX XXX XXX',
                    'example' => '+34 912 345 678'
                ],
                'address_format' => [
                    'format' => 'street\nzip city\nprovince, country',
                    'fields' => ['street', 'zip', 'city', 'province', 'country']
                ],
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'flag_emoji' => '🇫🇷',
                'direction' => 'ltr',
                'date_format' => 'DD/MM/YYYY',
                'time_format' => '24h',
                'currency_code' => 'EUR',
                'currency_position' => 'after',
                'thousand_separator' => ' ',
                'decimal_separator' => ',',
                'decimal_places' => 2,
                'phone_format' => [
                    'format' => '+33 X XX XX XX XX',
                    'example' => '+33 1 42 34 56 78'
                ],
                'address_format' => [
                    'format' => 'street\nzip city\ncountry',
                    'fields' => ['street', 'zip', 'city', 'country']
                ],
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
                'flag_emoji' => '🇩🇪',
                'direction' => 'ltr',
                'date_format' => 'DD.MM.YYYY',
                'time_format' => '24h',
                'currency_code' => 'EUR',
                'currency_position' => 'after',
                'thousand_separator' => '.',
                'decimal_separator' => ',',
                'decimal_places' => 2,
                'phone_format' => [
                    'format' => '+49 XXX XXXXXXX',
                    'example' => '+49 30 12345678'
                ],
                'address_format' => [
                    'format' => 'street\nzip city\ncountry',
                    'fields' => ['street', 'zip', 'city', 'country']
                ],
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'flag_emoji' => '🇸🇦',
                'direction' => 'rtl',
                'date_format' => 'DD/MM/YYYY',
                'time_format' => '12h',
                'currency_code' => 'SAR',
                'currency_position' => 'before',
                'thousand_separator' => '،',
                'decimal_separator' => '.',
                'decimal_places' => 2,
                'phone_format' => [
                    'format' => '+966 XX XXX XXXX',
                    'example' => '+966 50 123 4567'
                ],
                'address_format' => [
                    'format' => 'street\ncity zip\nregion, country',
                    'fields' => ['street', 'city', 'zip', 'region', 'country']
                ],
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }

        $this->command->info('Languages seeded successfully.');
    }
}