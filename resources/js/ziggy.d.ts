import axios from 'axios';

declare global {
    interface Window {
        axios: typeof axios;
    }

    function route(
        name?: string,
        params?: Record<string, unknown> | string | number,
        absolute?: boolean
    ): string & {
        current: (name?: string) => boolean;
    };
}

export {};
