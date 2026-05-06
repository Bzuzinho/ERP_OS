export type PublicTicketStatusKey =
    | 'em_analise'
    | 'em_tratamento'
    | 'aguarda_informacao'
    | 'resolvido'
    | 'todos';

export function mapTicketStatusToPublicLabel(status: string): string {
    const normalized = status.toLowerCase();

    if (['cancelado'].includes(normalized)) {
        return 'Cancelado';
    }

    if (['fechado', 'indeferido'].includes(normalized)) {
        return 'Encerrado';
    }

    if (['resolvido'].includes(normalized)) {
        return 'Resolvido';
    }

    if (['aguarda_informacao'].includes(normalized)) {
        return 'A aguardar informacao';
    }

    if (['encaminhado', 'em_execucao', 'agendado'].includes(normalized)) {
        return 'Em tratamento';
    }

    if (['novo', 'em_analise'].includes(normalized)) {
        return normalized === 'novo' ? 'Recebido' : 'Em analise';
    }

    return 'Em analise';
}

export function mapTicketStatusToPublicFilter(status: string): Exclude<PublicTicketStatusKey, 'todos'> {
    const label = mapTicketStatusToPublicLabel(status);

    if (['Resolvido', 'Encerrado', 'Cancelado'].includes(label)) {
        return 'resolvido';
    }

    if (label === 'A aguardar informacao') {
        return 'aguarda_informacao';
    }

    if (label === 'Em tratamento') {
        return 'em_tratamento';
    }

    return 'em_analise';
}
