type Props = {
    onChange: (file: File | null) => void;
};

export default function DocumentUploadField({ onChange }: Props) {
    return (
        <div className="space-y-2">
            <label className="text-sm font-medium text-slate-700">Ficheiro</label>
            <input
                type="file"
                onChange={(event) => onChange(event.target.files?.[0] ?? null)}
                className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
            />
        </div>
    );
}
