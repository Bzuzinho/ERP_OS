type ContactSummaryCardProps = {
    contact?: {
        id: number;
        name: string;
        email?: string | null;
        phone?: string | null;
        mobile?: string | null;
    } | null;
};

export default function ContactSummaryCard({ contact }: ContactSummaryCardProps) {
    if (!contact) {
        return (
            <div className="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                Sem contacto associado.
            </div>
        );
    }

    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-4">
            <h3 className="text-lg font-semibold text-slate-900">Contacto associado</h3>
            <p className="mt-2 text-sm text-slate-700">{contact.name}</p>
            <p className="mt-1 text-sm text-slate-600">{contact.email ?? 'Sem email'}</p>
            <p className="mt-1 text-sm text-slate-600">{contact.mobile ?? contact.phone ?? 'Sem telefone'}</p>
        </div>
    );
}
