import type { PageProps as InertiaPageProps } from '@inertiajs/core';

export type User = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    email_verified_at?: string | null;
    avatar_path?: string | null;
    organization?: {
        id: number;
        name: string;
        slug: string;
        logo_path?: string | null;
    } | null;
};

export type Flash = {
    success?: string | null;
    error?: string | null;
};

export type NotificationPreview = {
    recipient_id: number;
    id: number;
    notification_id: number;
    title: string | null;
    message: string | null;
    priority: string;
    type: string | null;
    action_url: string | null;
    read_at: string | null;
    created_at: string | null;
    created_at_human: string | null;
};

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> =
    T &
    InertiaPageProps & {
        auth: {
            user: User | null;
            can: {
                accessAdmin: boolean;
            };
        };
        flash?: Flash;
        notifications: {
            unread_count: number;
            recent: NotificationPreview[];
        };
    };
