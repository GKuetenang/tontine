import type { LucideIcon } from 'lucide-react';
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-react';
import { useState } from 'react';

import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';

export type SidebarContextOption = {
    id: number;
    name: string;
    slug: string;
};

type Props = {
    label: string;
    current?: SidebarContextOption;
    options: SidebarContextOption[];
    icon: LucideIcon;
    searchPlaceholder: string;
    emptyMessage: string;
    onSelect: (option: SidebarContextOption) => void;
};

export function SidebarContextSwitcher({
    label,
    current,
    options,
    icon: Icon,
    searchPlaceholder,
    emptyMessage,
    onSelect,
}: Props) {
    const [open, setOpen] = useState(false);

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <Popover open={open} onOpenChange={setOpen}>
                    <PopoverTrigger asChild>
                        <SidebarMenuButton
                            size="lg"
                            role="combobox"
                            aria-expanded={open}
                            aria-label={label}
                            tooltip={current?.name ?? label}
                            className="data-[state=open]:bg-sidebar-accent"
                        >
                            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-accent text-sidebar-primary">
                                <Icon className="size-4" />
                            </div>
                            <div className="grid min-w-0 flex-1 text-left text-sm leading-tight">
                                <span className="truncate text-xs text-muted-foreground">
                                    {label}
                                </span>
                                <span className="truncate font-semibold">
                                    {current?.name ?? label}
                                </span>
                            </div>
                            <ChevronsUpDownIcon className="ml-auto size-4 opacity-50 group-data-[collapsible=icon]:hidden" />
                        </SidebarMenuButton>
                    </PopoverTrigger>
                    <PopoverContent
                        align="start"
                        side="right"
                        sideOffset={8}
                        className="w-72 p-0"
                    >
                        <Command>
                            <CommandInput placeholder={searchPlaceholder} />
                            <CommandList>
                                <CommandEmpty>{emptyMessage}</CommandEmpty>
                                <CommandGroup heading={label}>
                                    {options.map((option) => (
                                        <CommandItem
                                            key={option.id}
                                            value={`${option.name} ${option.slug}`}
                                            onSelect={() => {
                                                setOpen(false);
                                                onSelect(option);
                                            }}
                                        >
                                            <Icon className="size-4" />
                                            <span className="truncate">
                                                {option.name}
                                            </span>
                                            <CheckIcon
                                                className={cn(
                                                    'ml-auto size-4',
                                                    option.id === current?.id
                                                        ? 'opacity-100'
                                                        : 'opacity-0',
                                                )}
                                            />
                                        </CommandItem>
                                    ))}
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
