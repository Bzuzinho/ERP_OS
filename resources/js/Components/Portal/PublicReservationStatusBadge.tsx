import AppBadge from '@/Components/App/AppBadge';

type Props = {
    status: string;
};

const labels: Record<string, string> = {
    requested: 'Pedido recebido',
    approved: 'Aprovado',
    rejected: 'Rejeitado',
    cancelled: 'Cancelado',
    completed: 'Concluido',
};

const tones: Record<string, 'blue' | 'amber' | 'green' | 'red' | 'slate'> = {
    requested: 'amber',
    approved: 'green',
    rejected: 'red',
    cancelled: 'slate',
    completed: 'blue',
};

export default function PublicReservationStatusBadge({ status }: Props) {
    return <AppBadge tone={tones[status] ?? 'slate'}>{labels[status] ?? 'Em analise'}</AppBadge>;
}
