import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';

type RequestRow = {
    id: number;
    title: string;
    status: string;
    created_at: string;
    requested_by?: { id: number; name: string } | null;
    approved_by?: { id: number; name: string } | null;
};

type Props = {
    requests: {
        data: RequestRow[];
    };
    statuses: string[];
    filters: {
        status?: string;
    };
};

export default function ResourceRequestsIndex({ requests }: Props) {
    return (
        <AdminLayout title="Requisicoes de Recursos" subtitle="Fluxo operacional de requisicao, aprovacao e devolucao.">
            <Head title="Requisicoes de Recursos" />

            <section className="rounded-2xl border border-slate-200 bg-white p-4">
                <div className="mb-3 flex items-center justify-between">
                    <h2 className="text-lg font-semibold text-slate-900">Lista de requisicoes</h2>
                    <Link href={route('admin.resource-requests.create')} className="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                        Nova requisicao
                    </Link>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-slate-600">
                            <tr>
                                <th className="px-3 py-2 text-left">ID</th>
                                <th className="px-3 py-2 text-left">Titulo</th>
                                <th className="px-3 py-2 text-left">Estado</th>
                                <th className="px-3 py-2 text-left">Solicitante</th>
                                <th className="px-3 py-2 text-left">Aprovador</th>
                                <th className="px-3 py-2 text-left">Criada</th>
                            </tr>
                        </thead>
                        <tbody>
                            {requests.data.map((request) => (
                                <tr key={request.id} className="border-t border-slate-100">
                                    <td className="px-3 py-2">#{request.id}</td>
                                    <td className="px-3 py-2">
                                        <Link href={route('admin.resource-requests.show', request.id)} className="text-blue-700 hover:underline">
                                            {request.title}
                                        </Link>
                                    </td>
                                    <td className="px-3 py-2">{request.status}</td>
                                    <td className="px-3 py-2">{request.requested_by?.name ?? '-'}</td>
                                    <td className="px-3 py-2">{request.approved_by?.name ?? '-'}</td>
                                    <td className="px-3 py-2">{new Date(request.created_at).toLocaleString()}</td>
                                </tr>
                            ))}
                            {requests.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-3 py-8 text-center text-slate-500">
                                        Sem requisicoes.
                                    </td>
                                </tr>
                            ) : null}
                        </tbody>
                    </table>
                </div>
            </section>
        </AdminLayout>
    );
}
