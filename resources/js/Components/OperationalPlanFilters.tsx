import TextInput from '@/Components/TextInput';

type Option = { id: number; name: string };

type Props = {
    filters: Record<string, string | null | undefined>;
    statuses: string[];
    types: string[];
    visibilities: string[];
    departments: Option[];
    teams: Option[];
    onChange: (name: string, value: string) => void;
};

export default function OperationalPlanFilters({ filters, statuses, types, visibilities, departments, teams, onChange }: Props) {
    return (
        <div className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-3 xl:grid-cols-6">
            <TextInput value={filters.search ?? ''} onChange={(e) => onChange('search', e.target.value)} placeholder="Pesquisar" />
            <select value={filters.status ?? ''} onChange={(e) => onChange('status', e.target.value)} className="rounded-lg border-slate-300">
                <option value="">Estado</option>
                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
            </select>
            <select value={filters.plan_type ?? ''} onChange={(e) => onChange('plan_type', e.target.value)} className="rounded-lg border-slate-300">
                <option value="">Tipo</option>
                {types.map((type) => <option key={type} value={type}>{type}</option>)}
            </select>
            <select value={filters.visibility ?? ''} onChange={(e) => onChange('visibility', e.target.value)} className="rounded-lg border-slate-300">
                <option value="">Visibilidade</option>
                {visibilities.map((visibility) => <option key={visibility} value={visibility}>{visibility}</option>)}
            </select>
            <select value={filters.department_id ?? ''} onChange={(e) => onChange('department_id', e.target.value)} className="rounded-lg border-slate-300">
                <option value="">Departamento</option>
                {departments.map((department) => <option key={department.id} value={department.id}>{department.name}</option>)}
            </select>
            <select value={filters.team_id ?? ''} onChange={(e) => onChange('team_id', e.target.value)} className="rounded-lg border-slate-300">
                <option value="">Equipa</option>
                {teams.map((team) => <option key={team.id} value={team.id}>{team.name}</option>)}
            </select>
        </div>
    );
}
