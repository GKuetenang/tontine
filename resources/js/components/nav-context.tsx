import { Link } from '@inertiajs/react';

import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useAuthorization } from '@/hooks/use-authorization';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

type NavContextProps = {
    items: NavItem[];
    children?: React.ReactNode;
};

export function NavContext({ items, children }: NavContextProps) {
    const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();
    const { can } = useAuthorization();
    const authorizedItems = items.filter(
        (item) => !item.permission || can(item.permission),
    );

    if (authorizedItems.length === 0) {
        return null;
    }

    return (
        <SidebarGroup>
            <SidebarGroupLabel className="sr-only">
                Navigation contextuelle
            </SidebarGroupLabel>

            {children}

            <SidebarMenu>
                {authorizedItems.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            isActive={
                                item.activeWithParentUrl
                                    ? isCurrentOrParentUrl(item.href)
                                    : isCurrentUrl(item.href)
                            }
                            tooltip={{
                                children: item.title,
                            }}
                        >
                            <Link href={item.href} prefetch>
                                {item.icon && <item.icon />}

                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
