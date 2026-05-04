<?php

namespace Database\Seeders;

use App\Models\AbsenceType;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\AttendanceRecord;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\ContactAddress;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentAccessRule;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Employee;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\InventoryBreakage;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\InventoryRestockRequest;
use App\Models\LeaveRequest;
use App\Models\MeetingMinute;
use App\Models\OperationalPlan;
use App\Models\OperationalPlanParticipant;
use App\Models\OperationalPlanResource;
use App\Models\OperationalPlanTask;
use App\Models\Organization;
use App\Models\RecurringOperation;
use App\Models\RecurringOperationRun;
use App\Models\Space;
use App\Models\SpaceCleaningRecord;
use App\Models\SpaceReservation;
use App\Models\SpaceReservationApproval;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskChecklistItem;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Ticket;
use App\Models\TicketStatusHistory;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use App\Services\Tickets\TicketReferenceGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private Organization $organization;

    /** @var array<string, User> */
    private array $users = [];

    /** @var array<string, Contact> */
    private array $contacts = [];

    /** @var array<string, Department> */
    private array $departments = [];

    /** @var array<string, Team> */
    private array $teams = [];

    /** @var array<string, Employee> */
    private array $employees = [];

    /** @var array<string, Space> */
    private array $spaces = [];

    /** @var array<string, InventoryCategory> */
    private array $inventoryCategories = [];

    /** @var array<string, InventoryLocation> */
    private array $inventoryLocations = [];

    /** @var array<string, InventoryItem> */
    private array $inventoryItems = [];

    /** @var array<string, Ticket> */
    private array $tickets = [];

    /** @var array<string, Task> */
    private array $tasks = [];

    /** @var array<string, Event> */
    private array $events = [];

    /** @var array<string, Document> */
    private array $documents = [];

    /** @var array<string, OperationalPlan> */
    private array $plans = [];

    private ActivityLogger $activityLogger;

    private TicketReferenceGenerator $ticketReferenceGenerator;

    public function run(): void
    {
        $this->organization = Organization::query()->firstOrCreate(
            ['slug' => 'junta-freguesia-demo'],
            [
                'name' => 'Junta de Freguesia Demo',
                'code' => 'JFD',
                'email' => 'geral@junta-demo.pt',
                'is_active' => true,
            ],
        );

        $this->activityLogger = app(ActivityLogger::class);
        $this->ticketReferenceGenerator = app(TicketReferenceGenerator::class);

        $this->seedUsers();
        $this->seedContactsAndAddresses();
        $this->seedSpaces();
        $this->seedHr();
        $this->seedTickets();
        $this->seedTasks();
        $this->seedEvents();
        $this->seedSpaceReservations();
        $this->seedDocumentsAndMeetingMinutes();
        $this->seedInventory();
        $this->seedPlanning();
        $this->seedAttachments();
    }

    private function seedUsers(): void
    {
        $definitions = [
            ['email' => 'admin@juntaos.local', 'name' => 'Administrador', 'role' => 'super_admin'],
            ['email' => 'executivo@juntaos.local', 'name' => 'Carla Marques', 'role' => 'executivo'],
            ['email' => 'administrativo@juntaos.local', 'name' => 'Filipa Costa', 'role' => 'administrativo'],
            ['email' => 'operacional@juntaos.local', 'name' => 'Rui Santos', 'role' => 'operacional'],
            ['email' => 'manutencao@juntaos.local', 'name' => 'Joao Pereira', 'role' => 'manutencao'],
            ['email' => 'armazem@juntaos.local', 'name' => 'Ana Ferreira', 'role' => 'armazem'],
            ['email' => 'rh@juntaos.local', 'name' => 'Marta Lima', 'role' => 'rh'],
            ['email' => 'cidadao@juntaos.local', 'name' => 'Miguel Almeida', 'role' => 'cidadao'],
            ['email' => 'associacao@juntaos.local', 'name' => 'Associacao Cultural do Bairro', 'role' => 'associacao'],
        ];

        foreach ($definitions as $definition) {
            $user = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => Hash::make('password'),
                    'organization_id' => $this->organization->id,
                    'is_active' => true,
                ],
            );

            $user->forceFill(['email_verified_at' => now()])->save();
            $user->syncRoles([$definition['role']]);

            $this->users[$definition['email']] = $user;
        }
    }

    private function seedContactsAndAddresses(): void
    {
        $definitions = [
            [
                'key' => 'miguel',
                'name' => 'Miguel Almeida',
                'type' => 'citizen',
                'email' => 'cidadao@juntaos.local',
                'phone' => '912345678',
                'user_email' => 'cidadao@juntaos.local',
                'notes' => 'Cidadao residente na freguesia.',
            ],
            [
                'key' => 'associacao',
                'name' => 'Associacao Cultural do Bairro',
                'type' => 'association',
                'email' => 'associacao@juntaos.local',
                'phone' => '213456789',
                'user_email' => 'associacao@juntaos.local',
                'notes' => 'Entidade parceira de iniciativas culturais.',
            ],
            [
                'key' => 'escola',
                'name' => 'Escola Basica do Centro',
                'type' => 'institution',
                'email' => 'geral@ebcentro.pt',
                'phone' => '210000001',
                'notes' => 'Instituicao de ensino local.',
            ],
            [
                'key' => 'clube',
                'name' => 'Clube Desportivo Local',
                'type' => 'association',
                'email' => 'geral@cdlocal.pt',
                'phone' => '210000002',
                'notes' => 'Associacao desportiva da freguesia.',
            ],
            [
                'key' => 'limpezas',
                'name' => 'Limpezas Urbanas Lda',
                'type' => 'supplier',
                'email' => 'comercial@limpezasurbanas.pt',
                'phone' => '210000003',
                'notes' => 'Fornecedor de servicos de higiene urbana.',
            ],
            [
                'key' => 'maria',
                'name' => 'Maria Fernanda Lopes',
                'type' => 'citizen',
                'email' => 'maria.lopes@example.test',
                'phone' => '910000004',
                'notes' => 'Cidada da freguesia.',
            ],
        ];

        foreach ($definitions as $definition) {
            $contact = Contact::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'email' => $definition['email'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'user_id' => isset($definition['user_email']) ? $this->users[$definition['user_email']]->id : null,
                    'type' => $definition['type'],
                    'name' => $definition['name'],
                    'phone' => $definition['phone'],
                    'mobile' => $definition['phone'],
                    'notes' => $definition['notes'],
                    'is_active' => true,
                ],
            );

            $this->contacts[$definition['key']] = $contact;
        }

        $addresses = [
            ['contact' => 'miguel', 'address' => 'Rua da Escola Basica, 12', 'postal_code' => '1200-101', 'locality' => 'Lisboa'],
            ['contact' => 'associacao', 'address' => 'Largo da Liberdade, 3', 'postal_code' => '1200-102', 'locality' => 'Lisboa'],
            ['contact' => 'escola', 'address' => 'Avenida Central, 45', 'postal_code' => '1200-103', 'locality' => 'Lisboa'],
            ['contact' => 'maria', 'address' => 'Rua do Mercado, 8', 'postal_code' => '1200-104', 'locality' => 'Lisboa'],
            ['contact' => 'clube', 'address' => 'Centro Comunitario do Calhariz', 'postal_code' => '1200-105', 'locality' => 'Lisboa'],
        ];

        foreach ($addresses as $address) {
            ContactAddress::query()->firstOrCreate(
                [
                    'contact_id' => $this->contacts[$address['contact']]->id,
                    'address' => $address['address'],
                ],
                [
                    'type' => 'main',
                    'postal_code' => $address['postal_code'],
                    'locality' => $address['locality'],
                    'parish' => 'Junta Demo',
                    'municipality' => 'Lisboa',
                    'district' => 'Lisboa',
                    'is_primary' => true,
                ],
            );
        }
    }

    private function seedSpaces(): void
    {
        $definitions = [
            [
                'key' => 'sala-reunioes',
                'name' => 'Sala de Reunioes',
                'capacity' => 12,
                'status' => 'available',
                'requires_approval' => true,
                'has_cleaning_required' => false,
                'is_public' => false,
                'location_text' => 'Edificio da Junta',
            ],
            [
                'key' => 'auditorio-junta',
                'name' => 'Auditorio da Junta',
                'capacity' => 80,
                'status' => 'available',
                'requires_approval' => true,
                'has_cleaning_required' => true,
                'is_public' => true,
                'location_text' => 'Edificio da Junta',
            ],
            [
                'key' => 'sala-polivalente',
                'name' => 'Sala Polivalente',
                'capacity' => 40,
                'status' => 'available',
                'requires_approval' => true,
                'has_cleaning_required' => true,
                'is_public' => true,
                'location_text' => 'Edificio da Junta',
            ],
            [
                'key' => 'centro-calhariz',
                'name' => 'Centro Comunitario do Calhariz',
                'capacity' => 60,
                'status' => 'available',
                'requires_approval' => true,
                'has_cleaning_required' => true,
                'is_public' => true,
                'location_text' => 'Calhariz',
            ],
            [
                'key' => 'campo-jogos',
                'name' => 'Campo de Jogos',
                'capacity' => 120,
                'status' => 'available',
                'requires_approval' => true,
                'has_cleaning_required' => false,
                'is_public' => true,
                'location_text' => 'Parque Municipal',
            ],
        ];

        foreach ($definitions as $definition) {
            $slug = Str::slug($definition['name']);
            $space = Space::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'slug' => $slug,
                ],
                [
                    'organization_id' => $this->organization->id,
                    'name' => $definition['name'],
                    'slug' => $slug,
                    'description' => 'Espaco municipal para uso institucional e comunitario.',
                    'location_text' => $definition['location_text'],
                    'capacity' => $definition['capacity'],
                    'status' => $definition['status'],
                    'requires_approval' => $definition['requires_approval'],
                    'has_cleaning_required' => $definition['has_cleaning_required'],
                    'has_deposit' => false,
                    'deposit_amount' => null,
                    'price' => null,
                    'rules' => 'Uso sujeito a regulamento interno da Junta.',
                    'is_public' => $definition['is_public'],
                    'is_active' => true,
                ],
            );

            $this->spaces[$definition['key']] = $space;
        }
    }

    private function seedTickets(): void
    {
        $definitions = [
            [
                'title' => 'Buraco na via publica',
                'category' => 'Espaco Publico',
                'priority' => 'high',
                'status' => 'em_execucao',
                'source' => 'portal',
                'contact' => 'miguel',
                'assigned_to' => 'operacional@juntaos.local',
                'created_by' => 'administrativo@juntaos.local',
                'description' => 'Foi identificado um buraco junto a passadeira na Rua da Escola Basica, representando risco para peoes e veiculos.',
                'location_text' => 'Rua da Escola Basica',
                'due_days' => 3,
            ],
            [
                'title' => 'Pedido de emissao de atestado',
                'category' => 'Atestados',
                'priority' => 'normal',
                'status' => 'em_analise',
                'source' => 'portal',
                'contact' => 'maria',
                'assigned_to' => 'administrativo@juntaos.local',
                'created_by' => 'cidadao@juntaos.local',
                'description' => 'Solicito emissao de atestado de residencia para efeitos escolares.',
                'location_text' => null,
                'due_days' => 5,
            ],
            [
                'title' => 'Reparacao de candeeiro',
                'category' => 'Iluminacao Publica',
                'priority' => 'high',
                'status' => 'encaminhado',
                'source' => 'phone',
                'contact' => 'miguel',
                'assigned_to' => 'manutencao@juntaos.local',
                'created_by' => 'administrativo@juntaos.local',
                'description' => 'Candeeiro avariado junto ao largo principal.',
                'location_text' => 'Largo da Liberdade',
                'due_days' => 2,
            ],
            [
                'title' => 'Pedido de apoio social',
                'category' => 'Acao Social',
                'priority' => 'normal',
                'status' => 'em_analise',
                'source' => 'portal',
                'contact' => 'maria',
                'assigned_to' => 'rh@juntaos.local',
                'created_by' => 'administrativo@juntaos.local',
                'description' => 'Pedido de encaminhamento para apoio social pontual.',
                'location_text' => null,
                'due_days' => 7,
            ],
            [
                'title' => 'Solicitacao de recolha de monos',
                'category' => 'Higiene Urbana',
                'priority' => 'low',
                'status' => 'novo',
                'source' => 'portal',
                'contact' => 'miguel',
                'assigned_to' => null,
                'created_by' => 'cidadao@juntaos.local',
                'description' => 'Pedido de recolha de moveis antigos.',
                'location_text' => 'Rua do Mercado',
                'due_days' => 10,
            ],
            [
                'title' => 'Licenca para ocupacao de via',
                'category' => 'Licenciamentos',
                'priority' => 'normal',
                'status' => 'em_execucao',
                'source' => 'internal',
                'contact' => 'associacao',
                'assigned_to' => 'administrativo@juntaos.local',
                'created_by' => 'executivo@juntaos.local',
                'description' => 'Pedido de ocupacao temporaria da via para evento associativo.',
                'location_text' => 'Largo da Liberdade',
                'due_days' => 4,
            ],
            [
                'title' => 'Reclamacao sobre ruido',
                'category' => 'Fiscalizacao',
                'priority' => 'normal',
                'status' => 'aguarda_informacao',
                'source' => 'email',
                'contact' => 'maria',
                'assigned_to' => 'administrativo@juntaos.local',
                'created_by' => 'administrativo@juntaos.local',
                'description' => 'Queixa de ruido noturno recorrente.',
                'location_text' => 'Rua da Liberdade',
                'due_days' => 6,
            ],
            [
                'title' => 'Pedido de limpeza de sarjeta',
                'category' => 'Higiene Urbana',
                'priority' => 'high',
                'status' => 'novo',
                'source' => 'portal',
                'contact' => 'miguel',
                'assigned_to' => 'operacional@juntaos.local',
                'created_by' => 'cidadao@juntaos.local',
                'description' => 'Sarjeta obstruida apos chuva intensa.',
                'location_text' => 'Avenida Central',
                'due_days' => 2,
            ],
            [
                'title' => 'Manutencao de equipamento no parque infantil',
                'category' => 'Equipamentos',
                'priority' => 'urgent',
                'status' => 'em_execucao',
                'source' => 'presencial',
                'contact' => 'associacao',
                'assigned_to' => 'manutencao@juntaos.local',
                'created_by' => 'executivo@juntaos.local',
                'description' => 'Balanco com fixacao solta no parque infantil.',
                'location_text' => 'Parque Infantil do Calhariz',
                'due_days' => 1,
            ],
            [
                'title' => 'Pedido de cedencia de sala',
                'category' => 'Espacos',
                'priority' => 'normal',
                'status' => 'resolvido',
                'source' => 'portal',
                'contact' => 'associacao',
                'assigned_to' => 'administrativo@juntaos.local',
                'created_by' => 'associacao@juntaos.local',
                'description' => 'Pedido de cedencia de sala para assembleia da associacao.',
                'location_text' => 'Sala Polivalente',
                'due_days' => 1,
            ],
            [
                'title' => 'Informacao sobre atividades da freguesia',
                'category' => 'Informacao',
                'priority' => 'low',
                'status' => 'fechado',
                'source' => 'portal',
                'contact' => 'miguel',
                'assigned_to' => 'administrativo@juntaos.local',
                'created_by' => 'cidadao@juntaos.local',
                'description' => 'Pedido de informacao sobre agenda de atividades da freguesia.',
                'location_text' => null,
                'due_days' => null,
            ],
            [
                'title' => 'Pedido de poda de arvores',
                'category' => 'Espaco Verde',
                'priority' => 'normal',
                'status' => 'agendado',
                'source' => 'portal',
                'contact' => 'maria',
                'assigned_to' => 'operacional@juntaos.local',
                'created_by' => 'administrativo@juntaos.local',
                'description' => 'Solicitada poda preventiva de arvores junto ao passeio.',
                'location_text' => 'Rua do Mercado',
                'due_days' => 4,
            ],
        ];

        foreach ($definitions as $definition) {
            $ticket = Ticket::query()
                ->where('organization_id', $this->organization->id)
                ->where('title', $definition['title'])
                ->first();

            if (! $ticket) {
                $ticket = Ticket::query()->create([
                    'organization_id' => $this->organization->id,
                    'reference' => $this->ticketReferenceGenerator->generate($this->organization),
                    'created_by' => $this->users[$definition['created_by']]->id,
                    'contact_id' => isset($definition['contact']) ? $this->contacts[$definition['contact']]->id : null,
                    'assigned_to' => $definition['assigned_to'] ? $this->users[$definition['assigned_to']]->id : null,
                    'department_id' => null,
                    'category' => $definition['category'],
                    'priority' => $definition['priority'],
                    'status' => $definition['status'],
                    'title' => $definition['title'],
                    'description' => $definition['description'],
                    'location_text' => $definition['location_text'],
                    'source' => $definition['source'],
                    'visibility' => 'internal',
                    'due_date' => $definition['due_days'] !== null ? now()->addDays($definition['due_days'])->toDateString() : null,
                    'closed_at' => in_array($definition['status'], ['resolvido', 'fechado'], true) ? now()->subDay() : null,
                    'closed_by' => in_array($definition['status'], ['resolvido', 'fechado'], true) ? $this->users['administrativo@juntaos.local']->id : null,
                ]);
            } else {
                $ticket->update([
                    'created_by' => $this->users[$definition['created_by']]->id,
                    'contact_id' => isset($definition['contact']) ? $this->contacts[$definition['contact']]->id : null,
                    'assigned_to' => $definition['assigned_to'] ? $this->users[$definition['assigned_to']]->id : null,
                    'category' => $definition['category'],
                    'priority' => $definition['priority'],
                    'status' => $definition['status'],
                    'description' => $definition['description'],
                    'location_text' => $definition['location_text'],
                    'source' => $definition['source'],
                    'due_date' => $definition['due_days'] !== null ? now()->addDays($definition['due_days'])->toDateString() : null,
                    'closed_at' => in_array($definition['status'], ['resolvido', 'fechado'], true) ? now()->subDay() : null,
                    'closed_by' => in_array($definition['status'], ['resolvido', 'fechado'], true) ? $this->users['administrativo@juntaos.local']->id : null,
                ]);
            }

            $this->tickets[$definition['title']] = $ticket;

            TicketStatusHistory::query()->firstOrCreate(
                [
                    'ticket_id' => $ticket->id,
                    'new_status' => 'novo',
                ],
                [
                    'old_status' => null,
                    'changed_by' => $ticket->created_by,
                    'notes' => 'Estado inicial do pedido.',
                ],
            );

            if ($definition['status'] !== 'novo') {
                TicketStatusHistory::query()->firstOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'new_status' => $definition['status'],
                    ],
                    [
                        'old_status' => 'novo',
                        'changed_by' => $ticket->assigned_to,
                        'notes' => 'Atualizacao de estado durante o tratamento.',
                    ],
                );
            }

            $this->logActivity(
                $ticket,
                'ticket.created',
                $this->users[$definition['created_by']],
                [
                    'reference' => $ticket->reference,
                    'title' => $ticket->title,
                    'status' => 'novo',
                    'priority' => $ticket->priority,
                ],
                'Pedido criado.',
            );

            if ($definition['status'] !== 'novo') {
                $this->logActivity(
                    $ticket,
                    'ticket.status_changed',
                    $definition['assigned_to'] ? $this->users[$definition['assigned_to']] : $this->users[$definition['created_by']],
                    [
                        'status' => $definition['status'],
                    ],
                    'Estado do pedido atualizado.',
                    ['status' => 'novo'],
                );
            }
        }

        $comments = [
            ['ticket' => 'Buraco na via publica', 'author' => 'operacional@juntaos.local', 'body' => 'Vistoria no local concluida. Intervencao agendada para esta semana.'],
            ['ticket' => 'Buraco na via publica', 'author' => 'executivo@juntaos.local', 'body' => 'Prioridade mantida como alta devido ao risco rodoviario.'],
            ['ticket' => 'Pedido de emissao de atestado', 'author' => 'administrativo@juntaos.local', 'body' => 'Documentacao confirmada. Processo segue para analise final.'],
            ['ticket' => 'Manutencao de equipamento no parque infantil', 'author' => 'manutencao@juntaos.local', 'body' => 'Peca de substituicao encomendada ao fornecedor.'],
            ['ticket' => 'Pedido de poda de arvores', 'author' => 'operacional@juntaos.local', 'body' => 'Intervencao colocada em agenda para quinta-feira.'],
        ];

        foreach ($comments as $comment) {
            Comment::query()->firstOrCreate(
                [
                    'commentable_type' => Ticket::class,
                    'commentable_id' => $this->tickets[$comment['ticket']]->id,
                    'body' => $comment['body'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'user_id' => $this->users[$comment['author']]->id,
                    'visibility' => 'internal',
                ],
            );
        }
    }

    private function seedTasks(): void
    {
        $definitions = [
            ['title' => 'Verificar buraco na Rua da Escola', 'status' => 'in_progress', 'priority' => 'high', 'assigned_to' => 'operacional@juntaos.local', 'ticket' => 'Buraco na via publica'],
            ['title' => 'Preparar sala para reuniao de executivo', 'status' => 'pending', 'priority' => 'normal', 'assigned_to' => 'administrativo@juntaos.local', 'ticket' => null],
            ['title' => 'Confirmar reserva do auditorio', 'status' => 'waiting', 'priority' => 'normal', 'assigned_to' => 'administrativo@juntaos.local', 'ticket' => 'Pedido de cedencia de sala'],
            ['title' => 'Atualizar stock de materiais de limpeza', 'status' => 'pending', 'priority' => 'normal', 'assigned_to' => 'armazem@juntaos.local', 'ticket' => null],
            ['title' => 'Responder a pedido de atestado', 'status' => 'in_progress', 'priority' => 'high', 'assigned_to' => 'administrativo@juntaos.local', 'ticket' => 'Pedido de emissao de atestado'],
            ['title' => 'Validar documentacao de apoio social', 'status' => 'waiting', 'priority' => 'normal', 'assigned_to' => 'rh@juntaos.local', 'ticket' => 'Pedido de apoio social'],
            ['title' => 'Contactar empresa de iluminacao', 'status' => 'in_progress', 'priority' => 'high', 'assigned_to' => 'manutencao@juntaos.local', 'ticket' => 'Reparacao de candeeiro'],
            ['title' => 'Limpar sarjeta na Avenida Central', 'status' => 'pending', 'priority' => 'urgent', 'assigned_to' => 'operacional@juntaos.local', 'ticket' => 'Pedido de limpeza de sarjeta'],
            ['title' => 'Agendar intervencao no parque infantil', 'status' => 'in_progress', 'priority' => 'urgent', 'assigned_to' => 'manutencao@juntaos.local', 'ticket' => 'Manutencao de equipamento no parque infantil'],
            ['title' => 'Preparar edital para publicacao', 'status' => 'done', 'priority' => 'normal', 'assigned_to' => 'administrativo@juntaos.local', 'ticket' => null],
            ['title' => 'Recolher monos na Rua do Mercado', 'status' => 'cancelled', 'priority' => 'low', 'assigned_to' => 'operacional@juntaos.local', 'ticket' => 'Solicitacao de recolha de monos'],
            ['title' => 'Rever plano semanal de manutencao', 'status' => 'done', 'priority' => 'normal', 'assigned_to' => 'manutencao@juntaos.local', 'ticket' => null],
        ];

        foreach ($definitions as $definition) {
            $assignee = $this->users[$definition['assigned_to']];

            $task = Task::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'title' => $definition['title'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'ticket_id' => $definition['ticket'] ? $this->tickets[$definition['ticket']]->id : null,
                    'assigned_to' => $assignee->id,
                    'created_by' => $this->users['executivo@juntaos.local']->id,
                    'description' => 'Tarefa operacional de demonstracao para JuntaOS.',
                    'status' => $definition['status'],
                    'priority' => $definition['priority'],
                    'start_date' => now()->toDateString(),
                    'due_date' => now()->addDays(3)->toDateString(),
                    'completed_at' => in_array($definition['status'], ['done'], true) ? now()->subHours(3) : null,
                    'completed_by' => in_array($definition['status'], ['done'], true) ? $assignee->id : null,
                ],
            );

            $this->tasks[$definition['title']] = $task;

            $checklist = TaskChecklist::query()->firstOrCreate(
                [
                    'task_id' => $task->id,
                    'title' => 'Checklist operacional',
                ],
                ['position' => 1],
            );

            $items = [
                ['label' => 'Verificacao no local', 'position' => 1],
                ['label' => 'Material necessario', 'position' => 2],
                ['label' => 'Conclusao', 'position' => 3],
            ];

            foreach ($items as $item) {
                TaskChecklistItem::query()->updateOrCreate(
                    [
                        'task_checklist_id' => $checklist->id,
                        'label' => $item['label'],
                    ],
                    [
                        'position' => $item['position'],
                        'is_completed' => in_array($task->status, ['done'], true),
                        'completed_at' => in_array($task->status, ['done'], true) ? now()->subHours(2) : null,
                        'completed_by' => in_array($task->status, ['done'], true) ? $assignee->id : null,
                    ],
                );
            }

            $this->logActivity(
                $task,
                'task.created',
                $this->users['executivo@juntaos.local'],
                [
                    'title' => $task->title,
                    'status' => $task->status,
                    'priority' => $task->priority,
                ],
                'Tarefa criada.',
            );

            if ($task->status === 'done') {
                $this->logActivity(
                    $task,
                    'task.completed',
                    $assignee,
                    ['status' => 'done'],
                    'Tarefa concluida.',
                    ['status' => 'in_progress'],
                );
            }
        }
    }

    private function seedEvents(): void
    {
        $today = now()->startOfDay();

        $definitions = [
            ['title' => 'Reuniao de Executivo', 'type' => 'meeting', 'status' => 'scheduled', 'start' => $today->copy()->setTime(9, 0), 'end' => $today->copy()->setTime(10, 0), 'location' => 'Sala de Reunioes'],
            ['title' => 'Visita tecnica - Obras na Rua Afonso Lopes', 'type' => 'visit', 'status' => 'scheduled', 'start' => $today->copy()->setTime(11, 0), 'end' => $today->copy()->setTime(11, 45), 'location' => 'Rua Afonso Lopes'],
            ['title' => 'Atendimento descentralizado', 'type' => 'appointment', 'status' => 'scheduled', 'start' => $today->copy()->setTime(14, 30), 'end' => $today->copy()->setTime(16, 0), 'location' => 'Centro Comunitario do Calhariz'],
            ['title' => 'Reuniao com Associacao de Moradores', 'type' => 'meeting', 'status' => 'scheduled', 'start' => $today->copy()->setTime(16, 0), 'end' => $today->copy()->setTime(17, 0), 'location' => 'Sala Polivalente'],
            ['title' => 'Reuniao de Planeamento', 'type' => 'meeting', 'status' => 'scheduled', 'start' => $today->copy()->addDay()->setTime(10, 0), 'end' => $today->copy()->addDay()->setTime(11, 0), 'location' => 'Sala de Reunioes'],
            ['title' => 'Visita tecnica - Parque Infantil do Calhariz', 'type' => 'visit', 'status' => 'scheduled', 'start' => $today->copy()->addDay()->setTime(15, 0), 'end' => $today->copy()->addDay()->setTime(16, 0), 'location' => 'Parque Infantil do Calhariz'],
            ['title' => 'Atendimento ao Publico', 'type' => 'appointment', 'status' => 'scheduled', 'start' => $today->copy()->addDays(2)->setTime(9, 30), 'end' => $today->copy()->addDays(2)->setTime(11, 0), 'location' => 'Edificio da Junta'],
            ['title' => 'Evento Comunitario - Dia da Freguesia', 'type' => 'activity', 'status' => 'scheduled', 'start' => $today->copy()->addDays(2)->setTime(11, 30), 'end' => $today->copy()->addDays(2)->setTime(13, 0), 'location' => 'Centro Comunitario do Calhariz'],
        ];

        foreach ($definitions as $definition) {
            $event = Event::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'title' => $definition['title'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'description' => 'Evento de demonstracao da agenda da Junta.',
                    'event_type' => $definition['type'],
                    'status' => $definition['status'],
                    'start_at' => $definition['start'],
                    'end_at' => $definition['end'],
                    'location_text' => $definition['location'],
                    'created_by' => $this->users['executivo@juntaos.local']->id,
                    'visibility' => 'internal',
                ],
            );

            $this->events[$definition['title']] = $event;
        }

        $participants = [
            ['event' => 'Reuniao de Executivo', 'user' => 'executivo@juntaos.local', 'role' => 'presidencia'],
            ['event' => 'Reuniao de Executivo', 'user' => 'administrativo@juntaos.local', 'role' => 'secretariado'],
            ['event' => 'Visita tecnica - Obras na Rua Afonso Lopes', 'user' => 'operacional@juntaos.local', 'role' => 'tecnico'],
            ['event' => 'Reuniao com Associacao de Moradores', 'contact' => 'associacao', 'role' => 'entidade convidada'],
            ['event' => 'Reuniao de Planeamento', 'user' => 'executivo@juntaos.local', 'role' => 'coordenacao'],
            ['event' => 'Reuniao de Planeamento', 'user' => 'manutencao@juntaos.local', 'role' => 'operacional'],
            ['event' => 'Atendimento ao Publico', 'user' => 'administrativo@juntaos.local', 'role' => 'atendimento'],
            ['event' => 'Evento Comunitario - Dia da Freguesia', 'contact' => 'associacao', 'role' => 'organizacao'],
        ];

        foreach ($participants as $participant) {
            EventParticipant::query()->firstOrCreate(
                [
                    'event_id' => $this->events[$participant['event']]->id,
                    'user_id' => isset($participant['user']) ? $this->users[$participant['user']]->id : null,
                    'contact_id' => isset($participant['contact']) ? $this->contacts[$participant['contact']]->id : null,
                ],
                [
                    'role' => $participant['role'],
                    'attendance_status' => 'confirmed',
                ],
            );
        }
    }

    private function seedSpaceReservations(): void
    {
        $today = now()->startOfDay();

        $definitions = [
            [
                'purpose' => 'Assembleia da Associacao Cultural',
                'space' => 'sala-polivalente',
                'contact' => 'associacao',
                'requested_by' => 'associacao@juntaos.local',
                'status' => 'approved',
                'start' => $today->copy()->setTime(18, 0),
                'end' => $today->copy()->setTime(20, 0),
                'decision_user' => 'executivo@juntaos.local',
            ],
            [
                'purpose' => 'Ensaio grupo coral',
                'space' => 'auditorio-junta',
                'contact' => 'associacao',
                'requested_by' => 'associacao@juntaos.local',
                'status' => 'requested',
                'start' => $today->copy()->addDay()->setTime(19, 0),
                'end' => $today->copy()->addDay()->setTime(21, 0),
            ],
            [
                'purpose' => 'Workshop comunitario de saude',
                'space' => 'centro-calhariz',
                'contact' => 'escola',
                'requested_by' => 'administrativo@juntaos.local',
                'status' => 'approved',
                'start' => $today->copy()->addDay()->setTime(10, 0),
                'end' => $today->copy()->addDay()->setTime(12, 0),
                'decision_user' => 'executivo@juntaos.local',
            ],
            [
                'purpose' => 'Torneio local de futsal',
                'space' => 'campo-jogos',
                'contact' => 'clube',
                'requested_by' => 'administrativo@juntaos.local',
                'status' => 'requested',
                'start' => $today->copy()->addDays(2)->setTime(15, 0),
                'end' => $today->copy()->addDays(2)->setTime(18, 0),
            ],
            [
                'purpose' => 'Reuniao de condominio extraordinaria',
                'space' => 'sala-reunioes',
                'contact' => 'miguel',
                'requested_by' => 'cidadao@juntaos.local',
                'status' => 'rejected',
                'start' => $today->copy()->addDays(3)->setTime(18, 30),
                'end' => $today->copy()->addDays(3)->setTime(20, 30),
                'decision_user' => 'executivo@juntaos.local',
                'reason' => 'Horario em conflito com reuniao institucional.',
            ],
            [
                'purpose' => 'Aula aberta de danca',
                'space' => 'sala-polivalente',
                'contact' => 'associacao',
                'requested_by' => 'associacao@juntaos.local',
                'status' => 'cancelled',
                'start' => $today->copy()->addDays(4)->setTime(17, 0),
                'end' => $today->copy()->addDays(4)->setTime(19, 0),
                'decision_user' => 'associacao@juntaos.local',
                'reason' => 'Cancelado pelo requerente.',
            ],
            [
                'purpose' => 'Atendimento social itinerante',
                'space' => 'centro-calhariz',
                'contact' => 'maria',
                'requested_by' => 'administrativo@juntaos.local',
                'status' => 'approved',
                'start' => $today->copy()->addDays(2)->setTime(9, 0),
                'end' => $today->copy()->addDays(2)->setTime(12, 0),
                'decision_user' => 'executivo@juntaos.local',
            ],
            [
                'purpose' => 'Sessao informativa para fornecedores',
                'space' => 'auditorio-junta',
                'contact' => 'limpezas',
                'requested_by' => 'administrativo@juntaos.local',
                'status' => 'requested',
                'start' => $today->copy()->addDays(5)->setTime(10, 0),
                'end' => $today->copy()->addDays(5)->setTime(12, 0),
            ],
        ];

        foreach ($definitions as $definition) {
            $reservation = SpaceReservation::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'purpose' => $definition['purpose'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'space_id' => $this->spaces[$definition['space']]->id,
                    'requested_by_user_id' => $this->users[$definition['requested_by']]->id,
                    'contact_id' => $this->contacts[$definition['contact']]->id,
                    'status' => $definition['status'],
                    'start_at' => $definition['start'],
                    'end_at' => $definition['end'],
                    'notes' => 'Reserva criada para demonstracao.',
                    'internal_notes' => 'Validar requisitos logisticos e limpeza.',
                    'approved_by' => $definition['status'] === 'approved' ? $this->users[$definition['decision_user']]->id : null,
                    'approved_at' => $definition['status'] === 'approved' ? now()->subHours(8) : null,
                    'rejected_by' => $definition['status'] === 'rejected' ? $this->users[$definition['decision_user']]->id : null,
                    'rejected_at' => $definition['status'] === 'rejected' ? now()->subHours(7) : null,
                    'rejection_reason' => $definition['status'] === 'rejected' ? ($definition['reason'] ?? 'Sem disponibilidade.') : null,
                    'cancelled_by' => $definition['status'] === 'cancelled' ? $this->users[$definition['decision_user']]->id : null,
                    'cancelled_at' => $definition['status'] === 'cancelled' ? now()->subHours(6) : null,
                    'cancellation_reason' => $definition['status'] === 'cancelled' ? ($definition['reason'] ?? 'Cancelamento solicitado.') : null,
                ],
            );

            SpaceReservationApproval::query()->firstOrCreate(
                [
                    'space_reservation_id' => $reservation->id,
                    'action' => 'requested',
                    'new_status' => 'requested',
                ],
                [
                    'decided_by' => $reservation->requested_by_user_id,
                    'notes' => 'Pedido submetido.',
                    'old_status' => null,
                ],
            );

            if (in_array($reservation->status, ['approved', 'rejected', 'cancelled'], true)) {
                SpaceReservationApproval::query()->firstOrCreate(
                    [
                        'space_reservation_id' => $reservation->id,
                        'action' => $reservation->status,
                        'new_status' => $reservation->status,
                    ],
                    [
                        'decided_by' => $definition['status'] === 'cancelled'
                            ? $reservation->cancelled_by
                            : ($definition['status'] === 'rejected' ? $reservation->rejected_by : $reservation->approved_by),
                        'notes' => 'Estado atualizado para '.$reservation->status.'.',
                        'old_status' => 'requested',
                    ],
                );
            }

            if ($reservation->status === 'approved') {
                $event = Event::query()->updateOrCreate(
                    [
                        'organization_id' => $this->organization->id,
                        'title' => 'Reserva aprovada - '.$reservation->purpose,
                    ],
                    [
                        'organization_id' => $this->organization->id,
                        'description' => 'Evento gerado automaticamente a partir de reserva aprovada.',
                        'event_type' => 'reservation',
                        'status' => 'confirmed',
                        'start_at' => $reservation->start_at,
                        'end_at' => $reservation->end_at,
                        'location_text' => $reservation->space->name,
                        'created_by' => $this->users['administrativo@juntaos.local']->id,
                        'related_contact_id' => $reservation->contact_id,
                        'visibility' => 'internal',
                    ],
                );

                if ($reservation->event_id !== $event->id) {
                    $reservation->update(['event_id' => $event->id]);
                }

                if ($reservation->space->has_cleaning_required) {
                    SpaceCleaningRecord::query()->updateOrCreate(
                        [
                            'organization_id' => $this->organization->id,
                            'space_reservation_id' => $reservation->id,
                        ],
                        [
                            'organization_id' => $this->organization->id,
                            'space_id' => $reservation->space_id,
                            'task_id' => $this->tasks['Preparar sala para reuniao de executivo']->id,
                            'status' => 'scheduled',
                            'scheduled_at' => $reservation->end_at,
                            'assigned_to' => $this->users['manutencao@juntaos.local']->id,
                            'notes' => 'Limpeza necessaria apos reserva aprovada.',
                        ],
                    );
                }

                $this->logActivity(
                    $reservation,
                    'space_reservation.approved',
                    $this->users['executivo@juntaos.local'],
                    ['status' => 'approved'],
                    'Reserva aprovada.',
                    ['status' => 'requested'],
                );
            }
        }
    }

    private function seedDocumentsAndMeetingMinutes(): void
    {
        $documentTypes = [
            'Ata',
            'Edital',
            'Regulamento',
            'Formulario',
            'Contrato',
            'Documento interno',
            'Documento de pedido',
            'Documento de reuniao',
            'Documento de associacao',
        ];

        foreach ($documentTypes as $typeName) {
            DocumentType::query()->updateOrCreate(
                [
                    'organization_id' => null,
                    'slug' => Str::slug($typeName),
                ],
                [
                    'name' => $typeName,
                    'description' => 'Tipo documental para demonstracao.',
                    'is_active' => true,
                ],
            );
        }

        $definitions = [
            ['title' => 'Ata da reuniao de executivo', 'type' => 'Ata', 'visibility' => 'restricted', 'related_event' => 'Reuniao de Executivo'],
            ['title' => 'Edital de obras na via publica', 'type' => 'Edital', 'visibility' => 'public', 'related_ticket' => 'Buraco na via publica'],
            ['title' => 'Regulamento de cedencia de espacos', 'type' => 'Regulamento', 'visibility' => 'portal', 'related_space' => 'sala-polivalente'],
            ['title' => 'Formulario de pedido de atestado', 'type' => 'Formulario', 'visibility' => 'portal', 'related_ticket' => 'Pedido de emissao de atestado'],
            ['title' => 'Contrato de manutencao de equipamentos', 'type' => 'Contrato', 'visibility' => 'restricted', 'related_contact' => 'limpezas'],
            ['title' => 'Documento interno de planeamento semanal', 'type' => 'Documento interno', 'visibility' => 'internal', 'related_event' => 'Reuniao de Planeamento'],
            ['title' => 'Relatorio de intervencao no parque infantil', 'type' => 'Documento de reuniao', 'visibility' => 'internal', 'related_ticket' => 'Manutencao de equipamento no parque infantil'],
            ['title' => 'Pedido de apoio da associacao cultural', 'type' => 'Documento de associacao', 'visibility' => 'restricted', 'related_contact' => 'associacao'],
        ];

        foreach ($definitions as $definition) {
            $type = DocumentType::query()
                ->where('slug', Str::slug($definition['type']))
                ->firstOrFail();

            $fileName = Str::slug($definition['title']).'.txt';
            $filePath = 'demo-documents/'.$fileName;
            $content = "Documento demo gerado para apresentacao JuntaOS.\n\n".$definition['title']."\n";
            Storage::disk('local')->put($filePath, $content);

            $document = Document::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'title' => $definition['title'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'document_type_id' => $type->id,
                    'description' => 'Documento demo para navegacao no modulo documental.',
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'original_name' => $fileName,
                    'mime_type' => 'text/plain',
                    'size' => strlen($content),
                    'uploaded_by' => $this->users['administrativo@juntaos.local']->id,
                    'visibility' => $definition['visibility'],
                    'related_type' => isset($definition['related_ticket']) ? Ticket::class : (isset($definition['related_event']) ? Event::class : (isset($definition['related_space']) ? Space::class : (isset($definition['related_contact']) ? Contact::class : null))),
                    'related_id' => isset($definition['related_ticket'])
                        ? $this->tickets[$definition['related_ticket']]->id
                        : (isset($definition['related_event'])
                            ? $this->events[$definition['related_event']]->id
                            : (isset($definition['related_space'])
                                ? $this->spaces[$definition['related_space']]->id
                                : (isset($definition['related_contact']) ? $this->contacts[$definition['related_contact']]->id : null))),
                    'current_version' => 1,
                    'status' => 'active',
                    'is_active' => true,
                ],
            );

            $this->documents[$definition['title']] = $document;

            DocumentVersion::query()->updateOrCreate(
                [
                    'document_id' => $document->id,
                    'version' => 1,
                ],
                [
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'original_name' => $fileName,
                    'mime_type' => 'text/plain',
                    'size' => strlen($content),
                    'uploaded_by' => $this->users['administrativo@juntaos.local']->id,
                    'notes' => 'Versao inicial de demonstracao.',
                ],
            );
        }

        DocumentAccessRule::query()->firstOrCreate(
            [
                'document_id' => $this->documents['Ata da reuniao de executivo']->id,
                'role_name' => 'executivo',
                'permission' => 'view',
            ],
            [
                'created_by' => $this->users['admin@juntaos.local']->id,
            ],
        );

        DocumentAccessRule::query()->firstOrCreate(
            [
                'document_id' => $this->documents['Contrato de manutencao de equipamentos']->id,
                'role_name' => 'rh',
                'permission' => 'view',
            ],
            [
                'created_by' => $this->users['admin@juntaos.local']->id,
            ],
        );

        $minutes = [
            ['title' => 'Ata - Reuniao de Executivo', 'status' => 'draft', 'event' => 'Reuniao de Executivo', 'document' => 'Ata da reuniao de executivo'],
            ['title' => 'Ata - Reuniao com Associacao de Moradores', 'status' => 'approved', 'event' => 'Reuniao com Associacao de Moradores', 'document' => 'Ata da reuniao de executivo'],
            ['title' => 'Ata - Reuniao de Planeamento', 'status' => 'reviewed', 'event' => 'Reuniao de Planeamento', 'document' => 'Ata da reuniao de executivo'],
        ];

        foreach ($minutes as $minute) {
            MeetingMinute::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'title' => $minute['title'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'event_id' => $this->events[$minute['event']]->id,
                    'document_id' => $this->documents[$minute['document']]->id,
                    'summary' => 'Resumo da reuniao para demonstracao.',
                    'status' => $minute['status'],
                    'approved_at' => $minute['status'] === 'approved' ? now()->subDay() : null,
                    'approved_by' => $minute['status'] === 'approved' ? $this->users['executivo@juntaos.local']->id : null,
                    'created_by' => $this->users['administrativo@juntaos.local']->id,
                ],
            );
        }
    }

    private function seedInventory(): void
    {
        $categoryNames = [
            'Material de limpeza',
            'Ferramentas',
            'Equipamentos',
            'Viaturas',
            'Mobiliario',
            'Consumiveis administrativos',
            'Material de eventos',
        ];

        foreach ($categoryNames as $name) {
            $category = InventoryCategory::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'slug' => Str::slug($name),
                ],
                [
                    'organization_id' => $this->organization->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'is_active' => true,
                ],
            );

            $this->inventoryCategories[$name] = $category;
        }

        $locations = [
            ['name' => 'Armazem principal', 'responsible' => 'armazem@juntaos.local'],
            ['name' => 'Edificio da Junta', 'responsible' => 'administrativo@juntaos.local'],
            ['name' => 'Centro Comunitario', 'responsible' => 'operacional@juntaos.local'],
            ['name' => 'Viaturas', 'responsible' => 'manutencao@juntaos.local'],
        ];

        foreach ($locations as $definition) {
            $location = InventoryLocation::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'slug' => Str::slug($definition['name']),
                ],
                [
                    'organization_id' => $this->organization->id,
                    'name' => $definition['name'],
                    'slug' => Str::slug($definition['name']),
                    'description' => 'Local de armazenagem e operacao de inventario.',
                    'address' => 'Endereco interno da Junta Demo',
                    'responsible_user_id' => $this->users[$definition['responsible']]->id,
                    'is_active' => true,
                ],
            );

            $this->inventoryLocations[$definition['name']] = $location;
        }

        $items = [
            ['name' => 'Sacos do lixo', 'category' => 'Material de limpeza', 'location' => 'Armazem principal', 'type' => 'consumable', 'unit' => 'pack', 'current' => 120, 'minimum' => 50, 'loanable' => false],
            ['name' => 'Detergente multiusos', 'category' => 'Material de limpeza', 'location' => 'Armazem principal', 'type' => 'consumable', 'unit' => 'liter', 'current' => 18, 'minimum' => 20, 'loanable' => false],
            ['name' => 'Luvas descartaveis', 'category' => 'Material de limpeza', 'location' => 'Armazem principal', 'type' => 'consumable', 'unit' => 'box', 'current' => 6, 'minimum' => 30, 'loanable' => false],
            ['name' => 'Berbequim', 'category' => 'Ferramentas', 'location' => 'Edificio da Junta', 'type' => 'tool', 'unit' => 'unit', 'current' => 2, 'minimum' => 1, 'loanable' => true],
            ['name' => 'Mesa dobravel', 'category' => 'Mobiliario', 'location' => 'Centro Comunitario', 'type' => 'furniture', 'unit' => 'unit', 'current' => 12, 'minimum' => 4, 'loanable' => true],
            ['name' => 'Cadeiras empilhaveis', 'category' => 'Mobiliario', 'location' => 'Centro Comunitario', 'type' => 'furniture', 'unit' => 'unit', 'current' => 40, 'minimum' => 20, 'loanable' => true],
            ['name' => 'Extensao eletrica', 'category' => 'Equipamentos', 'location' => 'Armazem principal', 'type' => 'equipment', 'unit' => 'unit', 'current' => 3, 'minimum' => 5, 'loanable' => true],
            ['name' => 'Projetor', 'category' => 'Equipamentos', 'location' => 'Edificio da Junta', 'type' => 'equipment', 'unit' => 'unit', 'current' => 1, 'minimum' => 1, 'loanable' => true],
        ];

        foreach ($items as $item) {
            $inventoryItem = InventoryItem::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'slug' => Str::slug($item['name']),
                ],
                [
                    'organization_id' => $this->organization->id,
                    'inventory_category_id' => $this->inventoryCategories[$item['category']]->id,
                    'inventory_location_id' => $this->inventoryLocations[$item['location']]->id,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'description' => 'Item de inventario para demonstracao.',
                    'sku' => 'DEMO-'.Str::upper(Str::substr(Str::slug($item['name']), 0, 12)),
                    'item_type' => $item['type'],
                    'unit' => $item['unit'],
                    'current_stock' => $item['current'],
                    'minimum_stock' => $item['minimum'],
                    'status' => 'active',
                    'is_stock_tracked' => true,
                    'is_loanable' => $item['loanable'],
                    'is_active' => true,
                ],
            );

            $this->inventoryItems[$item['name']] = $inventoryItem;
        }

        $movements = [
            ['item' => 'Sacos do lixo', 'type' => 'entry', 'quantity' => 50, 'from' => null, 'to' => 'Armazem principal', 'notes' => 'Entrada de reposicao mensal.'],
            ['item' => 'Detergente multiusos', 'type' => 'consumption', 'quantity' => 8, 'from' => 'Armazem principal', 'to' => null, 'notes' => 'Consumo para limpeza de edificios.'],
            ['item' => 'Luvas descartaveis', 'type' => 'consumption', 'quantity' => 12, 'from' => 'Armazem principal', 'to' => null, 'notes' => 'Consumo em tarefas de manutencao e higiene.'],
            ['item' => 'Berbequim', 'type' => 'loan', 'quantity' => 1, 'from' => 'Edificio da Junta', 'to' => null, 'notes' => 'Emprestimo para intervencao tecnica.'],
            ['item' => 'Mesa dobravel', 'type' => 'loan', 'quantity' => 4, 'from' => 'Centro Comunitario', 'to' => null, 'notes' => 'Cedencia para evento comunitario.'],
            ['item' => 'Cadeiras empilhaveis', 'type' => 'return', 'quantity' => 10, 'from' => null, 'to' => 'Centro Comunitario', 'notes' => 'Devolucao apos evento local.'],
            ['item' => 'Extensao eletrica', 'type' => 'breakage', 'quantity' => 1, 'from' => 'Armazem principal', 'to' => null, 'notes' => 'Equipamento avariado em servico.'],
            ['item' => 'Projetor', 'type' => 'loan', 'quantity' => 1, 'from' => 'Edificio da Junta', 'to' => null, 'notes' => 'Emprestimo para apresentacao institucional.'],
        ];

        foreach ($movements as $movement) {
            $model = InventoryMovement::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'inventory_item_id' => $this->inventoryItems[$movement['item']]->id,
                    'movement_type' => $movement['type'],
                    'notes' => $movement['notes'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'inventory_item_id' => $this->inventoryItems[$movement['item']]->id,
                    'movement_type' => $movement['type'],
                    'quantity' => $movement['quantity'],
                    'from_location_id' => $movement['from'] ? $this->inventoryLocations[$movement['from']]->id : null,
                    'to_location_id' => $movement['to'] ? $this->inventoryLocations[$movement['to']]->id : null,
                    'related_ticket_id' => $this->tickets['Buraco na via publica']->id,
                    'requested_by' => $this->users['armazem@juntaos.local']->id,
                    'handled_by' => $this->users['armazem@juntaos.local']->id,
                    'occurred_at' => now()->subDays(2),
                ],
            );

            $this->logActivity(
                $model,
                'inventory.movement.created',
                $this->users['armazem@juntaos.local'],
                [
                    'movement_type' => $model->movement_type,
                    'quantity' => $model->quantity,
                ],
                'Movimento de inventario registado.',
            );
        }

        $loans = [
            ['item' => 'Berbequim', 'status' => 'active', 'quantity' => 1, 'borrower_user' => 'manutencao@juntaos.local', 'expected_days' => 2],
            ['item' => 'Mesa dobravel', 'status' => 'active', 'quantity' => 2, 'borrower_contact' => 'associacao', 'expected_days' => 4],
            ['item' => 'Cadeiras empilhaveis', 'status' => 'active', 'quantity' => 8, 'borrower_contact' => 'associacao', 'expected_days' => 3],
            ['item' => 'Projetor', 'status' => 'overdue', 'quantity' => 1, 'borrower_user' => 'executivo@juntaos.local', 'expected_days' => -1],
            ['item' => 'Extensao eletrica', 'status' => 'returned', 'quantity' => 1, 'borrower_user' => 'operacional@juntaos.local', 'expected_days' => -3],
        ];

        foreach ($loans as $loan) {
            InventoryLoan::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'inventory_item_id' => $this->inventoryItems[$loan['item']]->id,
                    'status' => $loan['status'],
                    'quantity' => $loan['quantity'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'inventory_item_id' => $this->inventoryItems[$loan['item']]->id,
                    'borrower_user_id' => isset($loan['borrower_user']) ? $this->users[$loan['borrower_user']]->id : null,
                    'borrower_contact_id' => isset($loan['borrower_contact']) ? $this->contacts[$loan['borrower_contact']]->id : null,
                    'quantity' => $loan['quantity'],
                    'loaned_at' => now()->subDays(5),
                    'expected_return_at' => now()->addDays($loan['expected_days']),
                    'returned_at' => $loan['status'] === 'returned' ? now()->subDay() : null,
                    'loaned_by' => $this->users['armazem@juntaos.local']->id,
                    'returned_to' => $loan['status'] === 'returned' ? $this->users['armazem@juntaos.local']->id : null,
                    'related_task_id' => $this->tasks['Agendar intervencao no parque infantil']->id,
                    'notes' => 'Emprestimo para operacao de servico.',
                    'return_notes' => $loan['status'] === 'returned' ? 'Material devolvido em boas condicoes.' : null,
                ],
            );
        }

        InventoryRestockRequest::query()->updateOrCreate(
            [
                'organization_id' => $this->organization->id,
                'inventory_item_id' => $this->inventoryItems['Detergente multiusos']->id,
                'status' => 'requested',
            ],
            [
                'requested_by' => $this->users['armazem@juntaos.local']->id,
                'quantity_requested' => 30,
                'reason' => 'Stock abaixo do minimo.',
                'notes' => 'Reposicao urgente para operacoes de limpeza.',
            ],
        );

        InventoryRestockRequest::query()->updateOrCreate(
            [
                'organization_id' => $this->organization->id,
                'inventory_item_id' => $this->inventoryItems['Luvas descartaveis']->id,
                'status' => 'approved',
            ],
            [
                'requested_by' => $this->users['armazem@juntaos.local']->id,
                'approved_by' => $this->users['executivo@juntaos.local']->id,
                'quantity_requested' => 50,
                'quantity_approved' => 40,
                'reason' => 'Necessidade operacional recorrente.',
                'approved_at' => now()->subDay(),
                'notes' => 'Aprovado parcialmente.',
            ],
        );

        InventoryRestockRequest::query()->updateOrCreate(
            [
                'organization_id' => $this->organization->id,
                'inventory_item_id' => $this->inventoryItems['Extensao eletrica']->id,
                'status' => 'requested',
            ],
            [
                'requested_by' => $this->users['manutencao@juntaos.local']->id,
                'quantity_requested' => 4,
                'reason' => 'Stock critico para intervencoes.',
                'notes' => 'Necessario para equipa externa.',
            ],
        );

        InventoryBreakage::query()->updateOrCreate(
            [
                'organization_id' => $this->organization->id,
                'inventory_item_id' => $this->inventoryItems['Cadeiras empilhaveis']->id,
                'description' => 'Uma cadeira danificada apos utilizacao em evento.',
            ],
            [
                'reported_by' => $this->users['operacional@juntaos.local']->id,
                'quantity' => 1,
                'breakage_type' => 'damaged',
                'status' => 'reported',
                'related_task_id' => $this->tasks['Recolher monos na Rua do Mercado']->id,
            ],
        );

        InventoryBreakage::query()->updateOrCreate(
            [
                'organization_id' => $this->organization->id,
                'inventory_item_id' => $this->inventoryItems['Extensao eletrica']->id,
                'description' => 'Uma extensao eletrica avariada durante manutencao.',
            ],
            [
                'reported_by' => $this->users['manutencao@juntaos.local']->id,
                'quantity' => 1,
                'breakage_type' => 'damaged',
                'status' => 'under_review',
                'related_task_id' => $this->tasks['Agendar intervencao no parque infantil']->id,
            ],
        );
    }

    private function seedHr(): void
    {
        $departmentDefinitions = [
            ['name' => 'Executivo', 'slug' => 'executivo', 'manager' => 'executivo@juntaos.local'],
            ['name' => 'Secretaria', 'slug' => 'secretaria', 'manager' => 'administrativo@juntaos.local'],
            ['name' => 'Manutencao', 'slug' => 'manutencao', 'manager' => 'manutencao@juntaos.local'],
            ['name' => 'Limpeza Urbana', 'slug' => 'limpeza-urbana', 'manager' => 'operacional@juntaos.local'],
            ['name' => 'Espacos e Equipamentos', 'slug' => 'espacos-equipamentos', 'manager' => 'operacional@juntaos.local'],
            ['name' => 'Armazem', 'slug' => 'armazem', 'manager' => 'armazem@juntaos.local'],
        ];

        foreach ($departmentDefinitions as $definition) {
            $department = Department::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'slug' => $definition['slug'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'name' => $definition['name'],
                    'description' => 'Departamento funcional da Junta.',
                    'manager_user_id' => $this->users[$definition['manager']]->id,
                    'is_active' => true,
                ],
            );

            $this->departments[$definition['slug']] = $department;
        }

        $employeeDefinitions = [
            ['email' => 'executivo@juntaos.local', 'department' => 'executivo', 'number' => 'EMP-001', 'role' => 'Presidente Executivo'],
            ['email' => 'administrativo@juntaos.local', 'department' => 'secretaria', 'number' => 'EMP-002', 'role' => 'Administrativa'],
            ['email' => 'operacional@juntaos.local', 'department' => 'limpeza-urbana', 'number' => 'EMP-003', 'role' => 'Tecnico Operacional'],
            ['email' => 'manutencao@juntaos.local', 'department' => 'manutencao', 'number' => 'EMP-004', 'role' => 'Tecnico de Manutencao'],
            ['email' => 'armazem@juntaos.local', 'department' => 'armazem', 'number' => 'EMP-005', 'role' => 'Responsavel de Armazem'],
            ['email' => 'rh@juntaos.local', 'department' => 'secretaria', 'number' => 'EMP-006', 'role' => 'Recursos Humanos'],
        ];

        foreach ($employeeDefinitions as $definition) {
            $user = $this->users[$definition['email']];

            $employee = Employee::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'user_id' => $user->id,
                ],
                [
                    'organization_id' => $this->organization->id,
                    'department_id' => $this->departments[$definition['department']]->id,
                    'employee_number' => $definition['number'],
                    'role_title' => $definition['role'],
                    'employment_type' => 'permanent',
                    'start_date' => now()->subYears(2)->toDateString(),
                    'phone' => '91'.substr((string) $user->id, -1).'00000'.substr((string) $user->id, -1),
                    'emergency_contact_name' => 'Contacto de Emergencia Demo',
                    'emergency_contact_phone' => '210000099',
                    'notes' => 'Colaborador de demonstracao.',
                    'is_active' => true,
                ],
            );

            $this->employees[$definition['email']] = $employee;
        }

        $teamDefinitions = [
            ['name' => 'Equipa de Manutencao', 'slug' => 'equipa-de-manutencao', 'department' => 'manutencao', 'leader' => 'manutencao@juntaos.local'],
            ['name' => 'Equipa de Limpeza', 'slug' => 'equipa-de-limpeza', 'department' => 'limpeza-urbana', 'leader' => 'operacional@juntaos.local'],
            ['name' => 'Atendimento', 'slug' => 'atendimento', 'department' => 'secretaria', 'leader' => 'administrativo@juntaos.local'],
            ['name' => 'Gestao de Espacos', 'slug' => 'gestao-de-espacos', 'department' => 'espacos-equipamentos', 'leader' => 'armazem@juntaos.local'],
        ];

        foreach ($teamDefinitions as $definition) {
            $team = Team::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'slug' => $definition['slug'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'department_id' => $this->departments[$definition['department']]->id,
                    'name' => $definition['name'],
                    'description' => 'Equipa operacional para demonstracao.',
                    'leader_user_id' => $this->users[$definition['leader']]->id,
                    'is_active' => true,
                ],
            );

            $this->teams[$definition['slug']] = $team;
        }

        $memberships = [
            ['team' => 'equipa-de-manutencao', 'employee' => 'manutencao@juntaos.local', 'role' => 'tecnico'],
            ['team' => 'equipa-de-manutencao', 'employee' => 'operacional@juntaos.local', 'role' => 'apoio tecnico'],
            ['team' => 'equipa-de-limpeza', 'employee' => 'operacional@juntaos.local', 'role' => 'coordenador'],
            ['team' => 'atendimento', 'employee' => 'administrativo@juntaos.local', 'role' => 'atendimento geral'],
            ['team' => 'gestao-de-espacos', 'employee' => 'armazem@juntaos.local', 'role' => 'gestao de espacos e armazem'],
        ];

        foreach ($memberships as $membership) {
            TeamMember::query()->updateOrCreate(
                [
                    'team_id' => $this->teams[$membership['team']]->id,
                    'employee_id' => $this->employees[$membership['employee']]->id,
                ],
                [
                    'role' => $membership['role'],
                    'joined_at' => now()->subYear()->toDateString(),
                    'is_active' => true,
                ],
            );
        }

        $todayAttendance = [
            'administrativo@juntaos.local' => 'present',
            'operacional@juntaos.local' => 'present',
            'manutencao@juntaos.local' => 'present',
            'armazem@juntaos.local' => 'present',
            'rh@juntaos.local' => 'vacation',
            'executivo@juntaos.local' => 'present',
        ];

        foreach ($todayAttendance as $email => $status) {
            AttendanceRecord::query()->updateOrCreate(
                [
                    'employee_id' => $this->employees[$email]->id,
                    'date' => now()->toDateString(),
                ],
                [
                    'organization_id' => $this->organization->id,
                    'status' => $status,
                    'check_in' => $status === 'present' ? '09:00:00' : null,
                    'check_out' => $status === 'present' ? '17:30:00' : null,
                    'worked_minutes' => $status === 'present' ? 510 : null,
                    'source' => 'manual',
                    'notes' => 'Registo de demonstracao.',
                    'validated_by' => $this->users['rh@juntaos.local']->id,
                    'validated_at' => now(),
                    'created_by' => $this->users['rh@juntaos.local']->id,
                ],
            );
        }

        for ($daysBack = 1; $daysBack <= 7; $daysBack++) {
            foreach (['administrativo@juntaos.local', 'operacional@juntaos.local', 'manutencao@juntaos.local', 'armazem@juntaos.local'] as $email) {
                AttendanceRecord::query()->updateOrCreate(
                    [
                        'employee_id' => $this->employees[$email]->id,
                        'date' => now()->subDays($daysBack)->toDateString(),
                    ],
                    [
                        'organization_id' => $this->organization->id,
                        'status' => $daysBack === 3 ? 'training' : 'present',
                        'check_in' => '09:00:00',
                        'check_out' => '17:00:00',
                        'worked_minutes' => 480,
                        'source' => 'manual',
                        'notes' => 'Historico semanal para relatorios.',
                        'validated_by' => $this->users['rh@juntaos.local']->id,
                        'validated_at' => now()->subDays($daysBack),
                        'created_by' => $this->users['rh@juntaos.local']->id,
                    ],
                );
            }
        }

        $absenceTypeSlugs = ['ferias', 'baixa-medica', 'falta-justificada', 'falta-injustificada', 'apoio-familia', 'formacao'];
        foreach ($absenceTypeSlugs as $slug) {
            $this->assertAbsenceTypeExists($slug);
        }

        $leaveDefinitions = [
            [
                'employee' => 'rh@juntaos.local',
                'absence_slug' => 'ferias',
                'leave_type' => 'vacation',
                'start' => now()->toDateString(),
                'end' => now()->addDay()->toDateString(),
                'status' => 'approved',
                'approved_by' => 'executivo@juntaos.local',
                'approved_at' => now()->subDays(2),
                'reason' => 'Ferias planeadas.',
            ],
            [
                'employee' => 'manutencao@juntaos.local',
                'absence_slug' => 'ferias',
                'leave_type' => 'vacation',
                'start' => now()->addWeek()->toDateString(),
                'end' => now()->addWeek()->addDays(2)->toDateString(),
                'status' => 'requested',
                'reason' => 'Pedido de descanso programado.',
            ],
            [
                'employee' => 'operacional@juntaos.local',
                'absence_slug' => 'falta-justificada',
                'leave_type' => 'justified_absence',
                'start' => now()->subDays(5)->toDateString(),
                'end' => now()->subDays(5)->toDateString(),
                'status' => 'rejected',
                'rejected_by' => 'rh@juntaos.local',
                'rejection_reason' => 'Documentacao insuficiente.',
                'reason' => 'Assunto pessoal.',
            ],
            [
                'employee' => 'administrativo@juntaos.local',
                'absence_slug' => 'formacao',
                'leave_type' => 'training',
                'start' => now()->subDays(3)->toDateString(),
                'end' => now()->subDays(2)->toDateString(),
                'status' => 'completed',
                'approved_by' => 'rh@juntaos.local',
                'approved_at' => now()->subDays(4),
                'reason' => 'Formacao em atendimento digital.',
            ],
        ];

        foreach ($leaveDefinitions as $definition) {
            $absenceType = AbsenceType::query()
                ->where('organization_id', $this->organization->id)
                ->where('slug', $definition['absence_slug'])
                ->first();

            $leave = LeaveRequest::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'employee_id' => $this->employees[$definition['employee']]->id,
                    'leave_type' => $definition['leave_type'],
                    'start_date' => $definition['start'],
                    'end_date' => $definition['end'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'employee_id' => $this->employees[$definition['employee']]->id,
                    'absence_type_id' => $absenceType?->id,
                    'total_days' => 1,
                    'status' => $definition['status'],
                    'reason' => $definition['reason'],
                    'approved_by' => isset($definition['approved_by']) ? $this->users[$definition['approved_by']]->id : null,
                    'rejected_by' => isset($definition['rejected_by']) ? $this->users[$definition['rejected_by']]->id : null,
                    'created_by' => $this->users['rh@juntaos.local']->id,
                    'rejection_reason' => $definition['rejection_reason'] ?? null,
                ],
            );

            $leave->forceFill([
                'approved_at' => $definition['approved_at'] ?? null,
                'rejected_at' => isset($definition['rejected_by']) ? now()->subDays(4) : null,
            ])->save();

            if ($leave->status === 'approved') {
                $this->logActivity(
                    $leave,
                    'leave_request.approved',
                    $this->users[$definition['approved_by']],
                    ['status' => 'approved'],
                    'Pedido de ausencia aprovado.',
                    ['status' => 'requested'],
                );
            }
        }
    }

    private function seedPlanning(): void
    {
        $definitions = [
            [
                'title' => 'Limpeza semanal de espacos publicos',
                'slug' => 'demo-limpeza-semanal',
                'type' => 'cleaning',
                'status' => 'in_progress',
                'visibility' => 'internal',
                'team' => 'equipa-de-limpeza',
                'progress' => 45,
                'related_space' => 'sala-polivalente',
            ],
            [
                'title' => 'Manutencao preventiva de equipamentos',
                'slug' => 'demo-manutencao-preventiva',
                'type' => 'maintenance',
                'status' => 'approved',
                'visibility' => 'internal',
                'team' => 'equipa-de-manutencao',
                'progress' => 20,
                'related_space' => 'centro-calhariz',
            ],
            [
                'title' => 'Campanha de sensibilizacao ambiental',
                'slug' => 'demo-campanha-ambiental',
                'type' => 'campaign',
                'status' => 'scheduled',
                'visibility' => 'public',
                'team' => 'atendimento',
                'progress' => 10,
                'related_space' => 'campo-jogos',
            ],
            [
                'title' => 'Evento publico da freguesia',
                'slug' => 'demo-evento-publico',
                'type' => 'public_event',
                'status' => 'scheduled',
                'visibility' => 'public',
                'team' => 'gestao-de-espacos',
                'progress' => 30,
                'related_space' => 'centro-calhariz',
            ],
            [
                'title' => 'Revisao de documentacao interna',
                'slug' => 'demo-revisao-documentacao',
                'type' => 'administrative',
                'status' => 'pending_approval',
                'visibility' => 'internal',
                'team' => 'atendimento',
                'progress' => 0,
                'related_space' => 'sala-reunioes',
            ],
        ];

        foreach ($definitions as $definition) {
            $plan = OperationalPlan::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'slug' => $definition['slug'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'title' => $definition['title'],
                    'slug' => $definition['slug'],
                    'description' => 'Plano operacional de demonstracao.',
                    'plan_type' => $definition['type'],
                    'status' => $definition['status'],
                    'visibility' => $definition['visibility'],
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addWeeks(3)->toDateString(),
                    'owner_user_id' => $this->users['executivo@juntaos.local']->id,
                    'department_id' => $this->teams[$definition['team']]->department_id,
                    'team_id' => $this->teams[$definition['team']]->id,
                    'related_ticket_id' => $this->tickets['Buraco na via publica']->id,
                    'related_space_id' => $this->spaces[$definition['related_space']]->id,
                    'budget_estimate' => 5000,
                    'progress_percent' => $definition['progress'],
                    'approved_by' => in_array($definition['status'], ['approved', 'in_progress', 'scheduled'], true) ? $this->users['executivo@juntaos.local']->id : null,
                    'approved_at' => in_array($definition['status'], ['approved', 'in_progress', 'scheduled'], true) ? now()->subDays(2) : null,
                    'created_by' => $this->users['executivo@juntaos.local']->id,
                ],
            );

            $this->plans[$definition['slug']] = $plan;

            $this->logActivity(
                $plan,
                'operational_plan.created',
                $this->users['executivo@juntaos.local'],
                [
                    'title' => $plan->title,
                    'status' => $plan->status,
                ],
                'Plano operacional criado.',
            );

            if (in_array($plan->status, ['approved', 'in_progress', 'scheduled'], true)) {
                $this->logActivity(
                    $plan,
                    'operational_plan.approved',
                    $this->users['executivo@juntaos.local'],
                    ['status' => $plan->status],
                    'Plano operacional aprovado.',
                    ['status' => 'pending_approval'],
                );
            }
        }

        $planTasks = [
            ['plan' => 'demo-limpeza-semanal', 'task' => 'Limpar sarjeta na Avenida Central', 'position' => 1, 'weight' => 40],
            ['plan' => 'demo-limpeza-semanal', 'task' => 'Recolher monos na Rua do Mercado', 'position' => 2, 'weight' => 60],
            ['plan' => 'demo-manutencao-preventiva', 'task' => 'Agendar intervencao no parque infantil', 'position' => 1, 'weight' => 50],
            ['plan' => 'demo-manutencao-preventiva', 'task' => 'Rever plano semanal de manutencao', 'position' => 2, 'weight' => 50],
            ['plan' => 'demo-revisao-documentacao', 'task' => 'Preparar edital para publicacao', 'position' => 1, 'weight' => 100],
        ];

        foreach ($planTasks as $planTask) {
            OperationalPlanTask::query()->updateOrCreate(
                [
                    'operational_plan_id' => $this->plans[$planTask['plan']]->id,
                    'task_id' => $this->tasks[$planTask['task']]->id,
                ],
                [
                    'position' => $planTask['position'],
                    'is_milestone' => false,
                    'weight' => $planTask['weight'],
                ],
            );
        }

        $participants = [
            ['plan' => 'demo-limpeza-semanal', 'user' => 'operacional@juntaos.local', 'employee' => 'operacional@juntaos.local', 'team' => 'equipa-de-limpeza', 'role' => 'executor'],
            ['plan' => 'demo-manutencao-preventiva', 'user' => 'manutencao@juntaos.local', 'employee' => 'manutencao@juntaos.local', 'team' => 'equipa-de-manutencao', 'role' => 'coordenador tecnico'],
            ['plan' => 'demo-campanha-ambiental', 'user' => 'administrativo@juntaos.local', 'employee' => 'administrativo@juntaos.local', 'team' => 'atendimento', 'role' => 'comunicacao'],
            ['plan' => 'demo-evento-publico', 'user' => 'armazem@juntaos.local', 'employee' => 'armazem@juntaos.local', 'team' => 'gestao-de-espacos', 'role' => 'logistica'],
        ];

        foreach ($participants as $participant) {
            OperationalPlanParticipant::query()->firstOrCreate(
                [
                    'operational_plan_id' => $this->plans[$participant['plan']]->id,
                    'user_id' => $this->users[$participant['user']]->id,
                    'employee_id' => $this->employees[$participant['employee']]->id,
                    'team_id' => $this->teams[$participant['team']]->id,
                ],
                [
                    'role' => $participant['role'],
                ],
            );
        }

        $resources = [
            ['plan' => 'demo-limpeza-semanal', 'item' => 'Detergente multiusos', 'space' => null, 'quantity' => 5],
            ['plan' => 'demo-limpeza-semanal', 'item' => 'Luvas descartaveis', 'space' => null, 'quantity' => 10],
            ['plan' => 'demo-manutencao-preventiva', 'item' => 'Berbequim', 'space' => null, 'quantity' => 1],
            ['plan' => 'demo-evento-publico', 'item' => 'Cadeiras empilhaveis', 'space' => 'centro-calhariz', 'quantity' => 20],
            ['plan' => 'demo-evento-publico', 'item' => 'Projetor', 'space' => 'centro-calhariz', 'quantity' => 1],
        ];

        foreach ($resources as $resource) {
            OperationalPlanResource::query()->updateOrCreate(
                [
                    'operational_plan_id' => $this->plans[$resource['plan']]->id,
                    'inventory_item_id' => $this->inventoryItems[$resource['item']]->id,
                    'space_id' => $resource['space'] ? $this->spaces[$resource['space']]->id : null,
                ],
                [
                    'quantity' => $resource['quantity'],
                    'notes' => 'Recurso alocado ao plano.',
                ],
            );
        }

        $recurringDefinitions = [
            [
                'title' => 'Limpeza semanal da Sala Polivalente',
                'type' => 'cleaning',
                'frequency' => 'weekly',
                'weekdays' => ['monday'],
                'team' => 'equipa-de-limpeza',
                'space' => 'sala-polivalente',
                'task_title' => 'Executar limpeza da Sala Polivalente',
                'task_priority' => 'normal',
            ],
            [
                'title' => 'Inspecao mensal ao Parque Infantil',
                'type' => 'inspection',
                'frequency' => 'monthly',
                'day_of_month' => 10,
                'team' => 'equipa-de-manutencao',
                'space' => 'campo-jogos',
                'task_title' => 'Inspecionar parque infantil',
                'task_priority' => 'high',
            ],
            [
                'title' => 'Verificacao semanal de stock critico',
                'type' => 'task',
                'frequency' => 'weekly',
                'weekdays' => ['friday'],
                'team' => 'gestao-de-espacos',
                'space' => 'auditorio-junta',
                'task_title' => 'Verificar stock abaixo do minimo',
                'task_priority' => 'high',
            ],
        ];

        $recurringByTitle = [];

        foreach ($recurringDefinitions as $definition) {
            $recurring = RecurringOperation::query()->updateOrCreate(
                [
                    'organization_id' => $this->organization->id,
                    'title' => $definition['title'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'description' => 'Operacao recorrente de demonstracao.',
                    'operation_type' => $definition['type'],
                    'status' => 'active',
                    'frequency' => $definition['frequency'],
                    'interval' => 1,
                    'weekdays' => $definition['weekdays'] ?? null,
                    'day_of_month' => $definition['day_of_month'] ?? null,
                    'start_date' => now()->toDateString(),
                    'next_run_at' => now()->addDays(2),
                    'owner_user_id' => $this->users['executivo@juntaos.local']->id,
                    'department_id' => $this->teams[$definition['team']]->department_id,
                    'team_id' => $this->teams[$definition['team']]->id,
                    'related_space_id' => $this->spaces[$definition['space']]->id,
                    'task_template' => [
                        'title' => $definition['task_title'],
                        'priority' => $definition['task_priority'],
                    ],
                    'created_by' => $this->users['executivo@juntaos.local']->id,
                ],
            );

            $recurringByTitle[$definition['title']] = $recurring;
        }

        $runDefinitions = [
            [
                'operation' => 'Limpeza semanal da Sala Polivalente',
                'status' => 'executed',
                'run_at' => now()->subDays(7)->setTime(9, 0),
                'executed_at' => now()->subDays(7)->setTime(9, 30),
                'executed_by' => 'operacional@juntaos.local',
                'task' => 'Limpar sarjeta na Avenida Central',
            ],
            [
                'operation' => 'Inspecao mensal ao Parque Infantil',
                'status' => 'pending',
                'run_at' => now()->addDays(3)->setTime(10, 0),
            ],
            [
                'operation' => 'Verificacao semanal de stock critico',
                'status' => 'failed',
                'run_at' => now()->subDay()->setTime(8, 0),
                'executed_at' => now()->subDay()->setTime(8, 5),
                'executed_by' => 'armazem@juntaos.local',
                'error' => 'Falha ao validar disponibilidade do item Extensao eletrica.',
            ],
        ];

        foreach ($runDefinitions as $definition) {
            RecurringOperationRun::query()->updateOrCreate(
                [
                    'recurring_operation_id' => $recurringByTitle[$definition['operation']]->id,
                    'run_at' => $definition['run_at'],
                ],
                [
                    'status' => $definition['status'],
                    'generated_task_id' => isset($definition['task']) ? $this->tasks[$definition['task']]->id : null,
                    'generated_event_id' => null,
                    'error_message' => $definition['error'] ?? null,
                    'executed_by' => isset($definition['executed_by']) ? $this->users[$definition['executed_by']]->id : null,
                    'executed_at' => $definition['executed_at'] ?? null,
                ],
            );
        }
    }

    private function seedAttachments(): void
    {
        $attachments = [
            [
                'subject_type' => Ticket::class,
                'subject_id' => $this->tickets['Buraco na via publica']->id,
                'file_name' => 'foto-buraco-via-publica.txt',
                'content' => 'Anexo demo: fotografia descritiva de ocorrencia em via publica.',
                'uploaded_by' => 'operacional@juntaos.local',
            ],
            [
                'subject_type' => Event::class,
                'subject_id' => $this->events['Reuniao de Planeamento']->id,
                'file_name' => 'ordem-trabalhos-reuniao-planeamento.txt',
                'content' => 'Anexo demo: ordem de trabalhos para reuniao de planeamento.',
                'uploaded_by' => 'administrativo@juntaos.local',
            ],
            [
                'subject_type' => Document::class,
                'subject_id' => $this->documents['Contrato de manutencao de equipamentos']->id,
                'file_name' => 'anexo-contrato-manutencao.txt',
                'content' => 'Anexo demo: clausulas adicionais do contrato de manutencao.',
                'uploaded_by' => 'executivo@juntaos.local',
            ],
        ];

        foreach ($attachments as $definition) {
            $path = 'demo-documents/attachments/'.$definition['file_name'];
            Storage::disk('local')->put($path, $definition['content']);

            Attachment::query()->updateOrCreate(
                [
                    'attachable_type' => $definition['subject_type'],
                    'attachable_id' => $definition['subject_id'],
                    'file_name' => $definition['file_name'],
                ],
                [
                    'organization_id' => $this->organization->id,
                    'uploaded_by' => $this->users[$definition['uploaded_by']]->id,
                    'file_path' => $path,
                    'mime_type' => 'text/plain',
                    'size' => strlen($definition['content']),
                    'visibility' => 'internal',
                ],
            );
        }
    }

    private function assertAbsenceTypeExists(string $slug): void
    {
        AbsenceType::query()->firstOrCreate(
            [
                'organization_id' => $this->organization->id,
                'slug' => $slug,
            ],
            [
                'name' => Str::title(str_replace('-', ' ', $slug)),
                'description' => 'Tipo de ausencia para demonstracao.',
                'requires_approval' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
        );
    }

    private function logActivity(
        Model $subject,
        string $action,
        ?User $user,
        ?array $newValues,
        string $description,
        ?array $oldValues = null,
    ): void {
        $exists = ActivityLog::query()
            ->where('subject_type', $subject::class)
            ->where('subject_id', $subject->getKey())
            ->where('action', $action)
            ->where('description', $description)
            ->exists();

        if ($exists) {
            return;
        }

        $this->activityLogger->log(
            subject: $subject,
            action: $action,
            user: $user,
            organization: $this->organization,
            oldValues: $oldValues,
            newValues: $newValues,
            description: $description,
        );
    }
}
