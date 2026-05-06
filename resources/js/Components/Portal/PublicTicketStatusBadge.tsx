import AppBadge from '@/Components/App/AppBadge';
import { mapTicketStatusToPublicLabel } from '@/Components/Portal/publicTicketStatus';

type Props = {
    status: string;
};

const toneByPublicLabel: Record<string, 'blue' | 'amber' | 'green' | 'red' | 'slate'> = {
    Recebido: 'blue',
    'Em analise': 'amber',
    'Em tratamento': 'blue',
    'A aguardar informacao': 'amber',
    Resolvido: 'green',
    Encerrado: 'slate',
    Cancelado: 'red',
};

export default function PublicTicketStatusBadge({ status }: Props) {
    const label = mapTicketStatusToPublicLabel(status);

    return <AppBadge tone={toneByPublicLabel[label] ?? 'slate'}>{label}</AppBadge>;
}
