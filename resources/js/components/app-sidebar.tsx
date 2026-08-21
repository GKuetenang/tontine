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
import { getSessionNavItems, getTontineNavItems } from '@/lib/utils';

import { dashboard } from '@/routes';
import tontines from '@/routes/tontines';

import type { NavItem, Session, Tontine } from '@/types';

import { Link, usePage } from '@inertiajs/react';

import {
    LayoutGrid,
    ListIcon
} from 'lucide-react';

type SidebarPageProps = {
    tontine?: Tontine;
    session?: Session;
};



export function AppSidebar() {
    const { props } = usePage<SidebarPageProps>();

    const tontine = props.tontine;
    const session = props.session;

    const mainNavItems: NavItem[] = [
        {
            title: 'Tableau de bord',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Tontines',
            href: tontines.index(),
            icon: ListIcon,
        },
    ];

    return (
        <Sidebar
            collapsible="icon"
            variant="inset"
        >
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                        >
                            <Link
                                href={dashboard()}
                                prefetch
                            >
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />

                {(tontine?.slug) && (
                    <NavContext
                        label="Tontine"
                        title={tontine.name}
                        items={
                            getTontineNavItems(
                                tontine,
                            )
                        }
                    />
                )}

                {(tontine && session?.slug) && (
                    <NavContext
                        label="Session"
                        title={session.name}
                        items={
                            getSessionNavItems(
                                tontine,
                                session,
                            )
                        }
                    />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}