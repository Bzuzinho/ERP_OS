import SpaceCard from '@/Components/SpaceCard';
import PortalLayout from '@/Layouts/PortalLayout';

type SpaceItem = { id: number; name: string; capacity: number | null; location_text: string | null; status: string };
type Props = { spaces: { data: SpaceItem[] } };

export default function PortalSpacesIndex({ spaces }: Props) {
    return (
        <PortalLayout title="Espacos" subtitle="Espacos disponiveis para pedido de reserva">
            <div className="grid gap-3 md:grid-cols-2">
                {spaces.data.map((space) => <SpaceCard key={space.id} space={space} showReserveButton />)}
            </div>
        </PortalLayout>
    );
}
