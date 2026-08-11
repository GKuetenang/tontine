import { router } from '@inertiajs/react';
import type { FC, ReactNode } from 'react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { Toaster } from '@/components/ui/sonner';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import type { Flash } from '@/types/flash';

const AppLayout = ({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) => {

    useEffect(() => {
        return router.on('flash', (event) => {
            const flash = event.detail.flash as Flash;

            if (flash.success) {
                toast.success(flash.success);
            }

            if (flash.error) {
                toast.error(flash.error);
            }

            if (flash.warning) {
                toast.warning(flash.warning);
            }

            if (flash.info) {
                toast.info(flash.info);
            }
        });

    }, []);

    return (
        <AppLayoutTemplate breadcrumbs={breadcrumbs}>
            {children}
            <Toaster richColors position="top-right" closeButton duration={4000} />
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
