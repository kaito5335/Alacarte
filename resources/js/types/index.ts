import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    /** 未ログイン時は null */
    user: User | null;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
    // Inertia の usePage<T> は T が PageProps（インデックスシグネチャ必須）であることを要求する
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    handle: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
