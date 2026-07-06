<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@firefly.com'],
            [
                'name' => 'Admin',
                'last_name' => 'Firefly',
                'document_number' => '00000001',
                'phone' => '999000001',
                'company' => 'La Positiva Seguros',
                'platform' => 'Mi Asesor Positivo',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'aylasmorenof@gmail.com'],
            [
                'name' => 'Fabricio',
                'last_name' => 'Aylas Moreno',
                'document_number' => '00000002',
                'phone' => '999000002',
                'company' => 'La Positiva Seguros',
                'platform' => 'Mi Asesor Positivo',
                'role' => 'admin',
                'password' => Hash::make('Fam151004'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'aylasmoreno@gmail.com'],
            [
                'name' => 'Fabricio',
                'last_name' => 'Aylas Moreno',
                'document_number' => '00000003',
                'phone' => '999000003',
                'company' => 'La Positiva Seguros',
                'platform' => 'Mi Asesor Positivo',
                'role' => 'usuario',
                'password' => Hash::make('Fam151004'),
            ]
        );

        $tiUsers = [
            ['name' => 'Carlos', 'last_name' => 'Mendoza', 'email' => 'carlos.ti@firefly.com', 'platform' => 'Mi Asesor Positivo'],
            ['name' => 'Daniel', 'last_name' => 'Rojas', 'email' => 'daniel.ti@firefly.com', 'platform' => 'Planning Corredores'],
            ['name' => 'Elena', 'last_name' => 'Paredes', 'email' => 'elena.ti@firefly.com', 'platform' => 'Protechting 2.0'],
        ];

        foreach ($tiUsers as $i => $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'last_name' => $u['last_name'],
                    'document_number' => '1000000' . $i,
                    'phone' => '99910000' . $i,
                    'company' => 'La Positiva Seguros',
                    'platform' => $u['platform'],
                    'role' => 'ti',
                    'password' => Hash::make('password'),
                ]
            );
        }

        $companies = ['La Positiva Seguros', 'Sanna', 'Profuturo', 'Mapfre'];
        $platforms = ['Mi Asesor Positivo', 'Planning Corredores', 'Protechting 2.0'];

        foreach ($companies as $i => $company) {
            User::updateOrCreate(
                ['email' => 'usuario' . ($i + 1) . '@firefly.com'],
                [
                    'name' => 'Usuario',
                    'last_name' => $company,
                    'document_number' => '2000000' . $i,
                    'phone' => '99920000' . $i,
                    'company' => $company,
                    'platform' => $platforms[$i % count($platforms)],
                    'role' => 'usuario',
                    'password' => Hash::make('password'),
                ]
            );
        }

        $reportante = User::where('role', 'usuario')->inRandomOrder()->first();
        $ti = User::where('role', 'ti')->get();

        $tickets = [
            [
                'issue_type' => 'Error de sistema',
                'module' => 'Login',
                'description' => 'No se puede iniciar sesión con credenciales válidas.',
                'status' => 'pendiente',
                'priority' => 1,
            ],
            [
                'issue_type' => 'Duda funcional',
                'module' => 'Reportes',
                'description' => 'No queda claro cómo exportar el reporte mensual.',
                'status' => 'asignado_ti',
                'priority' => 2,
                'assigned_to' => $ti->first()?->id,
                'due_date' => now()->addHours(6),
            ],
            [
                'issue_type' => 'Error de sistema',
                'module' => 'Gestión de usuarios',
                'description' => 'Al editar un usuario se pierde el rol asignado.',
                'status' => 'en_curso',
                'priority' => 1,
                'assigned_to' => $ti->skip(1)->first()?->id,
                'due_date' => now()->addHours(2),
            ],
            [
                'issue_type' => 'Solicitud de mejora',
                'module' => 'Toda la plataforma',
                'description' => 'Agregar modo oscuro a la plataforma.',
                'status' => 'levantado',
                'priority' => 3,
                'assigned_to' => $ti->last()?->id,
                'due_date' => now()->subHours(1),
            ],
            [
                'issue_type' => 'Error de sistema',
                'module' => 'Reportes',
                'description' => 'El PDF generado sale en blanco.',
                'status' => 'testing',
                'priority' => 2,
                'assigned_to' => $ti->first()?->id,
                'due_date' => now()->addHours(20),
            ],
        ];

        foreach ($tickets as $data) {
            $ticket = Ticket::create([
                'user_id' => $reportante?->id,
                'affected_user' => 'El problema me pasa a mí',
                'attachment' => null,
                ...$data,
            ]);

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'status' => 'pendiente',
                'type' => 'status_change',
                'comment' => 'Ticket registrado',
                'changed_by' => $reportante?->id,
            ]);

            if ($data['status'] !== 'pendiente') {
                TicketStatusHistory::create([
                    'ticket_id' => $ticket->id,
                    'status' => $data['status'],
                    'type' => 'status_change',
                    'comment' => 'Estado inicial de prueba (seeder)',
                    'changed_by' => $ticket->assigned_to ?? $reportante?->id,
                ]);
            }
        }
    }
}