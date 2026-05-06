type NotificationBadgeProps = {
    count: number;
};

export default function NotificationBadge({ count }: NotificationBadgeProps) {
    if (count <= 0) {
        return null;
    }

    return (
        <span className="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 text-[11px] font-semibold text-white">
            {count > 99 ? '99+' : count}
        </span>
    );
}
