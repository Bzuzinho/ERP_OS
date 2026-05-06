import SpaceSummaryCard from '@/Components/SpaceSummaryCard';
import PortalLayout from '@/Layouts/PortalLayout';
import { Link } from '@inertiajs/react';

type Space = { id: number; name: string; status: string; location_text: string | null; capacity: number | null; rules: string | null; description: string | null };
type Props = { space: Space };

export default function PortalSpacesShow({ space }: Props) {
    return (
        <PortalLayout
            title="Reservar espaco"
            subtitle={space.name}
            headerActions={
                <Link href={route('portal.space-reservations.create')} className="flex w-full items-center justify-center rounded-xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white lg:inline-flex lg:w-auto lg:py-2">
                    Reservar espaco
                </Link>
            }
        >
            <SpaceSummaryCard name={space.name} status={space.status} location_text={space.location_text} capacity={space.capacity} is_public />
            <section className="mt-4 rounded-2xl border border-stone-200 bg-white p-4 text-sm text-stone-700">
                <p className="font-semibold text-stone-900">Descricao</p>
                <p className="mt-1">{space.description ?? '-'}</p>
                <p className="mt-3 font-semibold text-stone-900">Regras</p>
                <p className="mt-1">{space.rules ?? '-'}</p>
            </section>
        </PortalLayout>
    );
}
