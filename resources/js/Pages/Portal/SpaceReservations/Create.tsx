import SpaceReservationForm from '@/Components/SpaceReservationForm';
import PortalLayout from '@/Layouts/PortalLayout';

type Props = { spaces: { id: number; name: string }[]; contacts: { id: number; name: string }[] };

export default function PortalSpaceReservationsCreate({ spaces, contacts }: Props) {
    return (
        <PortalLayout title="Reservar espaco" subtitle="Pedido de reserva com dados simples.">
            <SpaceReservationForm spaces={spaces} contacts={contacts} submitRoute={route('portal.space-reservations.store')} />
        </PortalLayout>
    );
}
