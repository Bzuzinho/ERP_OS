type Props = {
    hasApprovedConflict: boolean;
    requestedConflictsCount?: number;
};

export default function SpaceAvailabilityIndicator({ hasApprovedConflict, requestedConflictsCount = 0 }: Props) {
    if (hasApprovedConflict) {
        return <p className="text-sm font-medium text-rose-700">Conflito com reserva aprovada no periodo.</p>;
    }

    if (requestedConflictsCount > 0) {
        return <p className="text-sm font-medium text-amber-700">Existem pedidos pendentes no mesmo periodo.</p>;
    }

    return <p className="text-sm font-medium text-emerald-700">Disponivel para o periodo selecionado.</p>;
}
