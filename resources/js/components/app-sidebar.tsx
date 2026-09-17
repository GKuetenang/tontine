import { Link, usePage } from '@inertiajs/react';
import { LayoutGrid, ListIcon } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavContext } from '@/components/nav-context';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useTranslation } from '@/hooks/use-translation';
import { getSessionNavItems, getGroupNavItems } from '@/lib/utils';

import { dashboard } from '@/routes';
import groups from '@/routes/groups';

import type { NavItem, Session, Group } from '@/types';

type SidebarPageProps = {
    group?: Group;
    session?: Session;
};

export function AppSidebar() {
    const { t } = useTranslation();
    const { props } = usePage<SidebarPageProps>();

    const group = props.group;
    const session = props.session;

    const mainNavItems: NavItem[] = [
        {
            title: 'Tableau de bord',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Réunions',
            href: groups.index(),
            icon: ListIcon,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />

                {group?.slug && (
                    <NavContext
                        label={t('Réunion')}
                        title={group.name}
                        items={getGroupNavItems(group)}
                    />
                )}

                {group && session?.slug && (
                    <NavContext
                        label={t('Session')}
                        title={session.name}
                        items={getSessionNavItems(group, session)}
                    />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
