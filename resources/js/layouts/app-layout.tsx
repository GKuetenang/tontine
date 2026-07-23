import { usePage } from '@inertiajs/react';
import type { FC, ReactNode } from 'react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { Toaster } from '@/components/ui/sonner';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';

const AppLayout = ({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) => {
    const { flash } = usePage().props;

    useEffect(() => {
        if (flash.success) {
            toast.success(flash.success);
        }

        if (flash.error) {
            toast.error(flash.error);
        }

        if (flash.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    return (
        <AppLayoutTemplate breadcrumbs={breadcrumbs}>
            {children}
            <Toaster richColors position="top-center" closeButton />
        </AppLayoutTemplate>
    );
};

export function withAppLayout<T>(
    breadcrumbs: BreadcrumbItem[],
    component: FC<T>,
) {
    // @ts-expect-error layout exists for inertia
    component.layout = (page: ReactNode) => (
        <AppLayout breadcrumbs={breadcrumbs}>
            <div className="p-4 lg:p-6">{page}</div>
        </AppLayout>
    );

    return component;
}

export default AppLayout;
