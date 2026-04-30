import SpaceReservationForm from '@/Components/SpaceReservationForm';
import PortalLayout from '@/Layouts/PortalLayout';

type Props = { spaces: { id: number; name: string }[]; contacts: { id: number; name: string }[] };

export default function PortalSpaceReservationsCreate({ spaces, contacts }: Props) {
    return (
        <PortalLayout title="Pedir Reserva" subtitle="Submeta um pedido para utilizacao de espaco">
            <SpaceReservationForm spaces={spaces} contacts={contacts} submitRoute={route('portal.space-reservations.store')} />
        </PortalLayout>
    );
}
