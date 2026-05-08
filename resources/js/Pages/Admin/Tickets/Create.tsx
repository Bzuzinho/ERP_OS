import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Option = { id: number; name: string };
type ScopedOption = { id: number; name: string; organization_id?: number | null; department_id?: number | null };

type Props = {
    contacts: Option[];
    users: ScopedOption[];
    organizations: Option[];
    departments: ScopedOption[];
    teams: ScopedOption[];
    serviceAreas: ScopedOption[];
    operationalContext: {
        organization_id: number | null;
        department_id: number | null;
        service_area_id: number | null;
        all_my_scopes: boolean;
    };
    statuses: string[];
    types: string[];
    priorities: string[];
    sources: string[];
};

export default function TicketsCreate({ contacts, users, organizations, departments, teams, serviceAreas, operationalContext, types, priorities, sources }: Props) {
    const form = useForm({
        all_my_scopes: operationalContext.all_my_scopes,
        organization_id: operationalContext.organization_id ? String(operationalContext.organization_id) : '',
        contact_id: '',
        assigned_to: '',
        department_id: operationalContext.department_id ? String(operationalContext.department_id) : '',
        service_area_id: operationalContext.service_area_id ? String(operationalContext.service_area_id) : '',
        team_id: '',
        category: '',
        subcategory: '',
        priority: priorities[1] ?? 'normal',
        type: types[0] ?? 'internal',
        title: '',
        description: '',
        location_text: '',
        source: sources[1] ?? 'internal',
        visibility: 'internal',
        due_date: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.tickets.store'));
    };

    const selectedOrganization = form.data.organization_id ? Number(form.data.organization_id) : null;
    const selectedDepartment = form.data.department_id ? Number(form.data.department_id) : null;

    const filteredUsers = users.filter((item) => !selectedOrganization || item.organization_id === selectedOrganization);
    const filteredDepartments = departments.filter((item) => !selectedOrganization || item.organization_id === selectedOrganization);
    const filteredTeams = teams.filter((item) => {
        if (selectedOrganization && item.organization_id !== selectedOrganization) return false;
        if (selectedDepartment && item.department_id !== selectedDepartment) return false;
        return true;
    });
    const filteredServiceAreas = serviceAreas.filter((item) => {
        if (selectedOrganization && item.organization_id !== selectedOrganization) return false;
        if (selectedDepartment && item.department_id !== selectedDepartment) return false;
        return true;
    });

    return (
        <AdminLayout title="Novo Pedido" subtitle="Registar um novo ticket no CRM municipal">
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <label className="inline-flex items-center gap-2 text-sm text-slate-700 md:col-span-2">
                        <input type="checkbox" checked={form.data.all_my_scopes} onChange={(event) => form.setData('all_my_scopes', event.target.checked)} />
                        Criar no contexto all_my_scopes
                    </label>
                    <select value={form.data.organization_id} onChange={(event) => { form.setData('organization_id', event.target.value); form.setData('department_id', ''); form.setData('service_area_id', ''); }} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Selecionar organização</option>
                        {organizations.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                    <select value={form.data.type} onChange={(event) => form.setData('type', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {types.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} placeholder="Assunto" className="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
                    <textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} placeholder="Descricao" className="min-h-28 rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
                    <input value={form.data.category} onChange={(event) => form.setData('category', event.target.value)} placeholder="Categoria" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input value={form.data.subcategory} onChange={(event) => form.setData('subcategory', event.target.value)} placeholder="Subcategoria" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <select value={form.data.contact_id} onChange={(event) => form.setData('contact_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Sem contacto</option>
                        {contacts.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                    <select value={form.data.assigned_to} onChange={(event) => form.setData('assigned_to', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Sem responsavel</option>
                        {filteredUsers.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                    <select value={form.data.service_area_id} onChange={(event) => form.setData('service_area_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Sem area funcional</option>
                        {filteredServiceAreas.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                    <select value={form.data.department_id} onChange={(event) => { form.setData('department_id', event.target.value); form.setData('service_area_id', ''); }} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Sem departamento</option>
                        {filteredDepartments.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                    <select value={form.data.team_id} onChange={(event) => form.setData('team_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Sem equipa</option>
                        {filteredTeams.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                    <select value={form.data.priority} onChange={(event) => form.setData('priority', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {priorities.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <select value={form.data.source} onChange={(event) => form.setData('source', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {sources.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <select value={form.data.visibility} onChange={(event) => form.setData('visibility', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="internal">Interno</option>
                        <option value="public">Publico</option>
                    </select>
                    <input type="date" value={form.data.due_date} onChange={(event) => form.setData('due_date', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input value={form.data.location_text} onChange={(event) => form.setData('location_text', event.target.value)} placeholder="Localizacao" className="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
                </div>

                <div className="flex items-center gap-3">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Guardar</button>
                    <Link href={route('admin.tickets.index')} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
