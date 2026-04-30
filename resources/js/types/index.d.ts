import type { PageProps as InertiaPageProps } from '@inertiajs/core';

export type User = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    email_verified_at?: string | null;
    organization?: {
        id: number;
        name: string;
        slug: string;
    } | null;
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
    };
