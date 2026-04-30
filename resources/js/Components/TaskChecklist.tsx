type ChecklistItem = {
    id: number;
    label: string;
    is_completed: boolean;
};

type Checklist = {
    id: number;
    title: string;
    items: ChecklistItem[];
};

type TaskChecklistProps = {
    checklists: Checklist[];
};

export default function TaskChecklist({ checklists }: TaskChecklistProps) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 className="text-lg font-semibold text-slate-900">Checklist</h3>
            <div className="mt-3 space-y-3">
                {checklists.map((checklist) => (
                    <section key={checklist.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <h4 className="text-sm font-semibold text-slate-900">{checklist.title}</h4>
                        <ul className="mt-2 space-y-1 text-sm text-slate-700">
                            {checklist.items.map((item) => (
                                <li key={item.id}>{item.is_completed ? '[]' : '[ ]'} {item.label}</li>
                            ))}
                        </ul>
                    </section>
                ))}
                {checklists.length === 0 ? <p className="text-sm text-slate-500">Sem checklists.</p> : null}
            </div>
        </div>
    );
}
