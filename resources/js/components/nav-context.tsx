import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

type NavContextProps = {
    label: string;
    title?: string;
    items: NavItem[];
};

export function NavContext({ label, title, items }: NavContextProps) {
    const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="group-data-[collapsible=icon]:hidden">
            <SidebarGroupLabel className="flex h-auto flex-col items-start py-2">
                <span className="uppercase">{label}</span>

                {title && (
                    <span className="truncate text-sm font-semibold text-foreground">
                        {title}
                    </span>
                )}
            </SidebarGroupLabel>

            <SidebarMenu>
                {items.map((item) => (
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
