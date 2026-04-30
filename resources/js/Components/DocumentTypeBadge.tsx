type Props = {
    typeName?: string | null;
};

export default function DocumentTypeBadge({ typeName }: Props) {
    return (
        <span className="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-800">
            {typeName ?? 'Sem tipo'}
        </span>
    );
}
