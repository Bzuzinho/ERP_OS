import PortalLayout from '@/Layouts/PortalLayout';

export default function PortalOperationalPlansShow({ plan }: { plan: any }) {
    return (
        <PortalLayout title={plan.title} subtitle="Detalhe da atividade pública">
            <section className="rounded-2xl border border-stone-200 bg-white p-6">
                <p className="text-xs uppercase tracking-[0.2em] text-amber-700">{plan.plan_type}</p>
                <h2 className="mt-2 text-2xl font-semibold text-stone-950">{plan.title}</h2>
                <p className="mt-4 text-sm text-stone-600">{plan.description ?? 'Sem descrição pública.'}</p>
                <div className="mt-4 grid gap-3 text-sm text-stone-700 md:grid-cols-3">
                    <div>Estado: {plan.status}</div>
                    <div>Início: {plan.start_date}</div>
                    <div>Fim: {plan.end_date ?? '-'}</div>
                    <div>Local: {plan.related_space?.name ?? '-'}</div>
                </div>
            </section>

            <section className="mt-4 rounded-2xl border border-stone-200 bg-white p-6">
                <h3 className="text-lg font-semibold text-stone-900">Documentos Públicos</h3>
                <ul className="mt-3 space-y-2 text-sm">
                    {(plan.documents ?? []).map((document: any) => (
                        <li key={document.id} className="rounded-xl bg-stone-50 px-3 py-2 text-stone-700">{document.title}</li>
                    ))}
                    {(plan.documents ?? []).length === 0 ? <li className="text-stone-500">Sem documentos públicos disponíveis.</li> : null}
                </ul>
            </section>
        </PortalLayout>
    );
}
