import SpaceReservationForm from '@/Components/SpaceReservationForm';
import AdminLayout from '@/Layouts/AdminLayout';

type Props = { spaces: { id: number; name: string }[]; contacts: { id: number; name: string }[]; };

export default function AdminSpaceReservationsCreate({ spaces, contacts }: Props) {
    return (
        <AdminLayout title="Criar Reserva" subtitle="Nova reserva interna">
            <SpaceReservationForm spaces={spaces} contacts={contacts} submitRoute={route('admin.space-reservations.store')} />
        </AdminLayout>
    );
}
