import { CheckIcon, ChevronsUpDownIcon } from 'lucide-react';
import { useState } from 'react';

import type { SelectOption } from '@/components/select-with-items';
import { Button } from '@/components/ui/button';
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
import { cn } from '@/lib/utils';

type Props = {
    id?: string;
    name: string;
    items: SelectOption[];
    value?: string;
    placeholder?: string;
    searchPlaceholder?: string;
    emptyMessage?: string;
    onValueChange?: (value: string) => void;
};

export function SearchableSelect({
    id,
    name,
    items,
    value,
    placeholder = 'Choisir une option',
    searchPlaceholder = 'Rechercher…',
    emptyMessage = 'Aucun résultat.',
    onValueChange,
}: Props) {
    const [open, setOpen] = useState(false);
    const selected = items.find((item) => item.value === value);

    return (
        <>
            <input type="hidden" name={name} value={value ?? ''} />
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        id={id}
                        type="button"
                        variant="outline"
                        role="combobox"
                        aria-expanded={open}
                        className="w-full justify-between font-normal text-foreground"
                    >
                        <span className="truncate">
                            {selected?.label ?? placeholder}
                        </span>
                        <ChevronsUpDownIcon className="size-4 shrink-0 opacity-50" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent
                    className="w-[var(--radix-popover-trigger-width)] p-0"
                    align="start"
                >
                    <Command>
                        <CommandInput placeholder={searchPlaceholder} />
                        <CommandList>
                            <CommandEmpty>{emptyMessage}</CommandEmpty>
                            <CommandGroup>
                                {items.map((item) => (
                                    <CommandItem
                                        key={item.value}
                                        value={`${item.label} ${item.value}`}
                                        onSelect={() => {
                                            onValueChange?.(item.value);
                                            setOpen(false);
                                        }}
                                    >
                                        <CheckIcon
                                            className={cn(
                                                'size-4',
                                                item.value === value
                                                    ? 'opacity-100'
                                                    : 'opacity-0',
                                            )}
                                        />
                                        {item.label}
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
        </>
    );
}
