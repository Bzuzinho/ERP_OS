type Run = {
    id: number;
    run_at: string;
    status: string;
    generated_task_id?: number | null;
    generated_event_id?: number | null;
    error_message?: string | null;
};

type Props = { runs: Run[] };

export default function RecurringOperationRunList({ runs }: Props) {
    return (
        <ul className="space-y-2 text-sm">
            {runs.map((run) => (
                <li key={run.id} className="rounded-xl bg-slate-50 px-3 py-2 text-slate-700">
                    {new Date(run.run_at).toLocaleString()} • {run.status}
                    {run.generated_task_id ? ` • tarefa #${run.generated_task_id}` : ''}
                    {run.generated_event_id ? ` • evento #${run.generated_event_id}` : ''}
                    {run.error_message ? ` • erro: ${run.error_message}` : ''}
                </li>
            ))}
            {runs.length === 0 ? <li className="text-slate-500">Sem execuções registadas.</li> : null}
        </ul>
    );
}
