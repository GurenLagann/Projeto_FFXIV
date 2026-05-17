<?php

namespace Database\Seeders;

use App\Models\Server;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServerSeeder extends Seeder
{
    public function run(): void
    {
        $servers = [
            // NA — Aether
            ['name' => 'Adamantoise', 'datacenter' => 'Aether',   'region' => 'NA'],
            ['name' => 'Cactuar',     'datacenter' => 'Aether',   'region' => 'NA'],
            ['name' => 'Faerie',      'datacenter' => 'Aether',   'region' => 'NA'],
            ['name' => 'Gilgamesh',   'datacenter' => 'Aether',   'region' => 'NA'],
            ['name' => 'Jenova',      'datacenter' => 'Aether',   'region' => 'NA'],
            ['name' => 'Midgardsormr','datacenter' => 'Aether',   'region' => 'NA'],
            ['name' => 'Sargatanas',  'datacenter' => 'Aether',   'region' => 'NA'],
            ['name' => 'Siren',       'datacenter' => 'Aether',   'region' => 'NA'],
            // NA — Crystal
            ['name' => 'Balmung',     'datacenter' => 'Crystal',  'region' => 'NA'],
            ['name' => 'Brynhildr',   'datacenter' => 'Crystal',  'region' => 'NA'],
            ['name' => 'Coeurl',      'datacenter' => 'Crystal',  'region' => 'NA'],
            ['name' => 'Diabolos',    'datacenter' => 'Crystal',  'region' => 'NA'],
            ['name' => 'Goblin',      'datacenter' => 'Crystal',  'region' => 'NA'],
            ['name' => 'Malboro',     'datacenter' => 'Crystal',  'region' => 'NA'],
            ['name' => 'Mateus',      'datacenter' => 'Crystal',  'region' => 'NA'],
            ['name' => 'Zalera',      'datacenter' => 'Crystal',  'region' => 'NA'],
            // NA — Dynamis
            ['name' => 'Halicarnassus','datacenter'=> 'Dynamis',  'region' => 'NA'],
            ['name' => 'Maduin',      'datacenter' => 'Dynamis',  'region' => 'NA'],
            ['name' => 'Marilith',    'datacenter' => 'Dynamis',  'region' => 'NA'],
            ['name' => 'Seraph',      'datacenter' => 'Dynamis',  'region' => 'NA'],
            // NA — Primal
            ['name' => 'Behemoth',    'datacenter' => 'Primal',   'region' => 'NA'],
            ['name' => 'Excalibur',   'datacenter' => 'Primal',   'region' => 'NA'],
            ['name' => 'Exodus',      'datacenter' => 'Primal',   'region' => 'NA'],
            ['name' => 'Famfrit',     'datacenter' => 'Primal',   'region' => 'NA'],
            ['name' => 'Hyperion',    'datacenter' => 'Primal',   'region' => 'NA'],
            ['name' => 'Lamia',       'datacenter' => 'Primal',   'region' => 'NA'],
            ['name' => 'Leviathan',   'datacenter' => 'Primal',   'region' => 'NA'],
            ['name' => 'Ultros',      'datacenter' => 'Primal',   'region' => 'NA'],
            // EU — Chaos
            ['name' => 'Cerberus',    'datacenter' => 'Chaos',    'region' => 'EU'],
            ['name' => 'Louisoix',    'datacenter' => 'Chaos',    'region' => 'EU'],
            ['name' => 'Moogle',      'datacenter' => 'Chaos',    'region' => 'EU'],
            ['name' => 'Omega',       'datacenter' => 'Chaos',    'region' => 'EU'],
            ['name' => 'Phantom',     'datacenter' => 'Chaos',    'region' => 'EU'],
            ['name' => 'Ragnarok',    'datacenter' => 'Chaos',    'region' => 'EU'],
            ['name' => 'Sagittarius', 'datacenter' => 'Chaos',    'region' => 'EU'],
            ['name' => 'Spriggan',    'datacenter' => 'Chaos',    'region' => 'EU'],
            // EU — Light
            ['name' => 'Alpha',       'datacenter' => 'Light',    'region' => 'EU'],
            ['name' => 'Lich',        'datacenter' => 'Light',    'region' => 'EU'],
            ['name' => 'Odin',        'datacenter' => 'Light',    'region' => 'EU'],
            ['name' => 'Phoenix',     'datacenter' => 'Light',    'region' => 'EU'],
            ['name' => 'Raiden',      'datacenter' => 'Light',    'region' => 'EU'],
            ['name' => 'Shiva',       'datacenter' => 'Light',    'region' => 'EU'],
            ['name' => 'Twintania',   'datacenter' => 'Light',    'region' => 'EU'],
            ['name' => 'Zodiark',     'datacenter' => 'Light',    'region' => 'EU'],
            // JP — Elemental
            ['name' => 'Aegis',       'datacenter' => 'Elemental','region' => 'JP'],
            ['name' => 'Atomos',      'datacenter' => 'Elemental','region' => 'JP'],
            ['name' => 'Carbuncle',   'datacenter' => 'Elemental','region' => 'JP'],
            ['name' => 'Garuda',      'datacenter' => 'Elemental','region' => 'JP'],
            ['name' => 'Gungnir',     'datacenter' => 'Elemental','region' => 'JP'],
            ['name' => 'Kujata',      'datacenter' => 'Elemental','region' => 'JP'],
            ['name' => 'Tonberry',    'datacenter' => 'Elemental','region' => 'JP'],
            ['name' => 'Typhon',      'datacenter' => 'Elemental','region' => 'JP'],
            // JP — Gaia
            ['name' => 'Alexander',   'datacenter' => 'Gaia',     'region' => 'JP'],
            ['name' => 'Bahamut',     'datacenter' => 'Gaia',     'region' => 'JP'],
            ['name' => 'Durandal',    'datacenter' => 'Gaia',     'region' => 'JP'],
            ['name' => 'Fenrir',      'datacenter' => 'Gaia',     'region' => 'JP'],
            ['name' => 'Ifrit',       'datacenter' => 'Gaia',     'region' => 'JP'],
            ['name' => 'Ridill',      'datacenter' => 'Gaia',     'region' => 'JP'],
            ['name' => 'Tiamat',      'datacenter' => 'Gaia',     'region' => 'JP'],
            ['name' => 'Ultima',      'datacenter' => 'Gaia',     'region' => 'JP'],
            // JP — Mana
            ['name' => 'Anima',       'datacenter' => 'Mana',     'region' => 'JP'],
            ['name' => 'Asura',       'datacenter' => 'Mana',     'region' => 'JP'],
            ['name' => 'Chocobo',     'datacenter' => 'Mana',     'region' => 'JP'],
            ['name' => 'Hades',       'datacenter' => 'Mana',     'region' => 'JP'],
            ['name' => 'Ixion',       'datacenter' => 'Mana',     'region' => 'JP'],
            ['name' => 'Masamune',    'datacenter' => 'Mana',     'region' => 'JP'],
            ['name' => 'Pandaemonium','datacenter' => 'Mana',     'region' => 'JP'],
            ['name' => 'Titan',       'datacenter' => 'Mana',     'region' => 'JP'],
            // JP — Meteor
            ['name' => 'Belias',      'datacenter' => 'Meteor',   'region' => 'JP'],
            ['name' => 'Mandragora',  'datacenter' => 'Meteor',   'region' => 'JP'],
            ['name' => 'Ramuh',       'datacenter' => 'Meteor',   'region' => 'JP'],
            ['name' => 'Shinryu',     'datacenter' => 'Meteor',   'region' => 'JP'],
            ['name' => 'Unicorn',     'datacenter' => 'Meteor',   'region' => 'JP'],
            ['name' => 'Valefor',     'datacenter' => 'Meteor',   'region' => 'JP'],
            ['name' => 'Yojimbo',     'datacenter' => 'Meteor',   'region' => 'JP'],
            ['name' => 'Zeromus',     'datacenter' => 'Meteor',   'region' => 'JP'],
            // OCE — Materia
            ['name' => 'Bismarck',    'datacenter' => 'Materia',  'region' => 'OCE'],
            ['name' => 'Ravana',      'datacenter' => 'Materia',  'region' => 'OCE'],
            ['name' => 'Sephirot',    'datacenter' => 'Materia',  'region' => 'OCE'],
            ['name' => 'Sophia',      'datacenter' => 'Materia',  'region' => 'OCE'],
            ['name' => 'Zurvan',      'datacenter' => 'Materia',  'region' => 'OCE'],
        ];

        foreach ($servers as $server) {
            Server::updateOrCreate(
                ['name' => $server['name']],
                [
                    'slug'       => Str::slug($server['name']),
                    'datacenter' => $server['datacenter'],
                    'region'     => $server['region'],
                    'is_active'  => true,
                ]
            );
        }
    }
}
