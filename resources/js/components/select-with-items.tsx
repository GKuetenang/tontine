import type { ComponentProps } from 'react';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export type SelectOption = {
    label: string;
    value: string;
};

type Props = {
    items: SelectOption[];
    placeholder?: string;
    className?: string;
    name: string;
    defaultValue?: string;
} & ComponentProps<typeof SelectTrigger>;

export function SelectWithItems({
    items,
    placeholder,
    name,
    defaultValue,
    ...props
}: Props) {
    return (
        <Select name={name} defaultValue={defaultValue}>
            <SelectTrigger {...props}>
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    <SelectLabel>{placeholder}</SelectLabel>
                    {items.map((item) => (
                        <SelectItem key={item.value} value={item.value}>
                            {item.label}
                        </SelectItem>
                    ))}
                </SelectGroup>
            </SelectContent>
        </Select>
    );
}
