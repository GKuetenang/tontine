import { Link, router, usePage } from '@inertiajs/react';
import {
    Building2Icon,
    CalendarIcon,
    LayoutGrid,
    ListIcon,
} from 'lucide-react';

import AppLogo from '@/components/app-logo';
import { NavContext } from '@/components/nav-context';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { SidebarContextSwitcher } from '@/components/sidebar-context-switcher';
import type { SidebarContextOption } from '@/components/sidebar-context-switcher';
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
import sessions from '@/routes/groups/sessions';
import type { NavItem, Session, Group } from '@/types';

type SidebarPageProps = {
    group?: Group;
    session?: Session;
    navigation: {
        groups: SidebarContextOption[];
        sessions: SidebarContextOption[];
    };
};

export function AppSidebar() {
    const { t } = useTranslation();
    const { props } = usePage<SidebarPageProps>();

    const group = props.group;
    const session = props.session;
    const navigation = props.navigation ?? { groups: [], sessions: [] };

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
                    <NavContext items={getGroupNavItems(group)}>
                        <SidebarContextSwitcher
                            label={t('Réunion')}
                            current={navigation.groups.find(
                                (option) => option.slug === group.slug,
                            )}
                            options={navigation.groups}
                            icon={Building2Icon}
                            searchPlaceholder={t('Rechercher une réunion')}
                            emptyMessage={t('Aucune réunion trouvée')}
                            onSelect={(option) =>
                                router.visit(groups.show(option.slug))
                            }
                        />
                    </NavContext>
                )}

                {group && session?.slug && (
                    <NavContext items={getSessionNavItems(group, session)}>
                        <SidebarContextSwitcher
                            label={t('Session')}
                            current={navigation.sessions.find(
                                (option) => option.slug === session.slug,
                            )}
                            options={navigation.sessions}
                            icon={CalendarIcon}
                            searchPlaceholder={t('Rechercher une session')}
                            emptyMessage={t('Aucune session trouvée')}
                            onSelect={(option) =>
                                router.visit(
                                    sessions.show({
                                        group: group.slug!,
                                        session: option.slug,
                                    }),
                                )
                            }
                        />
                    </NavContext>
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
